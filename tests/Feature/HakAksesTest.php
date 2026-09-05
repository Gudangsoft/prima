<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Resources\RoleResource;
use App\Filament\Resources\RoleResource\Pages\EditRole;
use App\Filament\Resources\RoleResource\Pages\ListRoles;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class HakAksesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRoles();
    }

    private function actingOtpVerified(string $role): User
    {
        $user = User::factory()->create(['phone_number' => '081234567890']);
        $user->assignRole($role);
        $this->actingAs($user)->withSession([config('sip2m.otp.session_key') => $user->getKey()]);

        return $user;
    }

    public function test_only_super_admin_can_open_hak_akses(): void
    {
        $this->actingOtpVerified('super_admin');
        $this->get('/admin/hak-akses')->assertOk();

        foreach (['admin_lppm', 'pimpinan', 'dosen', 'reviewer'] as $role) {
            $this->actingOtpVerified($role);
            $this->get('/admin/hak-akses')->assertForbidden();
        }
    }

    public function test_super_admin_can_change_permissions_of_a_role(): void
    {
        $this->actingOtpVerified('super_admin');

        $reviewer = Role::findByName('reviewer');
        $perm = Permission::findByName('schemes.create');
        $this->assertFalse($reviewer->hasPermissionTo('schemes.create'));

        Livewire::test(EditRole::class, ['record' => $reviewer->getKey()])
            ->fillForm(['permissions' => $reviewer->permissions->pluck('id')->push($perm->id)->all()])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertTrue($reviewer->fresh()->hasPermissionTo('schemes.create'));
    }

    public function test_built_in_role_has_no_delete_action(): void
    {
        $this->actingOtpVerified('super_admin');

        $this->assertTrue(RoleResource::isBuiltIn(Role::findByName('admin_lppm')));

        Livewire::test(ListRoles::class)
            ->assertTableActionHidden('delete', Role::findByName('admin_lppm'));
    }

    public function test_custom_role_delete_action_depends_on_usage(): void
    {
        $this->actingOtpVerified('super_admin');
        $custom = Role::create(['name' => 'operator_fakultas', 'guard_name' => 'web']);

        Livewire::test(ListRoles::class)
            ->assertTableActionVisible('delete', $custom);

        $custom->users()->attach(User::factory()->create()->id);

        Livewire::test(ListRoles::class)
            ->assertTableActionHidden('delete', $custom);
    }
}
