<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    public function index()
    {
        $uid = Auth::id();
        $completed = \App\Models\Assignment::where('User_ID', $uid)
            ->where('Status', 'Completed')
            ->with(['request.skill', 'request.user'])
            ->get()
            ->filter(function ($a) {
                return ! Review::where('Request_ID', $a->Request_ID)->where('Reviewer_ID', Auth::id())->exists();
            })
            ->values();

        return view('reviews.index', compact('completed'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'request_id' => ['required', 'integer', 'exists:skill_requests,Request_ID'],
            'rated_user_id' => ['required', 'integer', 'exists:users,User_ID'],
            'rating' => ['required', 'integer', 'between:1,5'],
            'comment' => ['required', 'string'],
        ]);
        Review::create([
            'Reviewed_User_ID' => $validated['rated_user_id'],
            'Reviewer_ID' => Auth::id(),
            'Request_ID' => $validated['request_id'],
            'Rating' => $validated['rating'],
            'Comment' => $validated['comment'],
        ]);
        $avg = Review::where('Reviewed_User_ID', $validated['rated_user_id'])->avg('Rating');
        $user = User::find($validated['rated_user_id']);
        $user->Avg_Rating = round($avg, 2);
        $user->Total_Completed = ($user->Total_Completed ?? 0) + 1;
        $user->save();

        return redirect()->route('reviews.index')->with('success', 'Review submitted.');
    }
}
