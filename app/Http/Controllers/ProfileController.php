<?php

namespace App\Http\Controllers;

use App\Models\AdminActionLog;
use App\Models\Skill;
use App\Models\User;
use App\Models\UserSkill;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    protected function logAction(string $action, ?string $details = null): void
    {
        AdminActionLog::create([
            'Admin_ID' => Auth::id(),
            'Action' => $action,
            'Details' => $details,
        ]);
    }

    public function show($id = null)
    {
        $userId = $id ?? Auth::id();
        $user = User::findOrFail($userId);
        $userSkills = $user->skills;
        $allSkills = Skill::orderBy('Category')->orderBy('Subcategory')->orderBy('Skill_Title')->get();
        $groupedSkills = $allSkills->groupBy(function ($s) {
            return $s->Subcategory
                ? ($s->Category.' / '.$s->Subcategory)
                : ($s->Category ?: 'General');
        });

        return view('profile.show', compact('user', 'userSkills', 'allSkills', 'groupedSkills'));
    }

    public function edit($id = null)
    {
        $userId = $id ?? Auth::id();
        $user = User::findOrFail($userId);

        return view('profile.edit', compact('user'));
    }

    public function update(Request $request, $id)
    {
        $userId = $id ?? Auth::id();
        $user = User::findOrFail($userId);
        $isAdmin = Auth::user() && Auth::user()->Role === 'Admin' && Auth::id() !== $userId;
        $section = $request->input('section', 'name');

        if ($section === 'password') {
            $request->validate([
                'current_password' => ['required', 'string'],
                'new_password' => ['required', 'string', 'min:8', 'confirmed'],
            ]);

            if (!Hash::check($request->input('current_password'), $user->Password_Hash)) {
                return back()->with('error', 'Current password is incorrect.');
            }

            $user->Password_Hash = Hash::make($request->input('new_password'));
            $user->save();

            return redirect()->route('profile.show', $user->User_ID)->with('success', 'Password updated.');
        }

        if ($section === 'about') {
            $request->validate([
                'bio' => ['nullable', 'string', 'max:500'],
            ]);

            $user->Bio = $request->input('bio');
            $user->save();

            return redirect()->route('profile.show', $user->User_ID)->with('success', 'Bio updated.');
        }

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,Email,'.$user->User_ID.',User_ID'],
            'role' => [$isAdmin ? 'required' : 'nullable', 'in:Student,Faculty,Staff,Admin'],
            'council' => ['nullable', 'string', 'in:HBM,CSC,BIT,EDUC,Unaffiliated'],
            'profile_picture' => ['nullable', 'image', 'max:2048'],
        ]);

        $user->Full_Name = $request->input('name');
        $user->Email = $request->input('email');
        if ($isAdmin) {
            $user->Role = $request->input('role');
            $council = in_array($request->input('role'), ['Student', 'Faculty'])
                ? $request->input('council')
                : null;
            $user->Council = $council;
            $this->logAction('update_role', "User ID {$user->User_ID}: role and council updated by admin");
        }
        if ($request->has('remove_profile_picture')) {
            $this->deleteProfilePicture($user);
            $user->Profile_Picture = null;
        } elseif ($request->hasFile('profile_picture')) {
            $this->deleteProfilePicture($user);
            $file = $request->file('profile_picture');
            $filename = 'user_'.$user->User_ID.'_'.time().'.'.$file->getClientOriginalExtension();
            $path = $file->storeAs('profile-pictures', $filename, 'public');
            $user->Profile_Picture = $path;
        }
        $user->save();

        return redirect()->route('profile.show', $user->User_ID)->with('success', 'Profile updated.');
    }

    protected function deleteProfilePicture(User $user): void
    {
        if ($user->Profile_Picture) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($user->Profile_Picture);
        }
    }

    public function addSkill(Request $request)
    {
        $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,User_ID'],
            'skill_id' => ['required', 'integer', 'exists:skills,Skill_ID'],
        ]);
        $userId = (int) $request->user_id;
        if (Auth::id() !== $userId && Auth::user()->Role !== 'Admin') {
            abort(403);
        }
        UserSkill::firstOrCreate(['User_ID' => $userId, 'Skill_ID' => $request->skill_id]);

        return back();
    }

    public function removeSkill(Request $request)
    {
        $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,User_ID'],
            'skill_id' => ['required', 'integer', 'exists:skills,Skill_ID'],
        ]);
        $userId = (int) $request->user_id;
        if (Auth::id() !== $userId && Auth::user()->Role !== 'Admin') {
            abort(403);
        }
        UserSkill::where('User_ID', $userId)->where('Skill_ID', $request->skill_id)->delete();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true]);
        }

        return back();
    }
}
