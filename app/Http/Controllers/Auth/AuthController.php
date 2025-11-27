<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\OtpMail;

class AuthController extends Controller
{
    public function authenticate(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        $email = strtolower($request->email);

        $user = User::where('email', $email)->first();

        if (!$user) {
            $user = User::create([
                'email' => $email,
                'name' => explode('@', $email)[0],
                'password' => bcrypt(str()->random(16)),
            ]);
        }

        if (!$user->active()) return $this->error('Your account has been deactivated.', 403);

        $otp = $user->createOtp();

        Mail::to($user->email)->send(new OtpMail($otp));

        return $this->ok('If an account exists, an email with a code has been sent');
    }

    public function resendOtp(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return $this->ok('If an account exists, an email with a code has been sent');
        }

        $otp = $user->createOtp();

        Mail::to($user->email)->send(new OtpMail($otp));

        return $this->ok('If an account exists, an email with a code has been sent');
    }

    public function verify(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'otp' => ['required', 'string'],
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) return $this->error('Invalid code or email', 401);

        if (!$user->verifyOtp($request->otp)) return $this->error('Invalid code or email', 401);

        Auth::login($user);

        $request->session()->regenerate();

        return $this->success([
            'redirect' => $user->getBaseRedirectUrl(),
        ], 200);
    }

    public function checkOtp(Request $request)
    {
        $request->validate(['email' => ['required', 'email']]);

        return $this->success([
            'available' => User::where('email', $request->email)
                ->whereNotNull('otp_code_hash')
                ->whereNull('otp_used_at')
                ->where('otp_expires_at', '>=', now())
                ->exists()
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return $this->ok('User logged out successfully');
    }
}
