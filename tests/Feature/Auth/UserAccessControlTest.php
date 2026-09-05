<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserAccessControlTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRoles();
    }

    private function actingAsOtpVerified(string $role): User
    {
        $user = User::factory()->create(['phone_number' => '081234567890']);
        $user->assignRole($role);

        $this->actingAs($user)->withSession([
            config('sip2m.otp.session_key') => $user->getKey(),
        ]);

        return $user;
    }

    public function test_dosen_cannot_open_user_management(): void
    {
        $this->actingAsOtpVerified('dosen');

        $this->get('/admin/users')->assertForbidden();
    }

    public function test_reviewer_cannot_open_user_management(): void
    {
        $this->actingAsOtpVerified('reviewer');

        $this->get('/admin/users')->assertForbidden();
    }

    public function test_admin_lppm_can_open_user_management(): void
    {
        $this->actingAsOtpVerified('admin_lppm');

        $this->get('/admin/users')->assertOk();
    }

    public function test_pimpinan_can_view_but_not_create_users(): void
    {
        $pimpinan = $this->actingAsOtpVerified('pimpinan');

        $this->get('/admin/users')->assertOk();
        $this->get('/admin/users/create')->assertForbidden();

        $this->assertTrue($pimpinan->can('users.viewAny'));
        $this->assertFalse($pimpinan->can('users.create'));
    }

    public function test_super_admin_bypasses_all_permission_checks(): void
    {
        $admin = $this->actingAsOtpVerified('super_admin');

        $this->get('/admin/users')->assertOk();
        $this->get('/admin/users/create')->assertOk();

        $this->assertTrue($admin->can('users.delete'));
    }
}
