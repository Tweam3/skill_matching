<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\Notification;
use App\Models\Report;
use App\Models\Review;
use App\Models\Skill;
use App\Models\SkillRequest;
use App\Models\UserMatch;
use App\Services\Matching\Recommender;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RequestController extends Controller
{
    public function index()
    {
        $uid = Auth::id();
        $requests = SkillRequest::where('User_ID', $uid)->with('skill')->latest('Request_ID')->get();
        $appliedRequests = SkillRequest::whereHas('assignments', function ($q) use ($uid) {
            $q->where('User_ID', $uid)
                ->where('Status', '!=', 'Rejected');
        })
            ->with(['skill', 'assignments' => function ($q) use ($uid) {
                $q->where('User_ID', $uid)
                    ->where('Status', '!=', 'Rejected');
            }])
            ->get()
            ->sortBy(function ($r) {
                $status = $r->Status;
                $order = ['Pending' => 1, 'Completed' => 2, 'Cancelled' => 3];

                return $order[$status] ?? 4;
            })
            ->sortByDesc(function ($r) {
                return $r->Request_ID;
            })
            ->values();

        return view('requests.index', compact('requests', 'appliedRequests'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'skill_ids' => ['required', 'array', 'min:1'],
            'skill_ids.*' => ['integer', 'exists:skills,Skill_ID'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'service_mode' => ['required', 'string', 'in:Remote,Face-to-Face,Hybrid'],
        ]);
        $uid = Auth::id();
        $skillIds = $validated['skill_ids'];
        $primarySkillId = $skillIds[0];
        $recommendationsCount = DB::transaction(function () use ($uid, $validated, $skillIds, $primarySkillId) {
            $requestModel = SkillRequest::create([
                'User_ID' => $uid,
                'Skill_ID' => $primarySkillId,
                'Title' => $validated['title'],
                'Description' => $validated['description'],
                'Status' => 'Open',
                'Service_Mode' => $validated['service_mode'],
                'Created_At' => now(),
            ]);
            $requestId = $requestModel->Request_ID;
            $requestModel->skills()->sync($skillIds);

            $recommendations = app(Recommender::class)->rankForRequest($requestModel, 20);

            foreach ($recommendations as $rec) {
                $provider = $rec['user'];
                UserMatch::updateOrCreate([
                    'Matched_User_ID' => $provider->User_ID,
                    'Request_ID' => $requestId,
                ], [
                    'Match_Score' => $rec['score'],
                ]);
                Notification::create([
                    'User_ID' => $provider->User_ID,
                    'Notif_Type' => 'Match',
                    'Message' => 'New request: '.$requestModel->Title,
                ]);
            }

            return count($recommendations);
        });

        return redirect()->route('requests.index')->with('success', 'Request created. '.$recommendationsCount.' provider(s) matched.');
    }

    public function show($id)
    {
        $request = SkillRequest::with(['skill', 'skills', 'user', 'assignments'])->findOrFail($id);
        $applicants = [];
        if (Auth::id() == $request->User_ID) {
            $applicants = Assignment::where('Request_ID', $id)
                ->whereIn('Status', ['Pending', 'Accepted'])
                ->with('user')
                ->get();
        }

        return view('requests.show', compact('request', 'applicants'));
    }

    public function edit($id)
    {
        $request = SkillRequest::with('skill')->findOrFail($id);
        $skills = Skill::orderBy('Category')->orderBy('Subcategory')->orderBy('Skill_Title')->get();

        return view('requests.edit', compact('request', 'skills'));
    }

    public function update(Request $request, $id)
    {
        $skillRequest = SkillRequest::findOrFail($id);
        $validated = $request->validate([
            'skill_ids' => ['required', 'array', 'min:1'],
            'skill_ids.*' => ['integer', 'exists:skills,Skill_ID'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'service_mode' => ['required', 'string', 'in:Remote,Face-to-Face,Hybrid'],
        ]);
        $skillIds = $validated['skill_ids'];
        $skillRequest->update([
            'Skill_ID' => $skillIds[0],
            'Title' => $validated['title'],
            'Description' => $validated['description'],
            'Service_Mode' => $validated['service_mode'],
        ]);
        $skillRequest->skills()->sync($skillIds);

        return redirect()->route('requests.index')->with('success', 'Request updated.');
    }

    public function destroy($id)
    {
        $skillRequest = SkillRequest::findOrFail($id);

        DB::transaction(function () use ($id, $skillRequest) {
            UserMatch::where('Request_ID', $id)->delete();
            Assignment::where('Request_ID', $id)->delete();
            Review::where('Request_ID', $id)->delete();
            Report::where('Request_ID', $id)->delete();
            $skillRequest->delete();
        });

        return redirect()->route('requests.index')->with('success', 'Request deleted.');
    }

    public function complete($id)
    {
        $request = SkillRequest::with('assignments')->findOrFail($id);
        $uid = Auth::id();
        if ($uid != $request->User_ID) {
            abort(403);
        }
        $acceptedAssignment = $request->assignments->firstWhere('Status', 'Accepted');
        if (! $acceptedAssignment) {
            return back()->with('error', 'No accepted applicant found for this request.');
        }
        DB::transaction(function () use ($request, $acceptedAssignment) {
            $acceptedAssignment->update(['Status' => 'Completed', 'Completed_At' => now()]);
            $request->update(['Status' => 'Completed']);
        });

        return redirect()->route('requests.show', $id)->with('success', 'Request marked as completed. Please leave a review for the assigned user.');
    }
}
