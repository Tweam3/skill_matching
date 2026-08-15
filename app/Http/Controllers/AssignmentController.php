<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\Notification;
use App\Models\Review;
use App\Models\SkillRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AssignmentController extends Controller
{
    public function apply(Request $request)
    {
        $request->validate([
            'request_id' => ['required', 'integer', 'exists:skill_requests,Request_ID'],
        ]);
        $uid = Auth::id();
        $reqId = $request->request_id;
        $req = SkillRequest::findOrFail($reqId);
        if ($req->Status !== 'Open') {
            return back()->with('error', 'This request is no longer accepting applications.');
        }
        $existing = Assignment::where('Request_ID', $reqId)->where('User_ID', $uid)->first();
        if ($existing) {
            return back()->with('info', 'You have already applied for this request.');
        }
        Assignment::create([
            'Request_ID' => $reqId,
            'User_ID' => $uid,
            'Status' => 'Pending',
        ]);
        $owner = $req->User_ID;
        if ($owner) {
            Notification::create([
                'User_ID' => $owner,
                'Notif_Type' => 'Assignment',
                'Message' => Auth::user()->Full_Name.' applied for your request: '.$req->Title,
                'url' => route('requests.show', $req->Request_ID),
            ]);
        }

        return redirect()->route('matches.index')->with('success', 'Application sent. The owner will review it.');
    }

    public function index()
    {
        $uid = Auth::id();
        $assignments = Assignment::where('User_ID', $uid)
            ->with(['request.skill', 'request.user'])
            ->orderBy('Status')
            ->orderByDesc('Responded_At')
            ->get();

        return view('assignments.index', compact('assignments'));
    }

    public function accept(Request $request, $id)
    {
        $assignment = Assignment::with('request')->findOrFail($id);
        $req = $assignment->request;
        $uid = Auth::id();
        if ($uid != $req->User_ID) {
            abort(403);
        }
        if ($req->Status !== 'Open') {
            return back()->with('error', 'This request is no longer open for assignment.');
        }
        $assignment->update(['Status' => 'Accepted', 'Responded_At' => now()]);
        $req->update(['Status' => 'Pending']);
        Assignment::where('Request_ID', $req->Request_ID)
            ->where('Assignment_ID', '!=', $assignment->Assignment_ID)
            ->where('Status', 'Pending')
            ->each(function ($other) use ($req) {
                $other->update(['Status' => 'Rejected', 'Responded_At' => now()]);
                Notification::create([
                    'User_ID' => $other->User_ID,
                    'Notif_Type' => 'Assignment',
                    'Message' => 'The request "'.$req->Title.'" was assigned to another applicant.',
                    'url' => route('matches.index'),
                ]);
            });
        Notification::create([
            'User_ID' => $assignment->User_ID,
            'Notif_Type' => 'Assignment',
            'Message' => 'Your application for "'.$req->Title.'" has been accepted!',
            'url' => route('requests.show', $req->Request_ID),
        ]);

        return back()->with('success', 'Applicant accepted. Other applicants have been notified.');
    }

    public function reject(Request $request, $id)
    {
        $assignment = Assignment::with('request')->findOrFail($id);
        $req = $assignment->request;
        $uid = Auth::id();
        if ($uid != $req->User_ID) {
            abort(403);
        }
        $assignment->update(['Status' => 'Rejected', 'Responded_At' => now()]);
        $hasPending = Assignment::where('Request_ID', $req->Request_ID)->where('Status', 'Pending')->exists();
        if (! $hasPending) {
            $req->update(['Status' => 'Open']);
        }
        Notification::create([
            'User_ID' => $assignment->User_ID,
            'Notif_Type' => 'Assignment',
            'Message' => 'Your application for "'.$req->Title.'" was not accepted.',
            'url' => route('matches.index'),
        ]);

        return back()->with('success', 'Applicant rejected.');
    }

    public function complete(Request $request, $id)
    {
        $assignment = Assignment::findOrFail($id);
        $uid = Auth::id();
        if ($uid != $assignment->User_ID && $uid != $assignment->request->User_ID) {
            abort(403);
        }
        $ok = $request->input('completed') == 1;
        $newStatus = $ok ? 'Completed' : 'Failed';
        $assignment->update([
            'Status' => $newStatus,
            'Completed_At' => now(),
        ]);
        $assignment->request->update(['Status' => $newStatus]);

        return redirect()->route('assignments.index')->with($ok ? 'success' : 'error', $ok ? 'Marked as completed.' : 'Transaction marked as failed.');
    }

    public function review(Request $request, $id)
    {
        $assignment = Assignment::with('request')->findOrFail($id);
        $uid = Auth::id();
        if ($uid != $assignment->User_ID && $uid != $assignment->request->User_ID) {
            abort(403);
        }
        $ratedId = $uid == $assignment->User_ID ? $assignment->request->User_ID : $assignment->User_ID;
        $validated = $request->validate([
            'rating' => ['required', 'integer', 'between:1,5'],
            'comment' => ['required', 'string'],
        ]);
        Review::create([
            'Reviewed_User_ID' => $ratedId,
            'Reviewer_ID' => $uid,
            'Request_ID' => $assignment->Request_ID,
            'Rating' => $validated['rating'],
            'Comment' => $validated['comment'],
        ]);
        $avg = Review::where('Reviewed_User_ID', $ratedId)->avg('Rating');
        $user = User::find($ratedId);
        $user->Avg_Rating = round($avg, 2);
        $user->Total_Completed = ($user->Total_Completed ?? 0) + 1;
        $user->save();

        return back()->with('success', 'Review submitted.');
    }
}
