<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureUserIsActive
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $user = Auth::user();
            $status = strtolower($user->Account_Status ?? 'active');

            if ($status === 'suspended') {
                return redirect()->route('account.suspended');
            }

            if ($status === 'banned') {
                return redirect()->route('account.banned');
            }
        }

        return $next($request);
    }
}
