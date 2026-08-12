<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\Notification;
use App\Models\SkillRequest;
use App\Models\UserMatch;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $uid = Auth::id();
        $openRequests = SkillRequest::where('User_ID', $uid)->where('Status', 'Open')->count();
        $pendingApplications = Assignment::where('User_ID', $uid)->where('Status', 'Pending')->count();
        $matchesCount = UserMatch::where('Matched_User_ID', $uid)->count();
        $unreadCount = Notification::where('User_ID', $uid)->where('Status', 'Unread')->count();
        $user = Auth::user();
        $recent = SkillRequest::where('User_ID', $uid)
            ->with(['skill', 'skills'])
            ->latest('Request_ID')
            ->take(6)
            ->get();

        return view('dashboard', compact('openRequests', 'pendingApplications', 'matchesCount', 'unreadCount', 'user', 'recent'));
    }
}
