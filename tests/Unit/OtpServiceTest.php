<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\User;
use App\Notifications\SendOtpCodeNotification;
use App\Services\Otp\OtpService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class OtpServiceTest extends TestCase
{
    use RefreshDatabase;

    private OtpService $otp;

    protected function setUp(): void
    {
        parent::setUp();
        $this->otp = app(OtpService::class);
        Notification::fake();
    }

    private function capturedCodeFor(User $user): string
    {
        $captured = null;

        Notification::assertSentTo($user, SendOtpCodeNotification::class, function ($n) use (&$captured) {
            $captured = $n->code;

            return true;
        });

        return (string) $captured;
    }

    public function test_issue_stores_a_hashed_code_with_expiry_and_notifies(): void
    {
        $user = User::factory()->create();

        $this->otp->issueFor($user);
        $user->refresh();

        $this->assertNotNull($user->otp_code);
        $this->assertNotSame($this->capturedCodeFor($user), $user->otp_code, 'kode harus disimpan sebagai hash');
        $this->assertTrue(Hash::check($this->capturedCodeFor($user), $user->otp_code));
        $this->assertTrue($user->otp_expires_at->isFuture());
        $this->assertSame(0, $user->otp_attempts);
    }

    public function test_verify_succeeds_with_correct_code_and_clears_state(): void
    {
        $user = User::factory()->create();
        $this->otp->issueFor($user);
        $code = $this->capturedCodeFor($user->refresh());

        $result = $this->otp->verify($user, $code);

        $this->assertTrue($result['ok']);
        $user->refresh();
        $this->assertNull($user->otp_code);
        $this->assertNull($user->otp_expires_at);
        $this->assertNotNull($user->otp_verified_at);
    }

    public function test_verify_fails_and_counts_attempts_on_wrong_code(): void
    {
        $user = User::factory()->create();
        $this->otp->issueFor($user);
        $user->refresh();

        $result = $this->otp->verify($user, '000000-wrong');

        $this->assertFalse($result['ok']);
        $this->assertSame(1, $user->refresh()->otp_attempts);
    }

    public function test_verify_is_locked_after_max_attempts(): void
    {
        $user = User::factory()->create();
        $this->otp->issueFor($user);
        $code = $this->capturedCodeFor($user->refresh());

        for ($i = 0; $i < $this->otp->maxAttempts(); $i++) {
            $this->otp->verify($user, 'salah');
        }

        // Bahkan kode yang benar pun ditolak setelah percobaan habis.
        $result = $this->otp->verify($user->refresh(), $code);

        $this->assertFalse($result['ok']);
        $this->assertStringContainsString('percobaan', strtolower($result['reason']));
    }

    public function test_verify_fails_after_expiry(): void
    {
        $user = User::factory()->create();
        $this->otp->issueFor($user);
        $code = $this->capturedCodeFor($user->refresh());

        $user->forceFill(['otp_expires_at' => now()->subMinute()])->save();

        $result = $this->otp->verify($user, $code);

        $this->assertFalse($result['ok']);
        $this->assertStringContainsString('kedaluwarsa', strtolower($result['reason']));
    }

    public function test_resend_cooldown_is_enforced(): void
    {
        $user = User::factory()->create();
        $this->otp->issueFor($user);
        $user->refresh();

        $this->assertFalse($this->otp->canResend($user));
        $this->assertGreaterThan(0, $this->otp->secondsUntilResend($user));

        $user->forceFill(['otp_last_sent_at' => now()->subSeconds($this->otp->resendCooldownSeconds() + 1)])->save();

        $this->assertTrue($this->otp->canResend($user->refresh()));
    }
}
