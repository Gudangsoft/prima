<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Pages\Kegiatan;
use App\Filament\Pages\MonitoringPelaksanaan;
use App\Models\User;
use App\Providers\Filament\AdminPanelProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * "Peran Aktif": akun dengan >1 role bisa berpindah lensa tampilan lewat
 * dropdown di menu pengguna, tanpa mengubah role/otorisasi data sungguhan.
 */
class ActiveRoleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRoles();
    }

    private function actAs(User $user): self
    {
        $this->actingAs($user)->withSession([config('sip2m.otp.session_key') => $user->getKey()]);

        return $this;
    }

    public function test_single_role_user_active_role_is_simply_that_role(): void
    {
        $dosen = User::factory()->create();
        $dosen->assignRole('dosen');

        $this->assertSame('dosen', $dosen->activeRole());
        $this->assertTrue($dosen->isActingAs('dosen'));
        $this->assertFalse($dosen->isActingAs('admin_lppm'));
    }

    public function test_multi_role_user_defaults_to_highest_priority_role(): void
    {
        $user = User::factory()->create();
        $user->syncRoles(['dosen', 'admin_lppm']);

        $this->assertSame('admin_lppm', $user->activeRole());
        $this->assertTrue($user->isActingAs('admin_lppm'));
        $this->assertFalse($user->isActingAs('dosen'));
    }

    public function test_active_role_reads_from_session_when_valid(): void
    {
        $user = User::factory()->create();
        $user->syncRoles(['dosen', 'admin_lppm']);

        $this->actAs($user);
        session(['active_role' => 'dosen']);

        $this->assertSame('dosen', $user->activeRole());
        $this->assertTrue($user->isActingAs('dosen'));
    }

    public function test_active_role_ignores_invalid_or_unowned_session_value(): void
    {
        $user = User::factory()->create();
        $user->syncRoles(['dosen', 'admin_lppm']);

        $this->actAs($user);
        session(['active_role' => 'reviewer']); // tidak dimiliki user ini

        $this->assertSame('admin_lppm', $user->activeRole());
    }

    public function test_switch_role_route_updates_active_role_for_multi_role_user(): void
    {
        $user = User::factory()->create();
        $user->syncRoles(['dosen', 'admin_lppm']);
        $this->actAs($user);

        $this->get('/switch-role/dosen')->assertRedirect('/admin');

        $this->assertSame('dosen', session('active_role'));
        $this->assertSame('dosen', $user->activeRole());
    }

    public function test_switch_role_route_rejects_role_user_does_not_have(): void
    {
        $user = User::factory()->create();
        $user->assignRole('reviewer');
        $this->actAs($user);

        $this->get('/switch-role/admin_lppm')->assertRedirect('/admin');

        $this->assertNull(session('active_role'));
        $this->assertSame('reviewer', $user->activeRole());
    }

    public function test_switching_active_role_toggles_dosen_menu_and_oversight_page_access(): void
    {
        $user = User::factory()->create(['phone_number' => '0812']);
        $user->syncRoles(['dosen', 'admin_lppm']);
        $this->actAs($user);

        // Default: admin_lppm aktif -> halaman oversight bisa diakses, menu dosen tidak.
        $this->assertTrue(MonitoringPelaksanaan::canAccess());
        $this->assertFalse(Kegiatan::canAccess());

        $method = new \ReflectionMethod(AdminPanelProvider::class, 'dosenNavigationItems');
        $method->setAccessible(true);
        $items = $method->invoke(new AdminPanelProvider(app()));
        $this->assertFalse(collect($items)->contains(fn ($i) => $i->isVisible()));

        // Beralih ke dosen -> sebaliknya.
        $this->get('/switch-role/dosen')->assertRedirect('/admin');

        $this->assertFalse(MonitoringPelaksanaan::canAccess());
        $this->assertTrue(Kegiatan::canAccess());

        $items = $method->invoke(new AdminPanelProvider(app()));
        $this->assertTrue(collect($items)->contains(fn ($i) => $i->isVisible()));
    }

    public function test_user_menu_lists_switch_options_only_for_multi_role_accounts(): void
    {
        $tunggal = User::factory()->create();
        $tunggal->assignRole('reviewer');
        $this->actAs($tunggal);

        $method = new \ReflectionMethod(AdminPanelProvider::class, 'roleMenuItems');
        $method->setAccessible(true);
        $items = $method->invoke(new AdminPanelProvider(app()));

        $switchItems = collect($items)->except('profile')->filter(fn ($i) => $i->isVisible());
        $this->assertTrue($switchItems->isEmpty(), 'akun 1 role tidak perlu opsi ganti peran');
        $this->assertStringContainsString('Reviewer', $items['profile']->getLabel());

        $ganda = User::factory()->create();
        $ganda->syncRoles(['dosen', 'reviewer']);
        $this->actAs($ganda);

        $items = $method->invoke(new AdminPanelProvider(app()));
        $switchItems = collect($items)->except('profile')->filter(fn ($i) => $i->isVisible());

        $this->assertCount(1, $switchItems, 'hanya role lain (bukan yang aktif) yang ditawarkan');
        $this->assertStringContainsString('Dosen', $switchItems->first()->getLabel());
        $this->assertStringContainsString('Reviewer', $items['profile']->getLabel());
    }
}
