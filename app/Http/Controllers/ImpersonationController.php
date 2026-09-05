<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

/**
 * Impersonation ("Login sebagai").
 *
 * `start()` dipanggil dari table action di UserResource; `stop()` dari banner.
 *
 * Kunci sesi:
 *   - impersonator_id : id user asli (penanda sedang menyamar)
 *
 * Selain menukar user yang login, kita juga:
 *   - menandai OTP target sebagai lolos (agar tidak kena EnsureOtpVerified);
 *   - menyelaraskan `password_hash_<guard>` supaya middleware AuthenticateSession
 *     tidak langsung me-logout sesi hasil penyamaran.
 */
class ImpersonationController extends Controller
{
    public const SESSION_KEY = 'impersonator_id';

    public static function start(User $impersonator, User $target): void
    {
        if (! $impersonator->canImpersonate()
            || ! $target->canBeImpersonated()
            || $impersonator->is($target)) {
            abort(403, 'Tidak diizinkan.');
        }

        // Simpan id akun asli SEBELUM Auth::login (data sesi bertahan saat migrate).
        session()->put(self::SESSION_KEY, $impersonator->getKey());

        Auth::login($target); // memicu Login event -> ResetOtpVerificationOnLogin

        self::syncSessionMarkers($target);
    }

    public function stop(): RedirectResponse
    {
        $originalId = session()->pull(self::SESSION_KEY);

        if ($originalId !== null && ($original = User::find($originalId)) !== null) {
            Auth::login($original);
            self::syncSessionMarkers($original);
        }

        return redirect(Filament::getUrl());
    }

    private static function syncSessionMarkers(User $user): void
    {
        // Akun ini dianggap sudah lolos OTP selama sesi penyamaran / setelah kembali.
        session()->put((string) config('sip2m.otp.session_key'), $user->getKey());

        // Cegah AuthenticateSession me-logout karena hash sandi berbeda.
        session()->put(
            'password_hash_'.Auth::getDefaultDriver(),
            $user->getAuthPassword(),
        );
    }
}
