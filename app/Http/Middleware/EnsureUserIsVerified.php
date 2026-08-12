<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureUserIsVerified
{
    public function handle(Request $request, Closure $next)
    {
        if (! Auth::check() || ! Auth::user()->Is_Verified) {
            return redirect()->route('pending.verification');
        }

        return $next($request);
    }
}
