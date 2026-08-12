<?php

namespace App\Http\Controllers;

use App\Models\SkillRequest;
use App\Models\User;
use App\Models\UserMatch;
use App\Services\Matching\Recommender;
use Illuminate\Support\Facades\Auth;

class MatchController extends Controller
{
    public function index()
    {
        $uid = Auth::id();
        $matched = UserMatch::where('Matched_User_ID', $uid)
            ->whereHas('request', function ($q) {
                $q->where('Status', 'Open');
            })
            ->with(['request.skill', 'request.skills', 'request.user'])
            ->orderByDesc('Match_Score')
            ->get();

        $available = SkillRequest::where('Status', 'Open')
            ->where('User_ID', '!=', $uid)
            ->whereDoesntHave('assignments', function ($q) use ($uid) {
                $q->where('User_ID', $uid);
            })
            ->with(['skill', 'skills', 'user'])
            ->take(20)
            ->get();

        return view('matches.index', compact('matched', 'available'));
    }

    /**
     * Show the match-score breakdown for a specific request as seen by
     * the authenticated user (a provider).
     */
    public function show($id)
    {
        $uid = Auth::id();
        $request = SkillRequest::with(['skill', 'skills', 'user'])
            ->findOrFail($id);

        $recommender = app(Recommender::class);

        $me = User::with('skills')->findOrFail($uid);
        $ranking = $request->Status === 'Open'
            ? $recommender->rankForRequest($request)
            : [];

        $myScore = null;
        foreach ($ranking as $entry) {
            if ($entry['user']->User_ID === $uid) {
                $myScore = $entry;
                break;
            }
        }

        $isMatched = UserMatch::where('Matched_User_ID', $uid)
            ->where('Request_ID', $id)
            ->exists();

        return view('matches.show', compact('request', 'ranking', 'myScore', 'isMatched'));
    }
}
