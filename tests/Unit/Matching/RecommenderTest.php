<?php

namespace Tests\Unit\Matching;

use App\Models\Skill;
use App\Models\SkillRequest;
use App\Models\User;
use App\Services\Matching\Recommender;
use Tests\TestCase;

class RecommenderTest extends TestCase
{
    private Recommender $recommender;

    protected function setUp(): void
    {
        parent::setUp();
        $this->recommender = new Recommender;
    }

    private function makeSkill(int $id, string $title, string $category): Skill
    {
        $skill = new Skill(['Skill_Title' => $title, 'Category' => $category]);
        $skill->Skill_ID = $id;

        return $skill;
    }

    private function makeUser(int $id, array $overrides = []): User
    {
        $data = array_merge([
            'Full_Name' => "User {$id}",
            'Email' => "user{$id}@test.com",
            'Role' => 'Student',
            'Avg_Rating' => 3.0,
            'Total_Completed' => 0,
            'Is_Verified' => false,
            'Account_Status' => 'Active',
        ], $overrides);

        $user = new User($data);
        $user->User_ID = $id;

        return $user;
    }

    private function makeRequest(
        int $id,
        int $requesterId,
        int $primarySkillId,
        array $allSkills,
        array $additionalSkillIds = []
    ): SkillRequest {
        $primarySkill = collect($allSkills)->firstWhere('Skill_ID', $primarySkillId);
        $additional = collect($allSkills)->filter(fn ($s) => in_array($s->Skill_ID, $additionalSkillIds));

        $request = new SkillRequest([
            'User_ID' => $requesterId,
            'Skill_ID' => $primarySkillId,
            'Title' => 'Test Request',
            'Description' => 'Test description',
            'Status' => 'Open',
            'Service_Mode' => 'Remote',
        ]);
        $request->Request_ID = $id;
        $request->setRelation('skill', $primarySkill);
        $request->setRelation('skills', $additional);

        return $request;
    }

    private function makeProviders(SkillRequest $request, array $users): array
    {
        return array_map(fn ($u) => $this->recommender->score($request, $u), $users);
    }

    public function test_perfect_match_score(): void
    {
        $php = $this->makeSkill(1, 'PHP', 'Programming');
        $js = $this->makeSkill(2, 'JavaScript', 'Programming');

        $request = $this->makeRequest(1, 1, 1, [$php, $js], [2]);

        $user = $this->makeUser(10, [
            'Avg_Rating' => 5.0,
            'Total_Completed' => 10,
            'Is_Verified' => true,
            'Service_Modes' => ['Remote', 'Face-to-Face', 'Hybrid'],
        ]);
        $skills = collect([$php, $js])->map(function ($skill) {
            $skill->setRelation('pivot', (object) ['Proficiency' => 4]);
            return $skill;
        });
        $user->setRelation('skills', $skills);

        $result = $this->recommender->score($request, $user);

        $this->assertEquals(100.0, $result['score']);
        $this->assertEquals(1.0, $result['raw_score']);
    }

    public function test_no_skill_overlap_still_receives_partial_score(): void
    {
        $php = $this->makeSkill(1, 'PHP', 'Programming');
        $graphic = $this->makeSkill(2, 'Graphic Design', 'Design');

        $request = $this->makeRequest(1, 1, 1, [$php], []);

        $user = $this->makeUser(10, [
            'Avg_Rating' => 4.0,
            'Total_Completed' => 5,
            'Is_Verified' => true,
        ]);
        $user->setRelation('skills', collect([$graphic]));

        $result = $this->recommender->score($request, $user);

        $this->assertGreaterThan(0.0, $result['score']);
        $this->assertEquals(0.0, $result['breakdown']['skill_overlap']);
        $this->assertEquals(0.0, $result['breakdown']['category_coverage']);
        $this->assertGreaterThan(0.0, $result['breakdown']['rating']);
    }

    public function test_exact_skill_match_ranks_above_partial(): void
    {
        $php = $this->makeSkill(1, 'PHP', 'Programming');
        $js = $this->makeSkill(2, 'JavaScript', 'Programming');
        $py = $this->makeSkill(3, 'Python', 'Programming');

        $request = $this->makeRequest(1, 1, 1, [$php, $js], [2]);

        $perfect = $this->makeUser(10, [
            'Avg_Rating' => 4.5,
            'Total_Completed' => 5,
            'Is_Verified' => true,
        ]);
        $perfect->setRelation('skills', collect([$php, $js]));

        $partial = $this->makeUser(11, [
            'Avg_Rating' => 5.0,
            'Total_Completed' => 10,
            'Is_Verified' => true,
        ]);
        $partial->setRelation('skills', collect([$php, $py]));

        $perfectResult = $this->recommender->score($request, $perfect);
        $partialResult = $this->recommender->score($request, $partial);

        $this->assertGreaterThan($partialResult['score'], $perfectResult['score']);
    }

    public function test_verified_provider_scores_higher(): void
    {
        $php = $this->makeSkill(1, 'PHP', 'Programming');
        $js = $this->makeSkill(2, 'JavaScript', 'Programming');

        $request = $this->makeRequest(1, 1, 1, [$php, $js], [2]);

        $verified = $this->makeUser(10, [
            'Avg_Rating' => 4.5,
            'Total_Completed' => 5,
            'Is_Verified' => true,
        ]);
        $verified->setRelation('skills', collect([$php, $js]));

        $unverified = $this->makeUser(11, [
            'Avg_Rating' => 4.5,
            'Total_Completed' => 5,
            'Is_Verified' => false,
        ]);
        $unverified->setRelation('skills', collect([$php, $js]));

        $vResult = $this->recommender->score($request, $verified);
        $uResult = $this->recommender->score($request, $unverified);

        $this->assertGreaterThan($uResult['score'], $vResult['score']);
        $this->assertGreaterThan($uResult['breakdown']['profile_quality'], $vResult['breakdown']['profile_quality']);
    }

    public function test_higher_rating_scores_higher(): void
    {
        $php = $this->makeSkill(1, 'PHP', 'Programming');
        $js = $this->makeSkill(2, 'JavaScript', 'Programming');

        $request = $this->makeRequest(1, 1, 1, [$php, $js], [2]);

        $highRated = $this->makeUser(10, [
            'Avg_Rating' => 4.8,
            'Total_Completed' => 10,
            'Is_Verified' => true,
        ]);
        $highRated->setRelation('skills', collect([$php, $js]));

        $lowRated = $this->makeUser(11, [
            'Avg_Rating' => 2.0,
            'Total_Completed' => 1,
            'Is_Verified' => false,
        ]);
        $lowRated->setRelation('skills', collect([$php, $js]));

        $highResult = $this->recommender->score($request, $highRated);
        $lowResult = $this->recommender->score($request, $lowRated);

        $this->assertGreaterThan($lowResult['score'], $highResult['score']);
        $this->assertGreaterThan($lowResult['breakdown']['rating'], $highResult['breakdown']['rating']);
    }

    public function test_category_only_match_uses_category_coverage(): void
    {
        $php = $this->makeSkill(1, 'PHP', 'Programming');
        $java = $this->makeSkill(2, 'Java', 'Programming');

        $request = $this->makeRequest(1, 1, 1, [$php], []);

        $user = $this->makeUser(10, [
            'Avg_Rating' => 3.0,
            'Total_Completed' => 2,
            'Is_Verified' => true,
        ]);
        $user->setRelation('skills', collect([$java]));

        $result = $this->recommender->score($request, $user);

        $this->assertEquals(0.0, $result['breakdown']['skill_overlap']);
        $this->assertEquals(1.0, $result['breakdown']['category_coverage']);
    }

    public function test_score_breakdown_contains_all_dimensions(): void
    {
        $php = $this->makeSkill(1, 'PHP', 'Programming');

        $request = $this->makeRequest(1, 1, 1, [$php], []);

        $user = $this->makeUser(10);
        $user->setRelation('skills', collect([$php]));

        $result = $this->recommender->score($request, $user);

        $this->assertArrayHasKey('skill_overlap', $result['breakdown']);
        $this->assertArrayHasKey('category_coverage', $result['breakdown']);
        $this->assertArrayHasKey('service_mode', $result['breakdown']);
        $this->assertArrayHasKey('profile_tags', $result['breakdown']);
        $this->assertArrayHasKey('rating', $result['breakdown']);
        $this->assertArrayHasKey('profile_quality', $result['breakdown']);
        $this->assertArrayHasKey('score', $result);
        $this->assertArrayHasKey('raw_score', $result);
        $this->assertArrayHasKey('user', $result);
    }

    public function test_custom_weights_normalise_correctly(): void
    {
        $recommender = new Recommender([
            'weights' => [
                'skill_overlap' => 0.60,
                'category_coverage' => 0.20,
                'rating' => 0.10,
                'profile_quality' => 0.10,
            ],
        ]);

        $php = $this->makeSkill(1, 'PHP', 'Programming');
        $js = $this->makeSkill(2, 'JavaScript', 'Programming');

        $request = $this->makeRequest(1, 1, 1, [$php, $js], [2]);

        $user = $this->makeUser(10, [
            'Avg_Rating' => 5.0,
            'Total_Completed' => 10,
            'Is_Verified' => true,
        ]);
        $user->setRelation('skills', collect([$php, $js]));

        $result = $recommender->score($request, $user);

        $this->assertEquals(100.0, $result['score']);
    }

    public function test_ranked_results_are_sorted_descending(): void
    {
        $php = $this->makeSkill(1, 'PHP', 'Programming');
        $js = $this->makeSkill(2, 'JavaScript', 'Programming');
        $python = $this->makeSkill(3, 'Python', 'Programming');

        $request = $this->makeRequest(1, 1, 1, [$php, $js], [2]);

        $best = $this->makeUser(10, [
            'Avg_Rating' => 4.8,
            'Total_Completed' => 10,
            'Is_Verified' => true,
        ]);
        $best->setRelation('skills', collect([$php, $js]));

        $mid = $this->makeUser(11, [
            'Avg_Rating' => 3.5,
            'Total_Completed' => 3,
            'Is_Verified' => true,
        ]);
        $mid->setRelation('skills', collect([$php]));

        $worst = $this->makeUser(12, [
            'Avg_Rating' => 2.0,
            'Total_Completed' => 1,
            'Is_Verified' => false,
        ]);
        $worst->setRelation('skills', collect([$python]));

        $results = $this->makeProviders($request, [$worst, $best, $mid]);

        usort($results, fn ($a, $b) => $b['score'] <=> $a['score']);

        $this->assertEquals($best->User_ID, $results[0]['user']->User_ID);
        $this->assertEquals($mid->User_ID, $results[1]['user']->User_ID);
        $this->assertEquals($worst->User_ID, $results[2]['user']->User_ID);

        $scores = array_column($results, 'score');
        $expectSorted = $scores;
        rsort($expectSorted);
        $this->assertEquals($expectSorted, $scores);
    }

    public function test_min_match_score_filter(): void
    {
        $recommender = new Recommender([
            'min_match_score' => 50.0,
        ]);

        $php = $this->makeSkill(1, 'PHP', 'Programming');
        $graphic = $this->makeSkill(2, 'Graphic Design', 'Design');

        $request = $this->makeRequest(1, 1, 1, [$php], []);

        $lowQuality = $this->makeUser(10, [
            'Avg_Rating' => 2.0,
            'Total_Completed' => 0,
            'Is_Verified' => false,
        ]);
        $lowQuality->setRelation('skills', collect([$graphic]));

        $result = $recommender->score($request, $lowQuality);

        $this->assertLessThan(50.0, $result['score']);
    }

    public function test_cold_start_unrated_provider_receives_prior_rating(): void
    {
        $php = $this->makeSkill(1, 'PHP', 'Programming');

        $request = $this->makeRequest(1, 1, 1, [$php], []);

        $rated = $this->makeUser(10, [
            'Avg_Rating' => 3.0,
            'Total_Completed' => 5,
            'Is_Verified' => true,
        ]);
        $rated->setRelation('skills', collect([$php])->map(function ($skill) {
            $skill->setRelation('pivot', (object) ['Proficiency' => 3]);
            return $skill;
        }));

        $unrated = $this->makeUser(11, [
            'Avg_Rating' => 0,
            'Total_Completed' => 0,
            'Is_Verified' => true,
        ]);
        $unrated->setRelation('skills', collect([$php])->map(function ($skill) {
            $skill->setRelation('pivot', (object) ['Proficiency' => 3]);
            return $skill;
        }));

        $recommender = new Recommender;

        $ratedResult = $recommender->score($request, $rated);
        $unratedResult = $recommender->score($request, $unrated);
        $ratedRating = $ratedResult['breakdown']['rating'];
        $unratedRating = $unratedResult['breakdown']['rating'];

        $this->assertGreaterThan(0, $unratedRating, 'Cold-start provider should receive a prior rating score.');
        $this->assertGreaterThanOrEqual($unratedRating, $ratedRating, 'Rated provider should score at least as high as cold-start prior.');
    }
}
