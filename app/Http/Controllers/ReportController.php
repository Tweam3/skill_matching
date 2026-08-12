<?php

namespace App\Http\Controllers;

use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'reported_user_id' => ['required', 'integer', 'exists:users,User_ID'],
            'reason' => ['required', 'string'],
            'proof' => ['nullable', 'string'],
            'request_id' => ['nullable', 'integer', 'exists:skill_requests,Request_ID'],
        ]);
        Report::create([
            'Reporter_ID' => Auth::id(),
            'Reported_User_ID' => $validated['reported_user_id'],
            'Request_ID' => $validated['request_id'] ?? null,
            'Reason' => $validated['reason'],
            'Proof' => $validated['proof'] ?? null,
            'Status' => 'Pending',
        ]);

        return back()->with('success', 'Report submitted.');
    }
}
