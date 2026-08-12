<?php

namespace App\Http\Controllers;

use App\Models\Skill;
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

        return view('skills.index', compact('skills', 'groupedSkills', 'userSkills'));
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
        \App\Models\UserSkill::firstOrCreate([
            'User_ID' => Auth::id(),
            'Skill_ID' => $request->skill_id,
        ], [
            'Proficiency' => $proficiency,
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true]);
        }

        return back();
    }
}
