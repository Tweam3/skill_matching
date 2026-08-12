<?php

namespace Tests\Feature\Moderation;

use App\Models\AdminActionLog;
use App\Models\Notification;
use App\Models\Report;
use App\Models\Skill;
use App\Models\SkillRequest;
use App\Models\User;
use App\Services\Moderation\ModerationService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class ModerationTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $reporter;

    private User $reported;

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
            'Warning_Count' => 0,
        ]);

        $this->reporter = User::create([
            'Full_Name' => 'Reporter',
            'Email' => 'reporter@test.com',
            'Password_Hash' => 'hash',
            'Role' => 'Student',
            'Is_Verified' => true,
            'Account_Status' => 'Active',
            'Warning_Count' => 0,
        ]);

        $this->reported = User::create([
            'Full_Name' => 'Reported User',
            'Email' => 'reported@test.com',
            'Password_Hash' => 'hash',
            'Role' => 'Student',
            'Is_Verified' => true,
            'Account_Status' => 'Active',
            'Warning_Count' => 0,
        ]);

        Skill::insert([
            ['Skill_ID' => 1, 'Skill_Title' => 'PHP', 'Category' => 'Programming'],
        ]);

        Auth::login($this->admin);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function createReport(User $reported): Report
    {
        $request = SkillRequest::create([
            'User_ID' => $reported->User_ID,
            'Skill_ID' => 1,
            'Title' => 'Test Request',
            'Description' => 'Test',
            'Status' => 'Open',
            'Service_Mode' => 'Remote',
            'Created_At' => now(),
        ]);

        return Report::create([
            'Reporter_ID' => $this->reporter->User_ID,
            'Reported_User_ID' => $reported->User_ID,
            'Request_ID' => $request->Request_ID,
            'Reason' => 'Inappropriate behavior',
        ]);
    }

    private function resolveReport(int $reportId): void
    {
        $this->post('/admin/reports/resolve', [
            'report_id' => $reportId,
            'approve' => 'Action_Taken',
        ]);
    }

    // --- Tier 1: Warning ---

    public function test_first_violation_sets_warning_status(): void
    {
        $report = $this->createReport($this->reported);
        $service = new ModerationService;

        $result = $service->escalateViolation($this->reported->fresh());

        $this->assertEquals('Warning', $result['status']);
        $this->assertEquals(1, $result['level']);
        $this->assertEquals('Warning', $this->reported->fresh()->Account_Status);
        $this->assertEquals(1, (int) $this->reported->fresh()->Warning_Count);
    }

    public function test_first_violation_via_http(): void
    {
        $report = $this->createReport($this->reported);
        $this->resolveReport($report->Report_ID);

        $user = $this->reported->fresh();
        $this->assertEquals('Warning', $user->Account_Status);
        $this->assertEquals(1, $user->Warning_Count);
    }

    // --- Tier 2: Suspension ---

    public function test_second_violation_sets_suspended_status(): void
    {
        $service = new ModerationService;

        $service->escalateViolation($this->reported->fresh());
        $result = $service->escalateViolation($this->reported->fresh());

        $this->assertEquals('Suspended', $result['status']);
        $this->assertEquals(2, $result['level']);
        $this->assertEquals('Suspended', $this->reported->fresh()->Account_Status);
        $this->assertEquals(2, (int) $this->reported->fresh()->Warning_Count);
    }

    public function test_second_violation_via_http(): void
    {
        $report1 = $this->createReport($this->reported);
        $this->resolveReport($report1->Report_ID);

        $report2 = $this->createReport($this->reported);
        $this->resolveReport($report2->Report_ID);

        $user = $this->reported->fresh();
        $this->assertEquals('Suspended', $user->Account_Status);
        $this->assertEquals(2, $user->Warning_Count);
    }

    // --- Tier 3: Permanent Ban ---

    public function test_third_violation_sets_banned_status(): void
    {
        $service = new ModerationService;

        $service->escalateViolation($this->reported->fresh());
        $service->escalateViolation($this->reported->fresh());
        $result = $service->escalateViolation($this->reported->fresh());

        $this->assertEquals('Banned', $result['status']);
        $this->assertEquals(3, $result['level']);
        $this->assertEquals('Banned', $this->reported->fresh()->Account_Status);
        $this->assertEquals(3, (int) $this->reported->fresh()->Warning_Count);
    }

    public function test_third_violation_via_http(): void
    {
        for ($i = 0; $i < 3; $i++) {
            $report = $this->createReport($this->reported);
            $this->resolveReport($report->Report_ID);
        }

        $user = $this->reported->fresh();
        $this->assertEquals('Banned', $user->Account_Status);
        $this->assertEquals(3, $user->Warning_Count);
    }

    // --- Beyond Tier 3: Stays Banned ---

    public function test_fourth_violation_stays_banned_and_increments(): void
    {
        $service = new ModerationService;

        $service->escalateViolation($this->reported->fresh());
        $service->escalateViolation($this->reported->fresh());
        $service->escalateViolation($this->reported->fresh());
        $result = $service->escalateViolation($this->reported->fresh());

        $this->assertEquals('Banned', $result['status']);
        $this->assertEquals(3, $result['level']);
        $this->assertEquals('Banned', $this->reported->fresh()->Account_Status);
        $this->assertEquals(4, (int) $this->reported->fresh()->Warning_Count);
    }

    // --- Dismiss ---

    public function test_dismiss_report_no_status_change(): void
    {
        $report = $this->createReport($this->reported);

        $this->post('/admin/reports/resolve', [
            'report_id' => $report->Report_ID,
            'approve' => 'Dismiss',
        ]);

        $user = $this->reported->fresh();
        $this->assertEquals('Active', $user->Account_Status);
        $this->assertEquals(0, $user->Warning_Count);

        $report->refresh();
        $this->assertEquals('Dismissed', $report->Status);
    }

    // --- Notifications ---

    public function test_resolve_report_notifies_reporter_and_reported(): void
    {
        $report = $this->createReport($this->reported);

        $this->resolveReport($report->Report_ID);

        $reporterNotif = Notification::where('User_ID', $this->reporter->User_ID)->first();
        $this->assertNotNull($reporterNotif);
        $this->assertEquals('Report', $reporterNotif->Notif_Type);

        $reportedNotif = Notification::where('User_ID', $this->reported->User_ID)->first();
        $this->assertNotNull($reportedNotif);
        $this->assertEquals('Penalty', $reportedNotif->Notif_Type);
    }

    // --- Admin Action Log ---

    public function test_resolve_report_logs_action(): void
    {
        $report = $this->createReport($this->reported);

        $this->resolveReport($report->Report_ID);

        $log = AdminActionLog::where('Action', 'resolve_report')->first();
        $this->assertNotNull($log);
        $this->assertStringContainsString('Escalated report ID', $log->Details);
        $this->assertStringContainsString('Warning', $log->Details);
    }

    public function test_resolve_report_logs_ban_on_third_violation(): void
    {
        $service = new ModerationService;

        $service->escalateViolation($this->reported->fresh());
        $service->escalateViolation($this->reported->fresh());
        $service->escalateViolation($this->reported->fresh());

        $report = $this->createReport($this->reported);
        $this->resolveReport($report->Report_ID);

        $log = AdminActionLog::where('Action', 'resolve_report')->latest('Created_At')->first();
        $this->assertStringContainsString('Banned', $log->Details);
    }

    // --- Access Control ---

    public function test_non_admin_cannot_resolve_reports(): void
    {
        Auth::logout();
        $this->actingAs($this->reporter);

        $report = $this->createReport($this->reported);

        $response = $this->post('/admin/reports/resolve', [
            'report_id' => $report->Report_ID,
            'approve' => 'Action_Taken',
        ]);

        $response->assertStatus(403);
        $this->assertEquals(0, $this->reported->fresh()->Warning_Count);
    }

    public function test_guest_cannot_resolve_reports(): void
    {
        Auth::logout();

        $report = $this->createReport($this->reported);

        $response = $this->post('/admin/reports/resolve', [
            'report_id' => $report->Report_ID,
            'approve' => 'Action_Taken',
        ]);

        $response->assertStatus(302);
        $this->assertEquals(0, $this->reported->fresh()->Warning_Count);
    }

    // --- Full Escalation Chain ---

    public function test_full_escalation_chain_through_http(): void
    {
        $statuses = [];

        for ($i = 0; $i < 4; $i++) {
            $report = $this->createReport($this->reported);
            $this->resolveReport($report->Report_ID);
            $statuses[] = $this->reported->fresh()->Account_Status;
        }

        $this->assertEquals('Warning', $statuses[0]);
        $this->assertEquals('Suspended', $statuses[1]);
        $this->assertEquals('Banned', $statuses[2]);
        $this->assertEquals('Banned', $statuses[3]);
    }

    public function test_warning_message_is_provided(): void
    {
        $service = new ModerationService;
        $result = $service->escalateViolation($this->reported);

        $this->assertStringContainsString('first warning', $result['message']);
    }

    public function test_suspension_message_is_provided(): void
    {
        $service = new ModerationService;
        $service->escalateViolation($this->reported->fresh());
        $result = $service->escalateViolation($this->reported->fresh());

        $this->assertStringContainsString('suspended', $result['message']);
    }

    public function test_ban_message_is_provided(): void
    {
        $service = new ModerationService;
        $service->escalateViolation($this->reported->fresh());
        $service->escalateViolation($this->reported->fresh());
        $result = $service->escalateViolation($this->reported->fresh());

        $this->assertStringContainsString('banned', strtolower($result['message']));
    }
}
