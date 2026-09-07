<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Filament\Auth\Login;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class LoginWithNidnTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRoles();
    }

    public function test_dosen_can_login_with_nidn(): void
    {
        $dosen = User::factory()->create(['nidn' => '0612108702', 'password' => 'password']);
        $dosen->assignRole('dosen');

        Livewire::test(Login::class)
            ->fillForm(['email' => '0612108702', 'password' => 'password'])
            ->call('authenticate');

        $this->assertAuthenticatedAs($dosen);
    }

    public function test_dosen_can_still_login_with_email(): void
    {
        $dosen = User::factory()->create(['nidn' => '0612108702', 'password' => 'password']);
        $dosen->assignRole('dosen');

        Livewire::test(Login::class)
            ->fillForm(['email' => $dosen->email, 'password' => 'password'])
            ->call('authenticate');

        $this->assertAuthenticatedAs($dosen);
    }

    public function test_account_without_nidn_still_logs_in_with_email(): void
    {
        $admin = User::factory()->create(['nidn' => null, 'password' => 'password']);
        $admin->assignRole('admin_lppm');

        Livewire::test(Login::class)
            ->fillForm(['email' => $admin->email, 'password' => 'password'])
            ->call('authenticate');

        $this->assertAuthenticatedAs($admin);
    }

    public function test_wrong_nidn_fails_gracefully(): void
    {
        User::factory()->create(['nidn' => '0612108702', 'password' => 'password'])->assignRole('dosen');

        Livewire::test(Login::class)
            ->fillForm(['email' => '9999999999', 'password' => 'password'])
            ->call('authenticate')
            ->assertHasFormErrors();

        $this->assertGuest();
    }
}
