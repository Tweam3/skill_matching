<?php

namespace App\Services\Matching;

use App\Models\Skill;
use App\Models\SkillRequest;
use App\Models\User;
use App\Models\UserMatch;

class Recommender
{
    public const WEIGHT_SKILL = 'skill_overlap';

    public const WEIGHT_CATEGORY = 'category_coverage';

    public const WEIGHT_SERVICE_MODE = 'service_mode';

    public const WEIGHT_PROFILE_TAGS = 'profile_tags';

    public const WEIGHT_RATING = 'rating';

    public const WEIGHT_PROFILE = 'profile_quality';

    protected array $weights;

    protected float $minMatchScore;

    protected int $maxCompletedForConfidence;

    protected int $maxCompletedForExperience;

    protected float $maxRating;

    protected int $defaultLimit;

    public function __construct(?array $config = null)
    {
        $config = $config ?? config('matching') ?? [];

        $this->weights = $config['weights'] ?? [
            self::WEIGHT_SKILL => 0.30,
            self::WEIGHT_CATEGORY => 0.20,
            self::WEIGHT_SERVICE_MODE => 0.15,
            self::WEIGHT_PROFILE_TAGS => 0.15,
            self::WEIGHT_RATING => 0.12,
            self::WEIGHT_PROFILE => 0.08,
        ];

        $this->minMatchScore = $config['min_match_score'] ?? 15.0;
        $this->maxCompletedForConfidence = $config['max_completed_for_confidence'] ?? 5;
        $this->maxCompletedForExperience = $config['max_completed_for_experience'] ?? 10;
        $this->maxRating = $config['max_rating'] ?? 5;
        $this->defaultLimit = $config['default_limit'] ?? 20;
    }

    /**
     * Score a single provider against a single request.
     *
     * The request must have its primary skill (skill) and additional skills
     * (skills) relations available.  The provider must have its skills
     * relation loaded.
     *
     * Returns an array with the composite score (0-100), the raw 0-1 score,
     * and a per-dimension breakdown.
     */
    public function score(SkillRequest $request, User $provider): array
    {
        $requested = $this->extractRequestSkills($request);
        $providerSkillIds = $provider->skills->pluck('Skill_ID')->all();
        $providerCategories = $provider->skills->pluck('Category')->filter()->unique()->values()->all();
        $providerProficiencyMap = $provider->skills->pluck('pivot.Proficiency', 'Skill_ID')->all();

        $skillOverlap = $this->skillOverlapScore($requested['skill_ids'], $providerSkillIds);
        $categoryCoverage = $this->categoryCoverageScore($requested['categories'], $providerCategories);
        $serviceMode = $this->serviceModeScore($request, $provider);
        $profileTags = $this->profileTagsScore($requested['skill_ids'], $providerProficiencyMap);
        $rating = $this->ratingScore($provider);
        $profileQuality = $this->profileQualityScore($provider);

        $rawScore = $this->weightedScore($skillOverlap, $categoryCoverage, $serviceMode, $profileTags, $rating, $profileQuality);

        return [
            'user' => $provider,
            'score' => round($rawScore * 100, 2),
            'raw_score' => $rawScore,
            'breakdown' => [
                'skill_overlap' => round($skillOverlap, 4),
                'category_coverage' => round($categoryCoverage, 4),
                'service_mode' => round($serviceMode, 4),
                'profile_tags' => round($profileTags, 4),
                'rating' => round($rating, 4),
                'profile_quality' => round($profileQuality, 4),
            ],
        ];
    }

    /**
     * Rank all eligible providers for a given request.
     *
     * Queries the database for candidate providers, scores each one, filters
     * by the minimum match score, and returns the results sorted by score
     * descending.
     */
    public function rankForRequest(SkillRequest $request, ?int $limit = null): array
    {
        $request->load(['skill', 'skills']);

        $requested = $this->extractRequestSkills($request);
        $requestedCategories = $requested['categories'];

        $candidateSkillIds = $this->candidateSkillIds($requested['skill_ids'], $requestedCategories);

        $candidates = User::query()
            ->where('Role', 'Student')
            ->where('Account_Status', 'Active')
            ->where('User_ID', '!=', $request->User_ID)
            ->whereHas('skills', function ($q) use ($candidateSkillIds) {
                $q->whereIn('skills.Skill_ID', $candidateSkillIds);
            })
            ->with('skills')
            ->get();

        $results = [];

        foreach ($candidates as $candidate) {
            $result = $this->score($request, $candidate);

            if ($result['score'] >= $this->minMatchScore) {
                $results[] = $result;
            }
        }

        usort($results, fn ($a, $b) => $b['raw_score'] <=> $a['raw_score']);

        if ($limit !== null) {
            $results = array_slice($results, 0, $limit);
        }

        return $results;
    }

    /**
     * Evaluate the recommender against a set of test cases where the ground
     * truth (which providers actually matched successfully) is known.
     *
     * Each test case is:
     *   ['request' => SkillRequest, 'successful_provider_ids' => [int,...]]
     *
     * Returns aggregate IR metrics: Precision@K, Recall@K, MRR, and MAP.
     */
    public function evaluate(array $testCases, int $k = 5): array
    {
        if (empty($testCases)) {
            return [
                'precision_at_k' => 0.0,
                'recall_at_k' => 0.0,
                'mrr' => 0.0,
                'map' => 0.0,
                'total_requests' => 0,
            ];
        }

        $precisions = [];
        $recalls = [];
        $reciprocalRanks = [];
        $averagePrecisions = [];

        foreach ($testCases as $case) {
            $request = $case['request'];
            $successfulIds = $case['successful_provider_ids'];

            $ranked = $this->rankForRequest($request);
            $rankedIds = array_map(fn ($r) => $r['user']->User_ID, $ranked);
            $rankedCount = count($rankedIds);

            $topK = array_slice($rankedIds, 0, $k);
            $relevantCount = max(count($successfulIds), 1);

            $hitsInTopK = count(array_intersect($topK, $successfulIds));
            $precisions[] = $hitsInTopK / $k;
            $recalls[] = min($hitsInTopK / count($successfulIds), 1.0);

            $rr = 0.0;
            foreach ($rankedIds as $rank => $id) {
                if (in_array($id, $successfulIds, true)) {
                    $rr = 1.0 / ($rank + 1);
                    break;
                }
            }
            $reciprocalRanks[] = $rr;

            $ap = 0.0;
            $hitCount = 0;
            foreach ($rankedIds as $rank => $id) {
                if (in_array($id, $successfulIds, true)) {
                    $hitCount++;
                    $ap += $hitCount / ($rank + 1);
                }
            }
            $averagePrecisions[] = $ap / min($relevantCount, $rankedCount);
        }

        return [
            'precision_at_k' => round(array_sum($precisions) / count($precisions), 4),
            'recall_at_k' => round(array_sum($recalls) / count($recalls), 4),
            'mrr' => round(array_sum($reciprocalRanks) / count($reciprocalRanks), 4),
            'map' => round(array_sum($averagePrecisions) / count($averagePrecisions), 4),
            'total_requests' => count($testCases),
        ];
    }

    /**
     * Persist the ranked matches for a given request into the matches table.
     */
    public function persistMatches(SkillRequest $request, ?int $limit = null): int
    {
        $limit = $limit ?? $this->defaultLimit;

        UserMatch::where('Request_ID', $request->Request_ID)->delete();

        $recommendations = $this->rankForRequest($request, $limit);

        foreach ($recommendations as $rec) {
            UserMatch::create([
                'Matched_User_ID' => $rec['user']->User_ID,
                'Request_ID' => $request->Request_ID,
                'Match_Score' => $rec['score'],
            ]);
        }

        return count($recommendations);
    }

    /*
    |--------------------------------------------------------------------------
    | Scoring primitives
    |--------------------------------------------------------------------------
    */

    /**
     * Extract the full set of skill IDs and categories from a request,
     * merging the primary skill (Skill_ID column) with the many-to-many
     * additional skills (request_skills pivot).
     */
    protected function extractRequestSkills(SkillRequest $request): array
    {
        $allSkills = $request->skills
            ->merge([$request->skill])
            ->unique('Skill_ID')
            ->values();

        return [
            'skill_ids' => $allSkills->pluck('Skill_ID')->all(),
            'categories' => $allSkills->pluck('Category')->filter()->unique()->values()->all(),
        ];
    }

    /**
     * Resolve the full candidate skill pool: the requested skill IDs plus
     * every skill that shares a category with any requested skill.
     */
    protected function candidateSkillIds(array $skillIds, array $categories): array
    {
        if (empty($categories)) {
            return $skillIds;
        }

        $relatedIds = Skill::whereIn('Category', $categories)
            ->pluck('Skill_ID')
            ->all();

        return array_values(array_unique(array_merge($skillIds, $relatedIds)));
    }

    /**
     * Jaccard similarity between requested and provider skill sets.
     */
    protected function skillOverlapScore(array $requestedIds, array $providerIds): float
    {
        $requestedIdSet = array_values(array_unique($requestedIds));
        $providerIdSet = array_values(array_unique($providerIds));

        if (empty($requestedIdSet) || empty($providerIdSet)) {
            return 0.0;
        }

        $intersection = count(array_intersect($requestedIdSet, $providerIdSet));
        $union = count(array_unique(array_merge($requestedIdSet, $providerIdSet)));

        return $union > 0 ? $intersection / $union : 0.0;
    }

    /**
     * Proportion of requested categories covered by the provider's skills.
     */
    protected function categoryCoverageScore(array $requestedCategories, array $providerCategories): float
    {
        if (empty($requestedCategories)) {
            return 0.0;
        }

        $covered = count(array_intersect($requestedCategories, $providerCategories));

        return $covered / count($requestedCategories);
    }

    /**
     * Normalised rating with a confidence factor that ramps up as the
     * provider completes more transactions.
     */
    protected function ratingScore(User $provider): float
    {
        $normalized = $this->maxRating > 0
            ? (float) $provider->Avg_Rating / $this->maxRating
            : 0.0;

        $confidence = $this->maxCompletedForConfidence > 0
            ? min((int) $provider->Total_Completed / $this->maxCompletedForConfidence, 1.0)
            : 0.0;

        return $normalized * (0.5 + 0.5 * $confidence);
    }

    /**
     * Provider profile quality: verification status, account health, and
     * experience (completed transactions, saturating at max).
     */
    protected function profileQualityScore(User $provider): float
    {
        $isVerified = $provider->Is_Verified ? 1.0 : 0.0;
        $isActive = $provider->Account_Status === 'Active' ? 1.0 : 0.0;

        $experience = $this->maxCompletedForExperience > 0
            ? min((int) $provider->Total_Completed / $this->maxCompletedForExperience, 1.0)
            : 0.0;

        $trust = $isVerified * 0.6 + $isActive * 0.4;

        return $trust * (0.3 + 0.7 * $experience);
    }

    /**
     * Service mode compatibility between request and provider.
     *
     * Returns 1.0 if the provider offers the requested service mode,
     * 0.5 if the provider has not configured service modes (legacy data),
     * and 0.0 otherwise. Hybrid requests are compatible with providers
     * offering Remote or Face-to-Face.
     */
    protected function serviceModeScore(SkillRequest $request, User $provider): float
    {
        $requestMode = $request->Service_Mode;

        if (! $requestMode) {
            return 0.5;
        }

        $providerModes = $provider->Service_Modes;

        if (empty($providerModes)) {
            return 0.5;
        }

        if (in_array($requestMode, $providerModes, true)) {
            return 1.0;
        }

        if ($requestMode === 'Hybrid') {
            $hasRemote = in_array('Remote', $providerModes, true);
            $hasF2F = in_array('Face-to-Face', $providerModes, true);

            if ($hasRemote || $hasF2F) {
                return 1.0;
            }
        }

        return 0.0;
    }

    /**
     * Profile tag matching: proportion of requested skills that the provider
     * has with at least Competent proficiency (level 3 or higher).
     */
    protected function profileTagsScore(array $requestedSkillIds, array $providerProficiencyMap): float
    {
        if (empty($requestedSkillIds)) {
            return 0.0;
        }

        $taggedCount = 0;

        foreach ($requestedSkillIds as $skillId) {
            $proficiency = $providerProficiencyMap[$skillId] ?? 0;

            if ($proficiency >= 3) {
                $taggedCount++;
            }
        }

        return $taggedCount / count($requestedSkillIds);
    }

    /**
     * Combine all dimension scores using configured weights.
     */
    protected function weightedScore(float $skill, float $category, float $serviceMode, float $profileTags, float $rating, float $profile): float
    {
        $weights = $this->weights;

        $total = ($weights[self::WEIGHT_SKILL] ?? 0)
            + ($weights[self::WEIGHT_CATEGORY] ?? 0)
            + ($weights[self::WEIGHT_SERVICE_MODE] ?? 0)
            + ($weights[self::WEIGHT_PROFILE_TAGS] ?? 0)
            + ($weights[self::WEIGHT_RATING] ?? 0)
            + ($weights[self::WEIGHT_PROFILE] ?? 0);

        if ($total <= 0) {
            return 0.0;
        }

        return (($skill * ($weights[self::WEIGHT_SKILL] ?? 0))
            + ($category * ($weights[self::WEIGHT_CATEGORY] ?? 0))
            + ($serviceMode * ($weights[self::WEIGHT_SERVICE_MODE] ?? 0))
            + ($profileTags * ($weights[self::WEIGHT_PROFILE_TAGS] ?? 0))
            + ($rating * ($weights[self::WEIGHT_RATING] ?? 0))
            + ($profile * ($weights[self::WEIGHT_PROFILE] ?? 0))) / $total;
    }
}
