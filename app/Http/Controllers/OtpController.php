<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\OtpMail;
use Inertia\Inertia;

class OtpController extends Controller
{
    public function show()
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        return Inertia::render('Auth/Otp', [
           'otpExpiresAt' => $user->otp_timer->toIso8601String(), // or ->toISOString()
        ]);
    }

    public function verify(Request $request)
    {
        $request->validate([
            'otp' => 'required|digits:6'
        ]);

        $user = Auth::user();

        if (!$user || now()->gt($user->otp_timer)) {
            Auth::logout();
            return redirect()->route('login')->withErrors(['otp' => 'OTP expired. Please login again.']);
        }

        if ($request->otp !== $user->otp) {
            return back()->withErrors(['otp' => 'Invalid OTP']);
        }

        session()->put('otp_verified', true);

        // Optionally clear OTP
        $user->otp = null;
        $user->otp_timer = null;
        $user->save();

        return redirect()->route('dashboard');
    }

    public function resend()
    {
        $user = Auth::user();

        // Generate new OTP + Timer
        $otpCode = rand(100000, 999999);
        $otpTimer = now()->addMinutes(2);

        $user->otp = $otpCode;
        $user->otp_timer = $otpTimer;
        $user->save();

        Mail::to($user->email)->send(new OtpMail($otpCode));

        // Return ONLY the new otpExpiresAt back to the page
        return response()->json([
            'otpExpiresAt' => $otpTimer->toIso8601String(), // send new expiry time
            'message' => 'OTP sent successfully',
        ]);
    }


}
