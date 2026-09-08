<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Auth\EditProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class ProfilTest extends TestCase
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

    public function test_profile_page_renders_for_any_authenticated_role(): void
    {
        foreach (['dosen', 'reviewer', 'admin_lppm'] as $role) {
            $this->actingOtpVerified($role);
            $this->get('/admin/profile')->assertOk();
        }
    }

    public function test_user_can_update_their_full_profile(): void
    {
        $user = $this->actingOtpVerified('dosen');

        Livewire::test(EditProfile::class)
            ->fillForm([
                'name' => 'Dr. Budi Santoso',
                'nidn' => '0405058001',
                'jabatan' => 'Lektor Kepala',
                'unit_kerja' => 'Prodi Informatika',
                'kompetensi' => 'Kecerdasan Buatan',
                'bio' => 'Peneliti bidang AI.',
                'phone_number' => '081999888777',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $user->refresh();
        $this->assertSame('Dr. Budi Santoso', $user->name);
        $this->assertSame('Lektor Kepala', $user->jabatan);
        $this->assertSame('Prodi Informatika', $user->unit_kerja);
        $this->assertSame('Peneliti bidang AI.', $user->bio);
        $this->assertSame('081999888777', $user->phone_number);
    }

    public function test_dosen_can_input_their_own_scopus_and_wos_data(): void
    {
        $user = $this->actingOtpVerified('dosen');

        Livewire::test(EditProfile::class)
            ->fillForm([
                'telepon' => '0271123456',
                'scopus_id' => '57363389100',
                'scopus_h_index' => '2',
                'scopus_articles' => '7',
                'scopus_citation' => '17',
                'wos_score' => '0',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $user->refresh();
        $this->assertSame('0271123456', $user->telepon);
        $this->assertSame('57363389100', $user->scopus_id);
        $this->assertSame(2, $user->scopus_h_index);
        $this->assertSame(7, $user->scopus_articles);
        $this->assertSame(17, $user->scopus_citation);
        $this->assertSame(0, $user->wos_score);
    }

    public function test_user_can_upload_avatar_and_it_becomes_the_filament_avatar(): void
    {
        Storage::fake('public');
        $user = $this->actingOtpVerified('admin_lppm');

        Livewire::test(EditProfile::class)
            ->fillForm(['avatar_path' => UploadedFile::fake()->image('foto.jpg', 300, 300)])
            ->call('save')
            ->assertHasNoFormErrors();

        $user->refresh();
        $this->assertNotNull($user->avatar_path);
        Storage::disk('public')->assertExists($user->avatar_path);
        $this->assertStringContainsString($user->avatar_path, (string) $user->getFilamentAvatarUrl());
    }

    public function test_user_can_change_password(): void
    {
        $user = $this->actingOtpVerified('reviewer');

        Livewire::test(EditProfile::class)
            ->fillForm([
                'password' => 'RahasiaBaru#2026',
                'passwordConfirmation' => 'RahasiaBaru#2026',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertTrue(Hash::check('RahasiaBaru#2026', $user->refresh()->password));
    }
}
