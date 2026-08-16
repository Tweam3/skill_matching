<?php

namespace App\Http\Controllers;

use App\Models\AdminActionLog;
use App\Models\Assignment;
use App\Models\Message;
use App\Models\Notification;
use App\Models\Report;
use App\Models\Review;
use App\Models\Skill;
use App\Models\SkillRequest;
use App\Models\User;
use App\Models\UserMatch;
use App\Models\UserSkill;
use App\Services\Moderation\ModerationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    private function logAction(string $action, ?string $details = null): void
    {
        AdminActionLog::create([
            'Admin_ID' => Auth::id(),
            'Action' => $action,
            'Details' => $details,
        ]);
    }

    public function index()
    {
        $users = User::all();
        $pendingUsers = User::where('Is_Verified', false)->get();
        $skills = Skill::orderBy('Category')->orderBy('Skill_Title')->get();
        $logs = AdminActionLog::latest('Created_At')->take(50)->get();
        $reports = Report::select('reports.*', 'u1.Full_Name as Reporter', 'u2.Full_Name as Reported', 'req.Title as Request_Title', 'u2.Warning_Count', 'u2.Account_Status')
            ->join('users as u1', 'u1.User_ID', '=', 'reports.Reporter_ID')
            ->join('users as u2', 'u2.User_ID', '=', 'reports.Reported_User_ID')
            ->leftJoin('skill_requests as req', 'req.Request_ID', '=', 'reports.Request_ID')
            ->latest('reports.Created_At')
            ->get();

        return view('admin.index', compact('users', 'pendingUsers', 'skills', 'logs', 'reports'));
    }

    public function addSkill(Request $request)
    {
        $validated = $request->validate([
            'skill_title' => ['required', 'string', 'max:100'],
            'category' => ['nullable', 'string', 'max:100'],
            'subcategory' => ['nullable', 'string', 'max:100'],
        ]);
        Skill::create([
            'Skill_Title' => $validated['skill_title'],
            'Category' => $validated['category'] ?? 'General',
            'Subcategory' => $validated['subcategory'],
        ]);
        $this->logAction('add_skill', 'Added skill: '.$validated['skill_title'].' ('.($validated['category'] ?? 'General').'/'.($validated['subcategory'] ?? 'General').')');

        return back();
    }

    public function registerUser(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,Email'],
            'password' => ['required', 'string', 'min:6'],
            'role' => ['required', 'in:Student,Faculty,Staff,Admin'],
            'council' => ['nullable', 'string', 'in:HBM,CSC,BIT,EDUC,Unaffiliated', 'required_if:role,Student,Faculty'],
        ]);
        $council = in_array($validated['role'], ['Student', 'Faculty'])
            ? $validated['council']
            : null;
        User::create([
            'Full_Name' => $validated['name'],
            'Email' => $validated['email'],
            'Password_Hash' => bcrypt($validated['password']),
            'Role' => $validated['role'],
            'Is_Verified' => true,
            'Council' => $council,
            'profile_slug' => User::generateUniqueProfileSlug(),
        ]);
        $this->logAction('register_user', 'Registered user: '.$validated['email'].' as '.$validated['role']);

        return back();
    }

    public function verifyUser(Request $request)
    {
        $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,User_ID'],
            'decision' => ['required', 'in:approve,reject'],
        ]);
        $uid = $request->user_id;
        $user = User::findOrFail($uid);
        if ($request->decision === 'approve') {
            $user->update(['Is_Verified' => true, 'Rejection_Reason' => null]);
            Notification::create([
                'User_ID' => $uid,
                'Notif_Type' => 'Verification',
                'Message' => 'Your account has been approved.',
                'url' => route('dashboard'),
            ]);
            $this->logAction('verify_user', 'Approved user ID: '.$uid);
        } else {
            $reason = $request->input('rejection_reason', '');
            $user->update(['Is_Verified' => false, 'Rejection_Reason' => $reason]);
            Notification::create([
                'User_ID' => $uid,
                'Notif_Type' => 'Verification',
                'Message' => 'Your account was not approved.',
                'url' => route('dashboard'),
            ]);
            $this->logAction('verify_user', 'Rejected user ID: '.$uid.' - Reason: '.$reason);
        }

        return back();
    }

    public function resolveReport(Request $request)
    {
        $request->validate([
            'report_id' => ['required', 'integer', 'exists:reports,Report_ID'],
            'approve' => ['required', 'in:Dismiss,Action_Taken'],
        ]);
        $report = Report::findOrFail($request->report_id);
        $reportedId = $report->Reported_User_ID;
        $reporterId = $report->Reporter_ID;
        $adminId = Auth::id();
        if ($request->approve === 'Dismiss') {
            $report->update(['Status' => 'Dismissed', 'Admin_ID' => $adminId]);
            Notification::create([
                'User_ID' => $reporterId,
                'Notif_Type' => 'Report',
                'Message' => 'Your report has been reviewed and dismissed.',
                'url' => route('dashboard'),
            ]);
            $this->logAction('resolve_report', 'Dismissed report ID: '.$request->report_id);
        } else {
            DB::transaction(function () use ($report, $reportedId, $reporterId, $adminId, $request) {
                $user = User::findOrFail($reportedId);
                $result = app(ModerationService::class)->escalateViolation($user);

                $report->update(['Status' => 'Action_Taken', 'Admin_ID' => $adminId]);
                    Notification::create([
                        'User_ID' => $reporterId,
                        'Notif_Type' => 'Report',
                        'Message' => 'Action taken on your report. Reported user has been '.$result['status'].'.',
                        'url' => route('dashboard'),
                    ]);
                Notification::create([
                    'User_ID' => $reportedId,
                    'Notif_Type' => 'Penalty',
                    'Message' => $result['message'],
                    'url' => route('dashboard'),
                ]);
                $this->logAction('resolve_report', 'Escalated report ID: '.$request->report_id.' — Penalty level '.$result['level'].' ('.$result['status'].'), Warning_Count now: '.$user->Warning_Count);
            });
        }

        return back();
    }

    public function deleteUser(Request $request)
    {
        $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,User_ID'],
        ]);
        $uid = $request->user_id;

        DB::transaction(function () use ($uid) {
            UserSkill::where('User_ID', $uid)->delete();
            Assignment::where('User_ID', $uid)->delete();
            Message::where('Sender_ID', $uid)->orWhere('Receiver_ID', $uid)->delete();
            Notification::where('User_ID', $uid)->delete();
            Review::where('Reviewer_ID', $uid)->orWhere('Reviewed_User_ID', $uid)->delete();
            UserMatch::where('Matched_User_ID', $uid)->delete();
            Report::where('Reporter_ID', $uid)->orWhere('Reported_User_ID', $uid)->delete();
            SkillRequest::where('User_ID', $uid)->delete();
            User::where('User_ID', $uid)->delete();
        });

        $this->logAction('delete_user', 'Deleted user ID: '.$uid);

        return back();
    }
}
