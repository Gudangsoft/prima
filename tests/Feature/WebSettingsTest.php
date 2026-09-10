<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Pages\PengaturanWeb;
use App\Filament\Resources\AnnouncementResource\Pages\CreateAnnouncement;
use App\Models\Announcement;
use App\Models\User;
use App\Support\Settings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class WebSettingsTest extends TestCase
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

    public function test_pengaturan_web_is_super_admin_only(): void
    {
        $this->actingOtpVerified('super_admin');
        $this->get('/admin/pengaturan-web')->assertOk();

        foreach (['admin_lppm', 'pimpinan', 'dosen'] as $role) {
            $this->actingOtpVerified($role);
            $this->get('/admin/pengaturan-web')->assertForbidden();
        }
    }

    public function test_super_admin_can_save_branding_and_institution_settings(): void
    {
        $this->actingOtpVerified('super_admin');

        Livewire::test(PengaturanWeb::class)
            ->fillForm([
                'app_name' => 'RISET-KAMPUS',
                'primary_color' => '#0F766E',
                'institusi_nama' => 'Universitas Merdeka',
                'hero_title' => 'Judul Baru',
                'footer_lembaga' => 'LPPM Universitas Merdeka',
                'footer_email' => 'lppm@merdeka.ac.id',
                'footer_copyright' => 'LPPM UM',
            ])
            ->call('simpan')
            ->assertHasNoFormErrors();

        $this->assertSame('RISET-KAMPUS', Settings::get('app_name'));
        $this->assertSame('Universitas Merdeka', Settings::institution()['nama']);
        $this->assertSame('Judul Baru', Settings::branding()['hero_title']);
        $this->assertSame('LPPM Universitas Merdeka', Settings::footer()['lembaga']);
        $this->assertSame('lppm@merdeka.ac.id', Settings::footer()['email']);

        $this->get('/')->assertOk()->assertSee('LPPM Universitas Merdeka')->assertSee('lppm@merdeka.ac.id');
    }

    public function test_second_institution_logo_renders_across_pages_when_set(): void
    {
        Settings::set('logo_instansi_path', 'branding/logo-instansi.png');

        // Halaman depan (header + footer).
        $this->get('/')
            ->assertOk()
            ->assertSee('branding/logo-instansi.png')
            ->assertSee('Logo Instansi');

        // Header panel admin + halaman login memakai brand logo yang sama.
        $this->get('/admin/login')
            ->assertOk()
            ->assertSee('branding/logo-instansi.png');
    }

    public function test_pages_render_fine_without_a_second_logo(): void
    {
        $this->get('/')->assertOk()->assertDontSee('Logo Instansi');
        $this->get('/admin/login')->assertOk();
    }

    public function test_admin_lppm_can_create_announcement_with_pdf(): void
    {
        Storage::fake('public');
        $admin = $this->actingOtpVerified('admin_lppm');

        Livewire::test(CreateAnnouncement::class)
            ->fillForm([
                'judul' => 'Sosialisasi Skema 2026',
                'isi' => '<p>Hadir ya.</p>',
                'tanggal_terbit' => now()->toDateString(),
                'lampiran_pdf' => UploadedFile::fake()->create('panduan.pdf', 120, 'application/pdf'),
                'terbit' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $a = Announcement::firstWhere('judul', 'Sosialisasi Skema 2026');
        $this->assertNotNull($a);
        $this->assertSame($admin->id, $a->created_by);
        $this->assertNotNull($a->lampiran_pdf);
        Storage::disk('public')->assertExists($a->lampiran_pdf);
    }

    public function test_reviewer_cannot_manage_announcements(): void
    {
        $this->actingOtpVerified('reviewer');
        $this->get('/admin/pengumuman')->assertForbidden();
    }
}
