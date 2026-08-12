<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);
        $user = User::where('Email', $credentials['email'])->first();
        if (! $user || ! Hash::check($credentials['password'], $user->Password_Hash)) {
            return back()->withErrors(['email' => 'Invalid credentials.']);
        }
        Auth::login($user);
        $request->session()->regenerate();
        if ($user->Is_Verified) {
            return $user->Role === 'Admin'
                ? redirect()->route('admin.index')
                : redirect()->route('dashboard');
        }

        return redirect()->route('pending.verification');
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,Email'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'role' => ['nullable', 'string', 'in:Student,Faculty,Staff,Admin'],
            'council' => ['nullable', 'string', 'in:HBM,CSC,BIT,EDUC,Unaffiliated', 'required_if:role,Student,Faculty'],
        ]);
        $role = 'Student';
        $verified = false;
        if (Auth::check() && Auth::user()->Role === 'Admin') {
            $role = $request->input('role', 'Student');
            $verified = $request->has('as_admin');
        }
        $council = in_array($role, ['Student', 'Faculty'])
            ? $validated['council']
            : null;
        $user = User::create([
            'Full_Name' => $validated['name'],
            'Email' => $validated['email'],
            'Password_Hash' => Hash::make($validated['password']),
            'Role' => $role,
            'Is_Verified' => $verified,
            'Council' => $council,
        ]);
        Auth::login($user);
        if ($verified) {
            return redirect()->route('dashboard');
        }

        return redirect()->route('pending.verification');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
