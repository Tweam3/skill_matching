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

    public function show($slug = null)
    {
        $user = null;

        if ($slug) {
            $user = User::where('profile_slug', $slug)->first();

            if (!$ $user) {
                abort(404);
            }
        } else {
            $user = Auth::user();
        }

        if (! $user->profile_slug) {
            $user->profile_slug = User::generateUniqueProfileSlug();
            $user->save();
        }

        $viewer = Auth::user();
        $isOwner = $viewer && $viewer->User_ID === $user->User_ID;
        $isAdmin = $viewer && $viewer->Role === 'Admin';
        $profileVisibility = $user->settings['profile_visibility'] ?? 'public';
        $canView = $isOwner || $isAdmin || $profileVisibility === 'public';

        if (!$canView) {
            return view('profile.show', compact('user', 'userSkills', 'allSkills', 'groupedSkills', 'canView'));
        }

        $userSkills = $user->skills;
        $allSkills = Skill::orderBy('Category')->orderBy('Subcategory')->orderBy('Skill_Title')->get();
        $groupedSkills = $allSkills->groupBy(function ($s) {
            return $s->Subcategory
                ? ($s->Category.' / '.$s->Subcategory)
                : ($s->Category ?: 'General');
        });

        return view('profile.show', compact('user', 'userSkills', 'allSkills', 'groupedSkills', 'canView'));
    }

    public function edit($slug = null)
    {
        $user = $this->resolveUser($slug);

        return view('profile.edit', compact('user'));
    }

    public function adjustPicture(Request $request, $slug)
    {
        $request->validate([
            'profile_picture' => ['required', 'image', 'max:2048'],
        ]);

        $user = $this->resolveUser($slug);

        if ($request->hasFile('profile_picture')) {
            $this->deleteProfilePicture($user);
            $file = $request->file('profile_picture');
            $filename = 'user_'.$user->User_ID.'_'.time().'.'.$file->getClientOriginalExtension();
            $path = $file->storeAs('profile-pictures', $filename, 'public');
            $user->Profile_Picture = $path;
            $user->save();
        }

        return redirect()->route('profile.show', $user->profile_slug)->with('success', 'Profile picture updated.');
    }

    public function update(Request $request, $slug)
    {
        $user = $this->resolveUser($slug);
        $isAdmin = Auth::user() && Auth::user()->Role === 'Admin' && Auth::id() !== $user->User_ID;
        $section = $request->input('section', 'name');

        if ($section === 'password') {
            $request->validate([
                'new_password' => ['required', 'string', 'min:8', 'confirmed'],
            ]);

            if (!$isAdmin) {
                $request->validate([
                    'current_password' => ['required', 'string'],
                ]);

                if (!Hash::check($request->input('current_password'), $user->Password_Hash)) {
                    return back()->with('error', 'Current password is incorrect.');
                }
            }

            $user->Password_Hash = Hash::make($request->input('new_password'));
            $user->save();

            return redirect()->route('profile.show', $user->profile_slug)->with('success', 'Password updated.');
        }

        if ($section === 'about') {
            $request->validate([
                'bio' => ['nullable', 'string', 'max:500'],
            ]);

            $user->Bio = $request->input('bio');
            $user->save();

            return redirect()->route('profile.show', $user->profile_slug)->with('success', 'Bio updated.');
        }

        if ($section === 'picture') {
            $request->validate([
                'profile_picture' => ['nullable', 'image', 'max:2048'],
            ]);

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

            return redirect()->route('profile.show', $user->profile_slug)->with('success', 'Profile picture updated.');
        }

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,Email,'.$user->User_ID.',User_ID'],
            'role' => [$isAdmin ? 'required' : 'nullable', 'in:Student,Faculty,Staff,Admin'],
            'council' => ['nullable', 'string', 'in:HBM,CSC,BIT,EDUC,Unaffiliated'],
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
        $user->save();

        return redirect()->route('profile.show', $user->profile_slug)->with('success', 'Profile updated.');
    }

    protected function resolveUser($slug)
    {
        $user = null;

        if ($slug) {
            $user = User::where('profile_slug', $slug)->first();

            if (! $user) {
                abort(404);
            }
        } else {
            $user = Auth::user();
        }

        return $user;
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
