<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SettingsController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $settings = $user->settings ?? [];

        return view('settings.index', compact('user', 'settings'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'dark_mode' => ['nullable', 'boolean'],
            'email_notifications' => ['nullable', 'boolean'],
            'profile_visibility' => ['nullable', 'in:public,private'],
            'language' => ['nullable', 'string', 'max:10'],
        ]);

        $current = $user->settings ?? [];
        $validated['dark_mode'] = $request->has('dark_mode');
        $current = array_merge($current, $validated);
        $user->settings = $current;
        $user->save();

        if ($current['dark_mode']) {
            session()->put('dark_mode', true);
        } else {
            session()->forget('dark_mode');
        }

        return back()->with('success', 'Settings saved successfully.');
    }
}
