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

    public function test_admin_lppm_can_set_dana_range_and_target_luaran(): void
    {
        $this->actingOtpVerified('admin_lppm');

        Livewire::test(CreateProposalScheme::class)
            ->fillForm([
                'nama_skema' => 'Penelitian Kolaborasi',
                'kategori' => 'penelitian',
                'dana_min' => 5_000_000,
                'dana_max' => 15_000_000,
                'target_luaran' => 'Minimal 1 artikel jurnal SINTA 2 dan 1 produk/prototipe.',
                'aktif' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $scheme = ProposalScheme::where('nama_skema', 'Penelitian Kolaborasi')->firstOrFail();
        $this->assertSame(5_000_000.0, $scheme->dana_min);
        $this->assertSame(15_000_000.0, $scheme->dana_max);
        $this->assertSame('Minimal 1 artikel jurnal SINTA 2 dan 1 produk/prototipe.', $scheme->target_luaran);
        $this->assertSame('Rp5.000.000 — Rp15.000.000', $scheme->rentangDanaLabel());
    }

    public function test_dana_max_must_be_greater_than_or_equal_to_dana_min(): void
    {
        $this->actingOtpVerified('admin_lppm');

        Livewire::test(CreateProposalScheme::class)
            ->fillForm([
                'nama_skema' => 'Skema Dana Invalid',
                'kategori' => 'penelitian',
                'dana_min' => 20_000_000,
                'dana_max' => 5_000_000,
                'aktif' => true,
            ])
            ->call('create')
            ->assertHasFormErrors(['dana_max']);
    }

    public function test_admin_lppm_can_set_periode_buka_tutup(): void
    {
        $this->actingOtpVerified('admin_lppm');

        Livewire::test(CreateProposalScheme::class)
            ->fillForm([
                'nama_skema' => 'Penelitian Periode',
                'kategori' => 'penelitian',
                'tanggal_buka' => now()->toDateString(),
                'tanggal_tutup' => now()->addMonth()->toDateString(),
                'aktif' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $scheme = ProposalScheme::where('nama_skema', 'Penelitian Periode')->firstOrFail();
        $this->assertNotNull($scheme->tanggal_buka);
        $this->assertNotNull($scheme->tanggal_tutup);
        $this->assertSame('Terbuka', $scheme->statusPeriode());
        $this->assertNotNull($scheme->periodeLabel());
    }

    public function test_tanggal_tutup_must_be_after_or_equal_tanggal_buka(): void
    {
        $this->actingOtpVerified('admin_lppm');

        Livewire::test(CreateProposalScheme::class)
            ->fillForm([
                'nama_skema' => 'Skema Periode Invalid',
                'kategori' => 'penelitian',
                'tanggal_buka' => now()->toDateString(),
                'tanggal_tutup' => now()->subDays(3)->toDateString(),
                'aktif' => true,
            ])
            ->call('create')
            ->assertHasFormErrors(['tanggal_tutup']);
    }

    public function test_tersedia_scope_respects_aktif_and_periode(): void
    {
        $tanpaPeriode = ProposalScheme::factory()->create();
        $nonaktif = ProposalScheme::factory()->nonaktif()->create();
        $belumDibuka = ProposalScheme::factory()->create(['tanggal_buka' => now()->addWeek()]);
        $sudahTutup = ProposalScheme::factory()->create(['tanggal_tutup' => now()->subWeek()]);
        $sedangBuka = ProposalScheme::factory()->create([
            'tanggal_buka' => now()->subDay(), 'tanggal_tutup' => now()->addDay(),
        ]);

        $tersedia = ProposalScheme::query()->tersedia()->pluck('id');

        $this->assertTrue($tersedia->contains($tanpaPeriode->id));
        $this->assertTrue($tersedia->contains($sedangBuka->id));
        $this->assertFalse($tersedia->contains($nonaktif->id));
        $this->assertFalse($tersedia->contains($belumDibuka->id));
        $this->assertFalse($tersedia->contains($sudahTutup->id));

        $this->assertSame('Belum Dibuka', $belumDibuka->statusPeriode());
        $this->assertSame('Sudah Ditutup', $sudahTutup->statusPeriode());
        $this->assertSame('Nonaktif', $nonaktif->statusPeriode());
        $this->assertSame('Terbuka', $sedangBuka->statusPeriode());
        $this->assertSame('Terbuka', $tanpaPeriode->statusPeriode());
    }

    public function test_scheme_outside_its_period_is_hidden_from_dosen_menu_and_wizard(): void
    {
        $this->actingOtpVerified('dosen');

        ProposalScheme::factory()->penelitian()->create([
            'nama_skema' => 'Skema Sudah Tutup', 'tanggal_tutup' => now()->subDay(),
        ]);
        ProposalScheme::factory()->penelitian()->create(['nama_skema' => 'Skema Sedang Buka']);

        $method = new \ReflectionMethod(\App\Providers\Filament\AdminPanelProvider::class, 'dosenNavigationItems');
        $method->setAccessible(true);
        $labels = collect($method->invoke(new \App\Providers\Filament\AdminPanelProvider(app())))
            ->map(fn ($i) => $i->getLabel());

        $this->assertTrue($labels->contains('Skema Sedang Buka'));
        $this->assertFalse($labels->contains('Skema Sudah Tutup'));

        \Livewire\Livewire::withQueryParams(['kat' => 'penelitian'])
            ->test(\App\Filament\Resources\ProposalResource\Pages\CreateProposal::class)
            ->assertSee('Skema Sedang Buka')
            ->assertDontSee('Skema Sudah Tutup');
    }
}
