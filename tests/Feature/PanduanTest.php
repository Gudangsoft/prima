<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Support\Settings;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PanduanTest extends TestCase
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

    public function test_panduan_page_is_open_to_any_authenticated_user(): void
    {
        foreach (['dosen', 'reviewer', 'admin_lppm', 'super_admin'] as $role) {
            $this->actingOtpVerified($role);
            $this->get('/admin/panduan')->assertOk()->assertSee('Panduan Pengguna');
        }
    }

    public function test_admin_guide_tab_is_only_shown_to_oversight_roles(): void
    {
        $this->actingOtpVerified('dosen');
        $this->get('/admin/panduan')
            ->assertOk()
            ->assertSee('Mengajukan Usulan Baru')
            ->assertDontSee('Panduan Admin LPPM')
            ->assertDontSee('Alur Penilaian Usulan');

        $this->actingOtpVerified('admin_lppm');
        $this->get('/admin/panduan')
            ->assertOk()
            ->assertSee('Panduan Admin LPPM')
            ->assertSee('Alur Penilaian Usulan');
    }

    public function test_pdf_download_link_appears_when_super_admin_uploads_one(): void
    {
        Settings::set('panduan_pengguna_path', 'panduan/panduan-dosen.pdf');

        $this->actingOtpVerified('dosen');
        $this->get('/admin/panduan')
            ->assertOk()
            ->assertSee('Unduh Panduan Lengkap (PDF)')
            ->assertSee('panduan/panduan-dosen.pdf');
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/admin/panduan')->assertRedirect();
    }
}
