<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class VerifyEmailController extends Controller
{
    public function verify(string $token, int $userId, Request $request)
    {
        $user = User::where('User_ID', $userId)->where('Verification_Token', $token)->first();

        if (! $user) {
            return redirect()->route('login')->withErrors(['email' => 'Invalid or expired verification link.']);
        }

        $user->update([
            'Is_Verified' => true,
            'Verification_Token' => null,
            'Account_Status' => 'Active',
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard')->with('success', 'Your email has been verified. Welcome!');
    }

    public function resend(Request $request)
    {
        $user = $request->user();

        if ($user->Is_Verified) {
            return redirect()->route('dashboard');
        }

        $token = Str::random(64);
        $user->update(['Verification_Token' => $token]);

        Mail::to($user->Email)->send(new \App\Mail\VerifyEmail($user, $token));

        return back()->with('success', 'Verification email sent. Check your inbox.');
    }
}
