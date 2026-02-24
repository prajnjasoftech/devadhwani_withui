<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\OtpLog;
use App\Models\Temple;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class AuthController extends Controller
{
    public function showLogin(): Response
    {
        return Inertia::render('Auth/Login');
    }

    public function login(Request $request): RedirectResponse
    {
        $request->validate([
            'phone' => 'required|string',
            'otp' => 'required|string|size:6',
        ]);

        // Verify OTP
        $otpLog = OtpLog::where('phone', $request->phone)
            ->where('otp', $request->otp)
            ->where('is_verified', false)
            ->where('expires_at', '>', now())
            ->latest()
            ->first();

        if (! $otpLog) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'otp' => 'Invalid or expired OTP',
            ]);
        }

        // Mark OTP as verified
        $otpLog->update(['is_verified' => true]);

        // Find temple or member
        $temple = Temple::where('phone', $request->phone)->first();
        $member = Member::where('phone', $request->phone)->first();

        if ($temple) {
            Auth::guard('web')->login($temple, true);
            $request->session()->regenerate();

            return redirect()->intended('/dashboard');
        }

        if ($member) {
            // For members, we still need to log in as temple
            $temple = Temple::find($member->temple_id);
            if ($temple) {
                Auth::guard('web')->login($temple, true);
                $request->session()->put('member_id', $member->id);
                $request->session()->regenerate();

                return redirect()->intended('/dashboard');
            }
        }

        throw \Illuminate\Validation\ValidationException::withMessages([
            'phone' => 'No account found for this number',
        ]);
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
