<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Http\Controllers\ImpersonationController;
use App\Services\Otp\OtpService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gerbang OTP untuk panel Filament.
 *
 * Alur setelah login berhasil:
 *   1. Belum punya nomor HP  -> arahkan ke halaman "Lengkapi Profil".
 *   2. Sudah punya nomor HP, OTP belum diverifikasi di sesi ini
 *      -> terbitkan kode (jika belum ada yang aktif) & arahkan ke "Verifikasi OTP".
 *   3. OTP sudah diverifikasi -> lanjut ke tujuan.
 *
 * Halaman OTP itu sendiri dan endpoint logout di-whitelist agar tidak
 * terjadi redirect loop.
 */
class EnsureOtpVerified
{
    /** Nama route yang boleh diakses tanpa OTP terverifikasi. */
    private const WHITELIST = [
        'filament.admin.pages.lengkapi-profil',
        'filament.admin.pages.verifikasi-otp',
        'filament.admin.auth.logout',
    ];

    public function __construct(private readonly OtpService $otp) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null) {
            return $next($request);
        }

        if ($this->isWhitelisted($request)) {
            return $next($request);
        }

        // Sedang menyamar (impersonate): akun asli sudah lolos OTP.
        if ($request->session()->has(ImpersonationController::SESSION_KEY)) {
            return $next($request);
        }

        $sessionKey = (string) config('sip2m.otp.session_key', 'otp.verified_for');

        if ((int) $request->session()->get($sessionKey) === $user->getKey()) {
            return $next($request);
        }

        if (blank($user->phone_number)) {
            return redirect()->route('filament.admin.pages.lengkapi-profil');
        }

        $this->otp->ensureIssuedFor($user);

        return redirect()->route('filament.admin.pages.verifikasi-otp');
    }

    private function isWhitelisted(Request $request): bool
    {
        foreach (self::WHITELIST as $name) {
            if ($request->routeIs($name)) {
                return true;
            }
        }

        return false;
    }
}
