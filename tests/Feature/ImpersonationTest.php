<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Resources\UserResource\Pages\ListUsers;
use App\Http\Controllers\ImpersonationController;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Livewire\Livewire;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class ImpersonationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRoles();
    }

    private function user(string $role, array $attrs = []): User
    {
        $u = User::factory()->create($attrs + ['phone_number' => '081234567890']);
        $u->assignRole($role);

        return $u;
    }

    private function otpVerified(User $user): self
    {
        $this->actingAs($user)->withSession([config('sip2m.otp.session_key') => $user->getKey()]);

        return $this;
    }

    public function test_super_admin_can_start_and_stop_impersonation(): void
    {
        $super = $this->user('super_admin');
        $dosen = $this->user('dosen');

        $this->actingAs($super);
        ImpersonationController::start($super, $dosen);

        $this->assertSame($dosen->getKey(), Auth::id());
        $this->assertSame($super->getKey(), (int) session(ImpersonationController::SESSION_KEY));
        $this->assertSame($dosen->getKey(), (int) session(config('sip2m.otp.session_key')));

        (new ImpersonationController)->stop();

        $this->assertSame($super->getKey(), Auth::id());
        $this->assertFalse(session()->has(ImpersonationController::SESSION_KEY));
    }

    public function test_impersonated_session_bypasses_the_otp_gate(): void
    {
        $super = $this->user('super_admin');
        $noPhoneDosen = $this->user('dosen', ['phone_number' => null]);

        $this->actingAs($super)->withSession([
            ImpersonationController::SESSION_KEY => $super->getKey(),
        ]);
        Auth::login($noPhoneDosen);

        // Tanpa nomor HP normalnya diarahkan ke "Lengkapi Profil"; saat menyamar tidak.
        $this->get('/admin')->assertOk();
    }

    public function test_admin_lppm_cannot_impersonate(): void
    {
        $admin = $this->user('admin_lppm');
        $dosen = $this->user('dosen');

        $this->actingAs($admin);

        $this->assertFalse($admin->canImpersonate());

        $this->expectException(HttpException::class);
        ImpersonationController::start($admin, $dosen);
    }

    public function test_super_admin_cannot_be_impersonated(): void
    {
        $super = $this->user('super_admin');
        $super2 = $this->user('super_admin');

        $this->actingAs($super);

        $this->expectException(HttpException::class);
        ImpersonationController::start($super, $super2);
    }

    public function test_impersonate_action_is_visible_only_to_super_admin(): void
    {
        $dosen = $this->user('dosen');

        $this->otpVerified($this->user('super_admin'));
        Livewire::test(ListUsers::class)
            ->assertTableActionVisible('impersonate', $dosen);

        $this->otpVerified($this->user('admin_lppm'));
        Livewire::test(ListUsers::class)
            ->assertTableActionHidden('impersonate', $dosen);
    }

    public function test_impersonate_table_action_switches_the_authenticated_user(): void
    {
        $super = $this->user('super_admin');
        $dosen = $this->user('dosen');

        $this->otpVerified($super);

        Livewire::test(ListUsers::class)
            ->callTableAction('impersonate', $dosen);

        $this->assertSame($dosen->getKey(), Auth::id());
        $this->assertSame($super->getKey(), (int) session(ImpersonationController::SESSION_KEY));
    }
}
