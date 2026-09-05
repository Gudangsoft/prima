<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Filament\Pages\VerifikasiOtp;
use App\Models\User;
use App\Notifications\SendOtpCodeNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\TestCase;

class OtpGateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRoles();
        Notification::fake();
    }

    private function dosen(array $attributes = []): User
    {
        $user = User::factory()->create($attributes);
        $user->assignRole('dosen');

        return $user;
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
    }

    public function test_user_without_phone_is_redirected_to_complete_profile(): void
    {
        $user = $this->dosen(['phone_number' => null]);

        $this->actingAs($user)
            ->get('/admin')
            ->assertRedirect(route('filament.admin.pages.lengkapi-profil'));
    }

    public function test_user_with_phone_is_redirected_to_verify_otp_and_a_code_is_issued(): void
    {
        $user = $this->dosen(['phone_number' => '081234567890']);

        $this->actingAs($user)
            ->get('/admin')
            ->assertRedirect(route('filament.admin.pages.verifikasi-otp'));

        $user->refresh();
        $this->assertNotNull($user->otp_code);
        $this->assertTrue($user->otp_expires_at->isFuture());

        Notification::assertSentTo($user, SendOtpCodeNotification::class);
    }

    public function test_a_verified_session_reaches_the_dashboard(): void
    {
        $user = $this->dosen(['phone_number' => '081234567890']);

        $this->actingAs($user)
            ->withSession([config('sip2m.otp.session_key') => $user->getKey()])
            ->get('/admin')
            ->assertOk();
    }

    public function test_the_verify_otp_page_itself_is_not_gated(): void
    {
        $user = $this->dosen(['phone_number' => '081234567890']);

        $this->actingAs($user)
            ->get(route('filament.admin.pages.verifikasi-otp'))
            ->assertOk();
    }

    public function test_gate_can_be_disabled_via_config(): void
    {
        config()->set('sip2m.otp.enabled', false);

        $user = $this->dosen(['phone_number' => null]); // tanpa HP & tanpa sesi OTP

        $this->actingAs($user)->get('/admin')->assertOk();
        $this->actingAs($user)->get(route('filament.admin.pages.verifikasi-otp'))
            ->assertRedirect(route('filament.admin.pages.dashboard'));
    }

    public function test_user_without_any_known_role_cannot_access_the_panel(): void
    {
        $user = User::factory()->create(['phone_number' => '081234567890']);

        $this->actingAs($user)
            ->withSession([config('sip2m.otp.session_key') => $user->getKey()])
            ->get('/admin')
            ->assertForbidden();
    }

    public function test_entering_the_correct_code_verifies_and_redirects_to_dashboard(): void
    {
        $user = $this->dosen(['phone_number' => '081234567890']);
        $this->actingAs($user);

        // Boot panel + terbitkan OTP lewat mount().
        $this->get(route('filament.admin.pages.verifikasi-otp'))->assertOk();

        $code = null;
        Notification::assertSentTo($user, SendOtpCodeNotification::class, function ($n) use (&$code) {
            $code = $n->code;

            return true;
        });

        $this->assertMatchesRegularExpression('/^\d{6}$/', (string) $code);

        Livewire::test(VerifikasiOtp::class)
            ->set('data.code', $code)
            ->call('verifikasi')
            ->assertHasNoFormErrors()
            ->assertRedirect(route('filament.admin.pages.dashboard'));

        $this->assertSame($user->getKey(), (int) session(config('sip2m.otp.session_key')));
        $this->assertNull($user->refresh()->otp_code);
    }

    public function test_wrong_length_code_is_rejected_with_a_form_error_not_a_crash(): void
    {
        $user = $this->dosen(['phone_number' => '081234567890']);
        $this->actingAs($user);
        $this->get(route('filament.admin.pages.verifikasi-otp'))->assertOk();

        Livewire::test(VerifikasiOtp::class)
            ->set('data.code', '12')
            ->call('verifikasi')
            ->assertHasFormErrors(['code']);
    }
}
