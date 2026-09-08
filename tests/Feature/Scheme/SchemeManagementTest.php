<?php

declare(strict_types=1);

namespace Tests\Feature\Scheme;

use App\Filament\Resources\ProposalSchemeResource\Pages\CreateProposalScheme;
use App\Models\ProposalScheme;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class SchemeManagementTest extends TestCase
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

    public function test_admin_lppm_can_open_scheme_management(): void
    {
        $this->actingOtpVerified('admin_lppm');
        $this->get('/admin/skema')->assertOk();
        $this->get('/admin/skema/create')->assertOk();
    }

    public function test_dosen_and_reviewer_cannot_manage_schemes(): void
    {
        $this->actingOtpVerified('dosen');
        $this->get('/admin/skema')->assertForbidden();

        $this->actingOtpVerified('reviewer');
        $this->get('/admin/skema')->assertForbidden();
    }

    public function test_pimpinan_can_view_schemes_but_not_create(): void
    {
        $pimpinan = $this->actingOtpVerified('pimpinan');

        $this->get('/admin/skema')->assertOk();
        $this->get('/admin/skema/create')->assertForbidden();

        $this->assertTrue($pimpinan->can('viewAny', ProposalScheme::class));
        $this->assertFalse($pimpinan->can('create', ProposalScheme::class));
    }

    public function test_aktif_scope_filters_inactive_schemes(): void
    {
        ProposalScheme::factory()->count(3)->create();
        ProposalScheme::factory()->nonaktif()->count(2)->create();

        $this->assertSame(3, ProposalScheme::query()->aktif()->count());
    }

    public function test_admin_lppm_can_upload_template_for_penelitian_and_pengabdian_scheme(): void
    {
        Storage::fake('public');
        $this->actingOtpVerified('admin_lppm');

        foreach (['penelitian', 'pengabdian'] as $kategori) {
            Livewire::test(CreateProposalScheme::class)
                ->fillForm([
                    'nama_skema' => 'Skema '.$kategori,
                    'kategori' => $kategori,
                    'template_path' => UploadedFile::fake()->create('template.pdf', 500, 'application/pdf'),
                    'aktif' => true,
                ])
                ->call('create')
                ->assertHasNoFormErrors();

            $scheme = ProposalScheme::where('nama_skema', 'Skema '.$kategori)->first();
            $this->assertNotNull($scheme);
            $this->assertNotNull($scheme->template_path);
            Storage::disk('public')->assertExists($scheme->template_path);
            $this->assertNotNull($scheme->templateUrl());
        }
    }
}
