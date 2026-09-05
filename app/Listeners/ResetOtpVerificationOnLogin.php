<?php

declare(strict_types=1);

namespace App\Listeners;

use Illuminate\Auth\Events\Login;

/**
 * Setiap kali user login, hapus penanda "OTP sudah lolos" dari sesi supaya
 * gerbang OTP wajib dilewati lagi pada sesi baru tersebut.
 */
class ResetOtpVerificationOnLogin
{
    public function handle(Login $event): void
    {
        session()->forget((string) config('sip2m.otp.session_key', 'otp.verified_for'));
    }
}
