<?php

namespace Tests\Feature\Analytics;

use App\Models\Assignment;
use App\Models\Review;
use App\Models\Skill;
use App\Models\SkillRequest;
use App\Models\User;
use App\Models\UserMatch;
use App\Services\Analytics\AnalyticsService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class AnalyticsDashboardTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $provider1;

    private User $provider2;

    private User $provider3;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow(Carbon::create(2026, 8, 15, 12, 0, 0));

        $this->admin = User::create([
            'Full_Name' => 'Admin',
            'Email' => 'admin@test.com',
            'Password_Hash' => 'hash',
            'Role' => 'Admin',
            'Is_Verified' => true,
            'Account_Status' => 'Active',
            'Avg_Rating' => 4.0,
            'Total_Completed' => 10,
        ]);

        $this->provider1 = User::create([
            'Full_Name' => 'Provider One',
            'Email' => 'provider1@test.com',
            'Password_Hash' => 'hash',
            'Role' => 'Student',
            'Is_Verified' => true,
            'Account_Status' => 'Active',
            'Avg_Rating' => 4.8,
            'Total_Completed' => 20,
        ]);

        $this->provider2 = User::create([
            'Full_Name' => 'Provider Two',
            'Email' => 'provider2@test.com',
            'Password_Hash' => 'hash',
            'Role' => 'Student',
            'Is_Verified' => true,
            'Account_Status' => 'Active',
            'Avg_Rating' => 4.5,
            'Total_Completed' => 15,
        ]);

        $this->provider3 = User::create([
            'Full_Name' => 'Provider Three',
            'Email' => 'provider3@test.com',
            'Password_Hash' => 'hash',
            'Role' => 'Student',
            'Is_Verified' => true,
            'Account_Status' => 'Active',
            'Avg_Rating' => 4.2,
            'Total_Completed' => 5,
        ]);

        User::create([
            'Full_Name' => 'Invalid Provider',
            'Email' => 'invalid@test.com',
            'Password_Hash' => 'hash',
            'Role' => 'Student',
            'Is_Verified' => true,
            'Account_Status' => 'Active',
            'Avg_Rating' => 5.0,
            'Total_Completed' => 2,
        ]);

        Skill::insert([
            ['Skill_ID' => 1, 'Skill_Title' => 'PHP', 'Category' => 'Programming'],
            ['Skill_ID' => 2, 'Skill_Title' => 'JavaScript', 'Category' => 'Programming'],
            ['Skill_ID' => 3, 'Skill_Title' => 'MySQL', 'Category' => 'Database'],
            ['Skill_ID' => 4, 'Skill_Title' => 'React', 'Category' => 'Frontend'],
        ]);

        $this->provider1->skills()->attach([1, 2]);
        $this->provider2->skills()->attach([3]);
        $this->provider3->skills()->attach([4]);

        Auth::login($this->admin);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function createRequest(int $skillId, string $title, ?Carbon $createdAt = null): SkillRequest
    {
        return SkillRequest::create([
            'User_ID' => $this->admin->User_ID,
            'Skill_ID' => $skillId,
            'Title' => $title,
            'Description' => "Description for {$title}",
            'Status' => 'Open',
            'Service_Mode' => 'Remote',
            'Created_At' => $createdAt ?? now(),
        ]);
    }

    private function createAssignment(SkillRequest $request, User $provider, string $status, ?Carbon $createdAt = null): Assignment
    {
        return Assignment::create([
            'Request_ID' => $request->Request_ID,
            'User_ID' => $provider->User_ID,
            'Status' => $status,
            'Created_At' => $createdAt ?? now(),
        ]);
    }

    // --- Access control ---

    public function test_analytics_dashboard_accessible_by_admin(): void
    {
        $response = $this->get('/analytics');

        $response->assertStatus(200);
        $response->assertSee('Analytics Dashboard');
    }

    public function test_analytics_dashboard_forbidden_for_non_admin(): void
    {
        Auth::logout();
        $this->actingAs($this->provider1);

        $response = $this->get('/analytics');

        $response->assertStatus(403);
    }

    public function test_analytics_dashboard_redirects_guests(): void
    {
        Auth::logout();

        $response = $this->get('/analytics');

        $response->assertStatus(302);
    }

    // --- KPI 1: Monthly Request Volume ---

    public function test_monthly_request_volume_returns_correct_data(): void
    {
        SkillRequest::query()->delete();

        $this->createRequest(1, 'R-current-1', now());
        $this->createRequest(1, 'R-current-2', now());
        $this->createRequest(1, 'R-current-3', now());
        $this->createRequest(1, 'R-2mo-1', now()->subMonths(2));
        $this->createRequest(1, 'R-2mo-2', now()->subMonths(2));
        $this->createRequest(1, 'R-6mo-1', now()->subMonths(6));

        $service = new AnalyticsService;
        $result = $service->monthlyRequestVolume(12);

        $this->assertCount(12, $result['data']);
        $this->assertCount(12, $result['labels']);
        $this->assertEquals(3, $result['data'][11]);
        $this->assertEquals(2, $result['data'][9]);
        $this->assertEquals(1, $result['data'][5]);
        $this->assertEquals(6, array_sum($result['data']));
    }

    public function test_monthly_request_volume_zero_when_no_requests(): void
    {
        SkillRequest::query()->delete();

        $result = (new AnalyticsService)->monthlyRequestVolume(12);

        $this->assertSame(array_fill(0, 12, 0), $result['data']);
    }

    // --- KPI 2: Top-Rated Providers ---

    public function test_top_rated_providers_sorted_by_rating(): void
    {
        $providers = (new AnalyticsService)->topRatedProviders(5);

        $this->assertCount(3, $providers);
        $this->assertEquals('Provider One', $providers[0]->Full_Name);
        $this->assertEquals(4.8, (float) $providers[0]->Avg_Rating);
        $this->assertEquals('Provider Two', $providers[1]->Full_Name);
        $this->assertEquals(4.5, (float) $providers[1]->Avg_Rating);
        $this->assertEquals('Provider Three', $providers[2]->Full_Name);
        $this->assertEquals(4.2, (float) $providers[2]->Avg_Rating);
    }

    public function test_top_rated_providers_excludes_insufficient_completions(): void
    {
        $providers = (new AnalyticsService)->topRatedProviders(5);

        foreach ($providers as $p) {
            $this->assertGreaterThanOrEqual(3, (int) $p->Total_Completed);
        }
    }

    public function test_top_rated_providers_have_skills_eager_loaded(): void
    {
        $providers = (new AnalyticsService)->topRatedProviders(5);

        $this->assertNotEmpty($providers[0]->skills);
        $this->assertEquals('PHP', $providers[0]->skills->first()->Skill_Title);
    }

    // --- KPI 3: Transaction Completion Rate ---

    public function test_transaction_completion_rate_with_mixed_statuses(): void
    {
        Assignment::query()->delete();
        SkillRequest::query()->delete();

        $r = $this->createRequest(1, 'Rate Test');
        $this->createAssignment($r, $this->provider1, 'Completed');
        $this->createAssignment($this->createRequest(1, 'T2'), $this->provider1, 'Completed');
        $this->createAssignment($this->createRequest(1, 'T3'), $this->provider1, 'Completed');
        $this->createAssignment($this->createRequest(1, 'T4'), $this->provider1, 'Completed');
        $this->createAssignment($this->createRequest(1, 'T5'), $this->provider1, 'Failed');

        $rate = (new AnalyticsService)->transactionCompletionRate();

        $this->assertEquals(80.0, $rate);
    }

    public function test_transaction_completion_rate_zero_when_no_resolved(): void
    {
        Assignment::query()->delete();

        $rate = (new AnalyticsService)->transactionCompletionRate();

        $this->assertEquals(0.0, $rate);
    }

    // --- KPI 4: Monthly Match Volume ---

    public function test_monthly_match_volume_returns_correct_data(): void
    {
        UserMatch::query()->delete();

        UserMatch::create([
            'Matched_User_ID' => $this->provider1->User_ID,
            'Request_ID' => $this->createRequest(1, 'M1')->Request_ID,
            'Match_Score' => 85.5,
            'Matched_At' => now(),
        ]);
        UserMatch::create([
            'Matched_User_ID' => $this->provider2->User_ID,
            'Request_ID' => $this->createRequest(1, 'M2')->Request_ID,
            'Match_Score' => 72.3,
            'Matched_At' => now(),
        ]);
        UserMatch::create([
            'Matched_User_ID' => $this->provider3->User_ID,
            'Request_ID' => $this->createRequest(1, 'M3')->Request_ID,
            'Match_Score' => 90.1,
            'Matched_At' => now()->subMonths(2),
        ]);

        $result = (new AnalyticsService)->monthlyMatchVolume(12);

        $this->assertCount(12, $result['data']);
        $this->assertEquals(2, $result['data'][11]);
        $this->assertEquals(1, $result['data'][9]);
    }

    // --- KPI 5: Completion Rate by Category ---

    public function test_completion_rate_by_category(): void
    {
        Assignment::query()->delete();
        SkillRequest::query()->delete();

        // Programming: 2 Completed, 1 Failed => 66.7%
        $r1 = $this->createRequest(1, 'PHP-1');
        $this->createAssignment($r1, $this->provider1, 'Completed');
        $this->createAssignment($this->createRequest(1, 'PHP-2'), $this->provider1, 'Completed');
        $this->createAssignment($this->createRequest(1, 'PHP-3'), $this->provider1, 'Failed');

        // Database: 1 Completed, 0 Failed => 100%
        $this->createAssignment($this->createRequest(3, 'DB-1'), $this->provider2, 'Completed');

        // Frontend: 0 Completed, 1 Failed => 0%
        $this->createAssignment($this->createRequest(4, 'FE-1'), $this->provider3, 'Failed');

        $result = (new AnalyticsService)->completionRateByCategory();

        $idx = array_flip($result['labels']);

        $this->assertArrayHasKey('Programming', $idx);
        $this->assertEquals(66.7, $result['data'][$idx['Programming']]);

        $this->assertArrayHasKey('Database', $idx);
        $this->assertEquals(100.0, $result['data'][$idx['Database']]);

        $this->assertArrayHasKey('Frontend', $idx);
        $this->assertEquals(0.0, $result['data'][$idx['Frontend']]);
    }

    // --- KPI 6: Active Provider Count ---

    public function test_active_provider_count_within_window(): void
    {
        Assignment::query()->delete();
        SkillRequest::query()->delete();

        $r1 = $this->createRequest(1, 'A1');
        $this->createAssignment($r1, $this->provider1, 'Accepted', now());
        $this->createAssignment($this->createRequest(1, 'A2'), $this->provider2, 'Accepted', now());
        $this->createAssignment($this->createRequest(1, 'A3'), $this->provider3, 'Completed', now()->subDays(31));

        $count = (new AnalyticsService)->activeProviderCount(30);

        $this->assertEquals(2, $count);
    }

    public function test_active_provider_count_excludes_rejected(): void
    {
        Assignment::query()->delete();
        SkillRequest::query()->delete();

        $r = $this->createRequest(1, 'AR1');
        $this->createAssignment($r, $this->provider1, 'Rejected', now());
        $this->createAssignment($this->createRequest(1, 'AR2'), $this->provider2, 'Accepted', now());

        $count = (new AnalyticsService)->activeProviderCount(30);

        $this->assertEquals(1, $count);
    }

    // --- KPI 7: Most Active Providers ---

    public function test_most_active_providers_ordered_by_completions(): void
    {
        $providers = (new AnalyticsService)->mostActiveProviders(5);

        $this->assertCount(4, $providers);
        $this->assertEquals('Provider One', $providers[0]->Full_Name);
        $this->assertEquals(20, (int) $providers[0]->Total_Completed);
        $this->assertEquals('Provider Two', $providers[1]->Full_Name);
        $this->assertEquals(15, (int) $providers[1]->Total_Completed);
        $this->assertEquals('Provider Three', $providers[2]->Full_Name);
        $this->assertEquals(5, (int) $providers[2]->Total_Completed);
        $this->assertEquals('Invalid Provider', $providers[3]->Full_Name);
        $this->assertEquals(2, (int) $providers[3]->Total_Completed);
    }

    // --- KPI 8: Most Requested Skill Categories ---

    public function test_most_requested_skill_categories(): void
    {
        SkillRequest::query()->delete();

        $this->createRequest(1, 'R1');
        $this->createRequest(1, 'R2');
        $this->createRequest(2, 'R3');
        $this->createRequest(3, 'R4');

        $result = (new AnalyticsService)->mostRequestedSkillCategories(5);

        $this->assertNotEmpty($result);
        $this->assertEquals('Programming', $result[0]->Category);
        $this->assertEquals(3, (int) $result[0]->request_count);
    }

    // --- KPI 9: Average User Rating Trend ---

    public function test_average_user_rating_trend_returns_monthly_data(): void
    {
        Review::query()->delete();
        SkillRequest::query()->delete();

        $r = $this->createRequest(1, 'RV1');
        Review::create([
            'Reviewed_User_ID' => $this->provider1->User_ID,
            'Reviewer_ID' => $this->admin->User_ID,
            'Request_ID' => $r->Request_ID,
            'Rating' => 5,
            'Comment' => 'Great',
            'Created_At' => now()->subMonths(1),
        ]);
        Review::create([
            'Reviewed_User_ID' => $this->provider2->User_ID,
            'Reviewer_ID' => $this->admin->User_ID,
            'Request_ID' => $r->Request_ID,
            'Rating' => 3,
            'Comment' => 'Okay',
            'Created_At' => now()->subMonths(1),
        ]);
        Review::create([
            'Reviewed_User_ID' => $this->provider3->User_ID,
            'Reviewer_ID' => $this->admin->User_ID,
            'Request_ID' => $r->Request_ID,
            'Rating' => 4,
            'Comment' => 'Good',
            'Created_At' => now(),
        ]);

        $result = (new AnalyticsService)->averageUserRatingTrend(12);

        $this->assertCount(12, $result['labels']);
        $this->assertCount(12, $result['data']);
        $this->assertEquals(4.0, $result['data'][10]);
        $this->assertEquals(4.0, $result['data'][11]);
    }

    // --- All Metrics Integration ---

    public function test_all_metrics_returns_all_nine_kpis(): void
    {
        $metrics = (new AnalyticsService)->allMetrics();

        $this->assertArrayHasKey('monthly_request_volume', $metrics);
        $this->assertArrayHasKey('top_rated_providers', $metrics);
        $this->assertArrayHasKey('transaction_completion_rate', $metrics);
        $this->assertArrayHasKey('monthly_match_volume', $metrics);
        $this->assertArrayHasKey('completion_rate_by_category', $metrics);
        $this->assertArrayHasKey('active_provider_count', $metrics);
        $this->assertArrayHasKey('most_active_providers', $metrics);
        $this->assertArrayHasKey('most_requested_categories', $metrics);
        $this->assertArrayHasKey('average_rating_trend', $metrics);
    }

    public function test_dashboard_displays_all_nine_kpis(): void
    {
        $response = $this->get('/analytics');

        $response->assertStatus(200);
        $response->assertSee('Monthly Request Volume');
        $response->assertSee('Top-Rated Providers');
        $response->assertSee('Transaction Completion Rate');
        $response->assertSee('Monthly Match Volume');
        $response->assertSee('Completion Rate by Category');
        $response->assertSee('Active Providers (30d)');
        $response->assertSee('Most Active Service Providers');
        $response->assertSee('Most Requested Skill Categories');
        $response->assertSee('Average User Rating Trend');
    }

    public function test_report_download_generates_csv(): void
    {
        $response = $this->get('/analytics/report');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $response->assertHeader('Content-Disposition', 'attachment; filename="campusskill-analytics-report-' . now()->format('Y-m-d') . '.csv"');
        $body = $response->getContent();
        $this->assertStringContainsString('Section', $body);
        $this->assertStringContainsString('Total Service Requests', $body);
        $this->assertStringContainsString('Most Requested Categories', $body);
        $this->assertStringContainsString('Average Rating Trend', $body);
    }
}
