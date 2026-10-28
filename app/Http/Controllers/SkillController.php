<?php

namespace App\Http\Controllers;

use App\Models\Skill;
use App\Models\SkillEndorsement;
use App\Models\UserSkill;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SkillController extends Controller
{
    public function index()
    {
        $skills = Skill::orderBy('Category')->orderBy('Subcategory')->orderBy('Skill_Title')->get();

        $groupedSkills = $skills->groupBy(function ($s) {
            return $s->Category ?: 'General';
        })->map(function ($categorySkills) {
            return $categorySkills->groupBy(function ($s) {
                return $s->Subcategory ?: null;
            });
        });

$userSkills = Auth::id()
    ? Auth::user()->skills->pluck('Skill_ID')->toArray()
    : [];
$userSkillStatuses = Auth::id()
    ? Auth::user()->userSkills->pluck('Status', 'Skill_ID')->toArray()
    : [];

        return view('skills.index', compact('skills', 'groupedSkills', 'userSkills', 'userSkillStatuses'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'skill_title' => ['required', 'string', 'max:100'],
            'category' => ['nullable', 'string', 'max:100'],
            'subcategory' => ['nullable', 'string', 'max:100'],
        ]);
        $skill = Skill::create($validated);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'skill' => $skill]);
        }

        return back()->with('success', 'Skill added.');
    }

public function addToMe(Request $request)
{
    $request->validate([
        'skill_id' => ['required', 'integer', 'exists:skills,Skill_ID'],
        'proficiency' => ['nullable', 'integer', 'min:1', 'max:5'],
    ]);
    $proficiency = $request->input('proficiency', 3);

    // Auto-verify if no one has this skill verified yet (bootstrap for new skills)
    $verifiedCount = UserSkill::where('Skill_ID', $request->skill_id)
        ->where('Status', 'verified')
        ->count();

    $status = $verifiedCount === 0 ? 'verified' : 'pending';

    \App\Models\UserSkill::firstOrCreate([
        'User_ID' => Auth::id(),
        'Skill_ID' => $request->skill_id,
    ], [
        'Proficiency' => $proficiency,
        'Status' => $status,
    ]);

    if ($request->wantsJson() || $request->ajax()) {
        return response()->json(['success' => true]);
    }

    $message = $status === 'verified'
        ? 'Skill added and auto-verified (first user).'
        : 'Skill added as pending. Get 2 endorsements from verified users to verify.';

    return back()->with('success', $message);
}
{
    $request->validate([
        'user_skill_id' => ['required', 'integer', 'exists:user_skills,User_Skill_ID'],
    ]);

    $userSkill = UserSkill::findOrFail($request->user_skill_id);

    // Can't endorse your own skill
    if ($userSkill->User_ID === Auth::id()) {
        return back()->with('error', 'You cannot endorse your own skill.');
    }

    // Endorser must have the same skill verified
    $endorserHasSkill = Auth::user()->verifiedSkills()
        ->where('skills.Skill_ID', $userSkill->Skill_ID)
        ->exists();

    if (! $endorserHasSkill) {
        return back()->with('error', 'You must have this skill verified to endorse others.');
    }

    // Create endorsement (unique per endorser + user_skill)
    $endorsement = SkillEndorsement::firstOrCreate([
        'Endorser_ID' => Auth::id(),
        'User_Skill_ID' => $request->user_skill_id,
    ]);

    // Auto-verify after 2 endorsements
    if ($endorsement->wasRecentlyCreated) {
        $count = $userSkill->endorsements()->count();
        if ($count >= 2) {
            $userSkill->update(['Status' => 'verified']);
        }
    }

    if ($request->wantsJson() || $request->ajax()) {
        return response()->json(['success' => true, 'endorsed' => $endorsement->wasRecentlyCreated]);
    }

    return back()->with('success', 'Skill endorsed successfully.');
}
}
