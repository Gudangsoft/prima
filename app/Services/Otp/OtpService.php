<?php

declare(strict_types=1);

namespace App\Services\Otp;

use App\Models\User;
use App\Notifications\SendOtpCodeNotification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

/**
 * Menerbitkan, mengirim, dan memverifikasi kode OTP untuk gerbang login.
 *
 * Kode disimpan dalam bentuk hash di kolom `users.otp_code`; plaintext hanya
 * ada sesaat saat dikirim lewat notifikasi. Verifikasi dibatasi jumlah
 * percobaan (`otp_attempts`) dan masa berlaku (`otp_expires_at`).
 */
class OtpService
{
    public function length(): int
    {
        return (int) config('sip2m.otp.length', 6);
    }

    public function ttlMinutes(): int
    {
        return (int) config('sip2m.otp.ttl_minutes', 10);
    }

    public function maxAttempts(): int
    {
        return (int) config('sip2m.otp.max_attempts', 5);
    }

    public function resendCooldownSeconds(): int
    {
        return (int) config('sip2m.otp.resend_cooldown_seconds', 60);
    }

    /**
     * Buat kode baru, simpan hash-nya, lalu kirim via notifikasi (queued).
     */
    public function issueFor(User $user): void
    {
        $plain = $this->generateCode();

        $user->forceFill([
            'otp_code' => Hash::make($plain),
            'otp_expires_at' => now()->addMinutes($this->ttlMinutes()),
            'otp_attempts' => 0,
            'otp_last_sent_at' => now(),
        ])->save();

        $user->notify(new SendOtpCodeNotification($plain, $this->ttlMinutes()));
    }

    /**
     * Terbitkan kode hanya jika belum ada yang aktif; kalau sudah ada dan
     * belum kedaluwarsa, biarkan. Dipakai oleh middleware agar tidak
     * spam-kirim tiap request.
     */
    public function ensureIssuedFor(User $user): void
    {
        if ($this->hasActiveCode($user)) {
            return;
        }

        $this->issueFor($user);
    }

    public function hasActiveCode(User $user): bool
    {
        return filled($user->otp_code)
            && $user->otp_expires_at instanceof Carbon
            && $user->otp_expires_at->isFuture()
            && $user->otp_attempts < $this->maxAttempts();
    }

    public function canResend(User $user): bool
    {
        return $this->secondsUntilResend($user) === 0;
    }

    public function secondsUntilResend(User $user): int
    {
        if (! $user->otp_last_sent_at instanceof Carbon) {
            return 0;
        }

        $elapsed = $user->otp_last_sent_at->diffInSeconds(now());

        return (int) max(0, $this->resendCooldownSeconds() - $elapsed);
    }

    /**
     * @return array{ok: bool, reason: ?string}
     */
    public function verify(User $user, string $code): array
    {
        $code = trim($code);

        if (blank($user->otp_code) || ! $user->otp_expires_at instanceof Carbon) {
            return ['ok' => false, 'reason' => 'Belum ada kode OTP aktif. Silakan minta kode baru.'];
        }

        if ($user->otp_expires_at->isPast()) {
            return ['ok' => false, 'reason' => 'Kode OTP sudah kedaluwarsa. Silakan minta kode baru.'];
        }

        if ($user->otp_attempts >= $this->maxAttempts()) {
            return ['ok' => false, 'reason' => 'Terlalu banyak percobaan. Silakan minta kode baru.'];
        }

        if (! Hash::check($code, $user->otp_code)) {
            $user->increment('otp_attempts');
            $sisa = max(0, $this->maxAttempts() - $user->otp_attempts);

            return ['ok' => false, 'reason' => "Kode OTP salah. Sisa percobaan: {$sisa}."];
        }

        $user->forceFill([
            'otp_code' => null,
            'otp_expires_at' => null,
            'otp_attempts' => 0,
            'otp_verified_at' => now(),
        ])->save();

        return ['ok' => true, 'reason' => null];
    }

    private function generateCode(): string
    {
        $max = (10 ** $this->length()) - 1;

        return str_pad((string) random_int(0, $max), $this->length(), '0', STR_PAD_LEFT);
    }
}
