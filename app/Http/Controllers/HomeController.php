<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\SkillRequest;
use App\Models\UserMatch;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function index()
    {
        $stats = [
            'requests' => 0,
            'matches' => 0,
            'unread' => 0,
            'latest' => [],
        ];
        if (Auth::check()) {
            $uid = Auth::id();
            $stats['requests'] = SkillRequest::where('User_ID', $uid)->count();
            $stats['matches'] = UserMatch::where('Matched_User_ID', $uid)->count();
            $stats['unread'] = Notification::where('User_ID', $uid)->where('Status', 'Unread')->count();
            $stats['latest'] = SkillRequest::where('User_ID', $uid)
                ->with(['skill', 'skills'])
                ->latest('Request_ID')
                ->take(10)
                ->get();
        }

        return view('home', compact('stats'));
    }
}
