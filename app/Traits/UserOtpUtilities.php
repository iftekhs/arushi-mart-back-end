<?php

namespace App\Traits;

use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

trait UserOtpUtilities
{
    public function createOtp(): string
    {
        $length = config('otp.length', 6);

        $ttlMinutes = config('otp.ttl_minutes', 10);

        $otp = str_pad((string) random_int(0, (int) pow(10, $length) - 1), $length, '0', STR_PAD_LEFT);

        $this->update([
            'otp_code_hash' => Hash::make($otp),
            'otp_expires_at' => Carbon::now()->addMinutes($ttlMinutes),
            'otp_requested_at' => Carbon::now(),
            'otp_used_at' => null,
            'otp_attempts' => 0,
        ]);

        return $otp;
    }

    public function verifyOtp(string $plainOtp): bool
    {
        if (!$this->otp_code_hash || !$this->otp_expires_at) {
            return false;
        }

        if ($this->otp_used_at) {
            return false;
        }

        if (now()->gt($this->otp_expires_at)) {
            return false;
        }

        $maxAttempts = (int) config('otp.verify_max', 5);

        if ($this->otp_attempts >= $maxAttempts) {
            return false;
        }

        if (Hash::check($plainOtp, $this->otp_code_hash)) {
            $this->update([
                'otp_used_at' => now(),
                'otp_attempts' => 0,
            ]);

            return true;
        }

        $this->increment('otp_attempts');
        $this->refresh();

        if ($this->otp_attempts >= $maxAttempts) {
            $this->update(['otp_used_at' => now()]);
        }

        return false;
    }

    public function hasValidOtp(): bool
    {
        return $this->otp_code_hash && !$this->otp_used_at && now()->lte($this->otp_expires_at);
    }
}
