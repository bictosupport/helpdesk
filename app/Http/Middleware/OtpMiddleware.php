<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class OtpMiddleware
{
    public function handle($request, Closure $next)
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        // ✅ Skip OTP if user logged in using Google (based on google_id or provider field)
        if ($user->google_id || $user->provider === 'google') {
            session()->put('otp_verified', true);
        }

        // ✅ If OTP is not verified yet, redirect to OTP page
        if (!session()->get('otp_verified')) {
            return redirect()->route('otp.verify');
        }

        return $next($request);
    }
}
