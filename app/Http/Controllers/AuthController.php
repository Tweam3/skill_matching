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

        $status = strtolower($user->Account_Status ?? 'active');
        if ($status === 'suspended') {
            $suspendedAt = $user->Suspended_At;
            if ($suspendedAt && $suspendedAt->copy()->addDays(3)->isPast()) {
                $user->update([
                    'Account_Status' => 'Active',
                    'Suspended_At' => null,
                ]);
            } else {
                return back()->withErrors(['email' => 'Your account has been suspended. Please contact support.']);
            }
        }
        if ($status === 'banned') {
            return back()->withErrors(['email' => 'Your account has been banned. Please contact support.']);
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
            'student_id' => ['required', 'string', 'max:50', 'unique:users,Student_ID', 'regex:/^\d{4}-\d{4}-[A-Z]$/i'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'role' => ['nullable', 'string', 'in:Student,Faculty,Staff,Admin'],
            'council' => ['nullable', 'string', 'in:HBM,CSC,BIT,EDUC,Unaffiliated', 'required_if:role,Student,Faculty'],
        ]);

        $approvedStudentIds = [
            '2023-7317-M', '2023-6293-M', '2023-8104-M', '2023-1281-M', '2023-1302-M',
            '2023-6360-M', '2023-4875-M', '2023-6126-M', '2023-7485-M', '2023-4111-M',
            '2023-2197-M', '2023-1098-M', '2023-7180-M', '2023-2341-M', '2023-7986-M',
            '2023-0377-M', '2023-1757-M', '2023-7823-M', '2023-2433-M', '2023-6965-M',
            '2023-5966-M', '2009-0136-M', '2023-7177-M', '2023-0038-M', '2023-3806-M',
            '2023-2760-M', '2023-0642-M', '2023-7872-M', '2023-0278-M', '2023-0168-M',
        ];

        $role = 'Student';
        $verified = false;
        $accountStatus = 'Active';

        if (Auth::check() && Auth::user()->Role === 'Admin') {
            $role = $request->input('role', 'Student');
            $verified = $request->has('as_admin');
        } elseif (in_array(strtoupper($validated['student_id']), array_map('strtoupper', $approvedStudentIds))) {
            $verified = true;
            $role = 'Student';
        }

        $council = in_array($role, ['Student', 'Faculty'])
            ? $validated['council']
            : null;

        $user = User::create([
            'Full_Name' => $validated['name'],
            'Email' => $validated['email'],
            'Student_ID' => $validated['student_id'],
            'Password_Hash' => Hash::make($validated['password']),
            'Role' => $role,
            'Is_Verified' => $verified,
            'Account_Status' => $verified ? 'Active' : 'Pending',
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
