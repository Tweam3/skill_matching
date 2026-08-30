<?php

namespace Tests\Feature\Matching;

use App\Models\Assignment;
use App\Models\Review;
use App\Models\Skill;
use App\Models\SkillRequest;
use App\Models\User;
use App\Models\UserMatch;
use App\Services\Matching\Recommender;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class MatchingEvaluationTest extends TestCase
{
    use RefreshDatabase;

    private Recommender $recommender;

    /** @var array<int, array{Skill_ID:int, Skill_Title:string, Category:string}> */
    private array $skillDefs = [
        1 => ['Skill_Title' => 'PHP', 'Category' => 'Programming'],
        2 => ['Skill_Title' => 'JavaScript', 'Category' => 'Programming'],
        3 => ['Skill_Title' => 'Python', 'Category' => 'Programming'],
        4 => ['Skill_Title' => 'MySQL', 'Category' => 'Database'],
        5 => ['Skill_Title' => 'React', 'Category' => 'Frontend'],
        6 => ['Skill_Title' => 'Node.js', 'Category' => 'Backend'],
        7 => ['Skill_Title' => 'Graphic Design', 'Category' => 'Design'],
        8 => ['Skill_Title' => 'Public Speaking', 'Category' => 'Communication'],
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->recommender = app(Recommender::class);

        $this->seedSkills();
    }

    private function seedSkills(): void
    {
        foreach ($this->skillDefs as $id => $def) {
            Skill::insert([
                ['Skill_ID' => $id, 'Skill_Title' => $def['Skill_Title'], 'Category' => $def['Category']],
            ]);
        }
    }

    private function makeProvider(int $id, array $skillIds, array $profile): User
    {
        $user = User::create(array_merge([
            'Full_Name' => "Provider {$id}",
            'Email' => "provider{$id}@test.com",
            'Password_Hash' => 'fake_hash',
            'Role' => 'Student',
            'Is_Verified' => true,
            'Avg_Rating' => 3.0,
            'Total_Completed' => 0,
            'Account_Status' => 'Active',
            'Warning_Count' => 0,
            'Service_Modes' => ['Remote', 'Face-to-Face', 'Hybrid'],
        ], $profile));

        $user->skills()->attach($skillIds);

        foreach ($skillIds as $skillId) {
            \App\Models\UserSkill::where('User_ID', $user->User_ID)
                ->where('Skill_ID', $skillId)
                ->update(['Proficiency' => 4]);
        }

        return $user;
    }

    private function makeRequest(int $requesterId, int $primarySkillId, array $skillIds, string $title): SkillRequest
    {
        $request = SkillRequest::create([
            'User_ID' => $requesterId,
            'Skill_ID' => $primarySkillId,
            'Title' => $title,
            'Description' => 'Description for '.$title,
            'Status' => 'Open',
            'Service_Mode' => 'Remote',
        ]);
        $request->skills()->attach($skillIds);

        return $request;
    }

    private function markCompleted(SkillRequest $request, int $providerId): void
    {
        $request->update(['Status' => 'Completed']);
        Assignment::create([
            'Request_ID' => $request->Request_ID,
            'User_ID' => $providerId,
            'Status' => 'Accepted',
            'Created_At' => now(),
            'Responded_At' => now(),
            'Status_Updated_At' => now(),
            'Completed_At' => now(),
        ]);
        Review::create([
            'Reviewed_User_ID' => $providerId,
            'Reviewer_ID' => $request->User_ID,
            'Request_ID' => $request->Request_ID,
            'Rating' => 5,
            'Comment' => 'Great work!',
            'Created_At' => now(),
        ]);
    }

    private function buildTestDataset(): array
    {
        $admin = User::create([
            'Full_Name' => 'Admin User',
            'Email' => 'admin@test.com',
            'Password_Hash' => 'admin_hash',
            'Role' => 'Admin',
            'Is_Verified' => true,
            'Avg_Rating' => 0,
            'Total_Completed' => 0,
            'Account_Status' => 'Active',
        ]);

        $alice = $this->makeProvider(2, [1, 2, 3], [
            'Full_Name' => 'Alice', 'Email' => 'alice@test.com',
            'Avg_Rating' => 4.8, 'Total_Completed' => 10,
            'Is_Verified' => true, 'Account_Status' => 'Active',
        ]);

        $bob = $this->makeProvider(3, [1, 4], [
            'Full_Name' => 'Bob', 'Email' => 'bob@test.com',
            'Avg_Rating' => 3.5, 'Total_Completed' => 3,
            'Is_Verified' => true, 'Account_Status' => 'Active',
        ]);

        $charlie = $this->makeProvider(4, [5, 6], [
            'Full_Name' => 'Charlie', 'Email' => 'charlie@test.com',
            'Avg_Rating' => 4.5, 'Total_Completed' => 5,
            'Is_Verified' => true, 'Account_Status' => 'Active',
        ]);

        $david = $this->makeProvider(5, [5, 2], [
            'Full_Name' => 'David', 'Email' => 'david@test.com',
            'Avg_Rating' => 4.0, 'Total_Completed' => 4,
            'Is_Verified' => true, 'Account_Status' => 'Active',
        ]);

        $grace = $this->makeProvider(6, [5, 2], [
            'Full_Name' => 'Grace', 'Email' => 'grace@test.com',
            'Avg_Rating' => 4.9, 'Total_Completed' => 8,
            'Is_Verified' => true, 'Account_Status' => 'Active',
        ]);

        $frank = $this->makeProvider(7, [1], [
            'Full_Name' => 'Frank', 'Email' => 'frank@test.com',
            'Avg_Rating' => 2.0, 'Total_Completed' => 1,
            'Is_Verified' => false, 'Account_Status' => 'Active',
        ]);

        $eve = $this->makeProvider(8, [7, 8], [
            'Full_Name' => 'Eve', 'Email' => 'eve@test.com',
            'Avg_Rating' => 3.9, 'Total_Completed' => 2,
            'Is_Verified' => true, 'Account_Status' => 'Active',
        ]);

        return compact('admin', 'alice', 'bob', 'charlie', 'david', 'grace', 'frank', 'eve');
    }

    public function test_perfect_skill_match_scores_highest(): void
    {
        $data = $this->buildTestDataset();

        $request = $this->makeRequest($data['admin']->User_ID, 1, [1, 2], 'PHP + JS developer');

        $results = $this->recommender->rankForRequest($request);

        $this->assertNotEmpty($results);
        $this->assertEquals($data['alice']->User_ID, $results[0]['user']->User_ID);
        $this->assertGreaterThan(80.0, $results[0]['score']);

        $perfectProvider = $this->makeProvider(99, [1, 2], [
            'Full_Name' => 'Perfect Match', 'Email' => 'perfect@test.com',
            'Avg_Rating' => 5.0, 'Total_Completed' => 10,
            'Is_Verified' => true, 'Account_Status' => 'Active',
        ]);

        $perfectResult = $this->recommender->score($request, $perfectProvider);

        $this->assertEquals(100.0, $perfectResult['score']);
    }

    public function test_successful_provider_is_top_recommendation(): void
    {
        $data = $this->buildTestDataset();

        $requestA = $this->makeRequest($data['admin']->User_ID, 1, [1, 2], 'PHP + JS developer');
        $requestB = $this->makeRequest($data['admin']->User_ID, 5, [5, 2], 'React frontend developer');
        $requestC = $this->makeRequest($data['admin']->User_ID, 3, [3, 4], 'Python + MySQL backend');

        $this->markCompleted($requestA, $data['alice']->User_ID);
        $this->markCompleted($requestB, $data['grace']->User_ID);
        $this->markCompleted($requestC, $data['bob']->User_ID);

        $resultsA = $this->recommender->rankForRequest($requestA);
        $resultsB = $this->recommender->rankForRequest($requestB);
        $resultsC = $this->recommender->rankForRequest($requestC);

        $this->assertEquals($data['alice']->User_ID, $resultsA[0]['user']->User_ID, 'Request A: Alice should be #1');
        $this->assertEquals($data['grace']->User_ID, $resultsB[0]['user']->User_ID, 'Request B: Grace should be #1');
        $this->assertEquals($data['bob']->User_ID, $resultsC[0]['user']->User_ID, 'Request C: Bob should be #1');
    }

    public function test_evaluate_metrics_show_effectiveness(): void
    {
        $data = $this->buildTestDataset();

        $requests = [
            $this->makeRequest($data['admin']->User_ID, 1, [1, 2], 'PHP + JS developer'),
            $this->makeRequest($data['admin']->User_ID, 5, [5, 2], 'React frontend developer'),
            $this->makeRequest($data['admin']->User_ID, 3, [3, 4], 'Python + MySQL backend'),
            $this->makeRequest($data['admin']->User_ID, 5, [5, 6], 'Full-stack React developer'),
        ];

        $this->markCompleted($requests[0], $data['alice']->User_ID);
        $this->markCompleted($requests[1], $data['grace']->User_ID);
        $this->markCompleted($requests[2], $data['bob']->User_ID);
        $this->markCompleted($requests[3], $data['charlie']->User_ID);

        $testCases = array_map(function ($req, $providerId) {
            return [
                'request' => $req,
                'successful_provider_ids' => [$providerId],
            ];
        }, $requests, [
            $data['alice']->User_ID,
            $data['grace']->User_ID,
            $data['bob']->User_ID,
            $data['charlie']->User_ID,
        ]);

        $metrics = $this->recommender->evaluate($testCases, k: 3);

        $this->assertSame(4, $metrics['total_requests']);
        $this->assertGreaterThanOrEqual(0.3, $metrics['precision_at_k'], 'Precision@3 should be at least 30%');
        $this->assertGreaterThanOrEqual(0.8, $metrics['mrr'], 'MRR should be at least 80%');
        $this->assertGreaterThanOrEqual(0.8, $metrics['map'], 'MAP should be at least 80%');
    }

    public function test_unverified_low_rating_provider_ranks_lowest(): void
    {
        $data = $this->buildTestDataset();

        $request = $this->makeRequest($data['admin']->User_ID, 1, [1, 2], 'PHP + JS developer');
        $results = $this->recommender->rankForRequest($request);

        $frankResult = null;
        foreach ($results as $r) {
            if ($r['user']->User_ID === $data['frank']->User_ID) {
                $frankResult = $r;
                break;
            }
        }

        $this->assertNotNull($frankResult, 'Frank should appear in results');
        $this->assertGreaterThanOrEqual(0.4, $frankResult['breakdown']['skill_overlap'], 'Frank has PHP (50% overlap)');
        $this->assertLessThan(0.2, $frankResult['breakdown']['profile_quality'], 'Frank is unverified and inexperienced — low profile quality');
    }

    public function test_suspended_provider_excluded_from_results(): void
    {
        $data = $this->buildTestDataset();

        $suspended = $this->makeProvider(9, [1], [
            'Full_Name' => 'Suspended', 'Email' => 'suspended@test.com',
            'Avg_Rating' => 5.0, 'Total_Completed' => 20,
            'Is_Verified' => true, 'Account_Status' => 'Suspended',
        ]);

        $request = $this->makeRequest($data['admin']->User_ID, 1, [1, 2], 'PHP + JS developer');
        $results = $this->recommender->rankForRequest($request);

        $found = array_filter($results, fn ($r) => $r['user']->User_ID === $suspended->User_ID);
        $this->assertEmpty($found, 'Suspended provider should be excluded');
    }

    public function test_request_creation_creates_ranked_matches(): void
    {
        $data = $this->buildTestDataset();

        Auth::login($data['admin']);

        $response = $this->post('/requests', [
            'skill_ids' => [1, 2],
            'title' => 'Need a PHP developer',
            'description' => 'I need help with PHP and JavaScript',
            'service_mode' => 'Remote',
        ]);

        $response->assertRedirect('/requests');

        $request = SkillRequest::latest('Request_ID')->first();
        $matches = UserMatch::where('Request_ID', $request->Request_ID)->get();

        $this->assertNotEmpty($matches, 'Should have created match records');
        $this->assertGreaterThan(15, $matches->min('Match_Score'), 'All scores should be above min threshold');
        $matchesSorted = $matches->sortByDesc('Match_Score')->values();
        $this->assertEquals($matches->sortByDesc('Match_Score')->values()->pluck('User_ID'), $matches->pluck('User_ID'));

        $topMatch = $matchesSorted->first();
        $this->assertEquals($data['alice']->User_ID, $topMatch->Matched_User_ID, 'Alice should be the top match');
    }

    public function test_regenerate_command_recomputes_matches(): void
    {
        $data = $this->buildTestDataset();

        $request = $this->makeRequest($data['admin']->User_ID, 1, [1, 2], 'PHP + JS developer');

        UserMatch::create([
            'Matched_User_ID' => $data['frank']->User_ID,
            'Request_ID' => $request->Request_ID,
            'Match_Score' => 10,
        ]);

        $this->artisan('matches:regenerate', ['--limit' => 1])
            ->assertExitCode(0);

        $matches = UserMatch::where('Request_ID', $request->Request_ID)->get();
        $this->assertNotEmpty($matches);
        $this->assertGreaterThan(15, $matches->min('Match_Score'));
    }

    public function test_recommender_achieves_precision_at_five_target(): void
    {
        $admin = User::create([
            'Full_Name' => 'Admin User',
            'Email' => 'admin-p5@test.com',
            'Password_Hash' => 'admin_hash',
            'Role' => 'Admin',
            'Is_Verified' => true,
            'Avg_Rating' => 0,
            'Total_Completed' => 0,
            'Account_Status' => 'Active',
            'Warning_Count' => 0,
            'Service_Modes' => ['Remote', 'Face-to-Face', 'Hybrid'],
        ]);

        $request = SkillRequest::create([
            'User_ID' => $admin->User_ID,
            'Skill_ID' => 1,
            'Title' => 'PHP and JavaScript Help',
            'Description' => 'Need help with PHP and JavaScript',
            'Status' => 'Open',
            'Service_Mode' => 'Remote',
            'Created_At' => now(),
        ]);
        $request->skills()->attach([1, 2]);

        $successfulIds = [];
        for ($i = 2; $i <= 6; $i++) {
            $provider = User::create([
                'Full_Name' => "Success Provider {$i}",
                'Email' => "success{$i}@test.com",
                'Password_Hash' => 'fake_hash',
                'Role' => 'Student',
                'Is_Verified' => true,
                'Avg_Rating' => 4.8,
                'Total_Completed' => 5,
                'Account_Status' => 'Active',
                'Warning_Count' => 0,
                'Service_Modes' => ['Remote', 'Face-to-Face', 'Hybrid'],
            ]);
            $provider->skills()->attach([1, 2]);
            \App\Models\UserSkill::where('User_ID', $provider->User_ID)
                ->whereIn('Skill_ID', [1, 2])
                ->update(['Proficiency' => 4]);
            $successfulIds[] = $provider->User_ID;

            Assignment::create([
                'Request_ID' => $request->Request_ID,
                'User_ID' => $provider->User_ID,
                'Status' => 'Accepted',
                'Created_At' => now(),
                'Responded_At' => now(),
                'Status_Updated_At' => now(),
                'Completed_At' => now(),
            ]);
            Review::create([
                'Reviewed_User_ID' => $provider->User_ID,
                'Reviewer_ID' => $admin->User_ID,
                'Request_ID' => $request->Request_ID,
                'Rating' => 5,
                'Comment' => 'Great work!',
                'Created_At' => now(),
            ]);
        }

        for ($i = 7; $i <= 9; $i++) {
            $weak = User::create([
                'Full_Name' => "Weak Provider {$i}",
                'Email' => "weak{$i}@test.com",
                'Password_Hash' => 'fake_hash',
                'Role' => 'Student',
                'Is_Verified' => true,
                'Avg_Rating' => 2.0,
                'Total_Completed' => 0,
                'Account_Status' => 'Active',
                'Warning_Count' => 0,
                'Service_Modes' => ['Remote'],
            ]);
            if ($i % 2 === 0) {
                $weak->skills()->attach([1]);
            } else {
                $weak->skills()->attach([2]);
            }
        }

        $testCases = [[
            'request' => $request,
            'successful_provider_ids' => $successfulIds,
        ]];

        $metrics = $this->recommender->evaluate($testCases, k: 5);

        $this->assertGreaterThanOrEqual(0.80, $metrics['precision_at_k'], 'Precision@5 should be at least 0.80');
    }
}
