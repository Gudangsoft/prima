<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\ProposalStatus;
use App\Filament\Pages\Kegiatan;
use App\Filament\Pages\ModulBelumTersedia;
use App\Filament\Resources\ProposalResource\Pages\CreateProposal;
use App\Models\Proposal;
use App\Models\ProposalScheme;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DosenMenuTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRoles();
    }

    private function actAs(string $role): User
    {
        $user = User::factory()->create(['phone_number' => '0812']);
        $user->assignRole($role);
        $this->actingAs($user)->withSession([config('sip2m.otp.session_key') => $user->getKey()]);

        return $user;
    }

    public function test_dosen_top_menu_mirrors_bima_tabs(): void
    {
        $this->actAs('dosen');

        $this->get('/admin')
            ->assertOk()
            ->assertSee('Konsorsium')
            ->assertSee('Prototipe')
            ->assertSee('Kekayaan Intelektual')
            ->assertSee('Bimbingan Teknis')
            ->assertSee('Perbaikan Usulan')
            ->assertSee('Catatan Harian')
            ->assertSee('Laporan Kemajuan')
            ->assertSee('Laporan Akhir')
            ->assertSee('Pengkinian Capaian Luaran');
    }

    public function test_placeholder_menus_are_hidden_from_oversight(): void
    {
        $this->actAs('admin_lppm');

        $this->get('/admin')
            ->assertOk()
            ->assertDontSee('Konsorsium')
            ->assertDontSee('Kekayaan Intelektual')
            ->assertDontSee('Pengkinian Capaian Luaran');
    }

    public function test_kegiatan_tabs_render_and_never_mix_kategori(): void
    {
        $dosen = $this->actAs('dosen');

        $pen = ProposalScheme::factory()->penelitian()->create();
        $pkm = ProposalScheme::factory()->pengabdian()->create();

        Proposal::factory()->forDosen($dosen)->forScheme($pen)->create(['judul' => 'Riset Penelitian A']);
        Proposal::factory()->forDosen($dosen)->forScheme($pkm)->create(['judul' => 'Kegiatan Pengabdian B']);
        Proposal::factory()->forDosen($dosen)->forScheme($pen)
            ->status(ProposalStatus::InProgress)->create(['judul' => 'Riset Berjalan C']);

        foreach (array_keys(Kegiatan::TABS) as $tab) {
            Livewire::withQueryParams(['kategori' => 'penelitian', 'tab' => $tab])
                ->test(Kegiatan::class)
                ->assertOk();
        }

        // Tab "Usulan": hanya penelitian pada halaman penelitian.
        $penRows = Livewire::withQueryParams(['kategori' => 'penelitian', 'tab' => 'usulan'])
            ->test(Kegiatan::class)->instance()->getRowsProperty();
        $this->assertCount(2, $penRows); // Riset A + Riset Berjalan C

        $pkmRows = Livewire::withQueryParams(['kategori' => 'pengabdian', 'tab' => 'usulan'])
            ->test(Kegiatan::class)->instance()->getRowsProperty();
        $this->assertCount(1, $pkmRows);

        // Tab "Catatan Harian": hanya usulan berjalan.
        $catatan = Livewire::withQueryParams(['kategori' => 'penelitian', 'tab' => 'catatan'])
            ->test(Kegiatan::class)->instance()->getRowsProperty();
        $this->assertCount(1, $catatan);
    }

    public function test_kegiatan_normalises_bad_params_and_blocks_non_dosen(): void
    {
        $this->actAs('dosen');
        Livewire::withQueryParams(['kategori' => 'ngawur', 'tab' => 'ngawur'])
            ->test(Kegiatan::class)
            ->assertOk()
            ->assertSet('kategori', 'penelitian')
            ->assertSet('tab', 'usulan');

        $this->actAs('admin_lppm');
        $this->get(Kegiatan::urlFor('penelitian', 'usulan'))->assertForbidden();
    }

    public function test_create_form_scheme_options_are_locked_to_context_kategori(): void
    {
        $this->actAs('dosen');

        $pen = ProposalScheme::factory()->penelitian()->create();
        $pkm = ProposalScheme::factory()->pengabdian()->create();

        Livewire::withQueryParams(['kat' => 'penelitian'])
            ->test(CreateProposal::class)
            ->assertOk()
            ->assertSee($pen->nama_skema)
            ->assertDontSee($pkm->nama_skema);

        Livewire::withQueryParams(['kat' => 'pengabdian'])
            ->test(CreateProposal::class)
            ->assertSee($pkm->nama_skema)
            ->assertDontSee($pen->nama_skema);

        Livewire::withQueryParams([])
            ->test(CreateProposal::class)
            ->assertSee($pen->nama_skema)
            ->assertSee($pkm->nama_skema);
    }

    public function test_modul_belum_tersedia_renders(): void
    {
        $this->actAs('dosen');

        $this->get(ModulBelumTersedia::urlFor('konsorsium'))
            ->assertOk()
            ->assertSee('Modul Konsorsium')
            ->assertSee('belum tersedia');
    }

    public function test_usulan_tab_shows_bima_style_columns_and_comments(): void
    {
        $dosen = $this->actAs('dosen');
        $admin = tap(User::factory()->create(), fn (User $u) => $u->assignRole('admin_lppm'));
        $reviewer = tap(User::factory()->create(), fn (User $u) => $u->assignRole('reviewer'));

        $skema = ProposalScheme::factory()->penelitian()->create();
        $proposal = Proposal::factory()->forDosen($dosen)->forScheme($skema)
            ->status(ProposalStatus::UnderReview)
            ->create(['judul' => 'Usulan Berkomentar']);

        $proposal->approvals()->create(['approved_by' => $admin->id, 'status' => 'approved', 'catatan' => 'Lengkapi RAB.']);
        $proposal->reviews()->create(['reviewer_id' => $reviewer->id, 'catatan' => 'Metodologi perlu diperjelas.']);

        $this->get(Kegiatan::urlFor('penelitian', 'usulan'))
            ->assertOk()
            ->assertSee('Bidang Fokus')
            ->assertSee('Komentar LPPM')
            ->assertSee('Komentar Reviewer')
            ->assertSee('Info Eligibilitas');

        $rows = Livewire::withQueryParams(['kategori' => 'penelitian', 'tab' => 'usulan'])
            ->test(Kegiatan::class)->instance()->getRowsProperty();

        $row = $rows->first();
        $this->assertStringContainsString('Lengkapi RAB.', $row[7]['items'][0]);
        $this->assertStringContainsString('Metodologi perlu diperjelas.', $row[8]['items'][0]);
    }

    public function test_eligibilitas_flags_scheme_with_existing_proposal_this_year(): void
    {
        $dosen = $this->actAs('dosen');

        $sudahDiajukan = ProposalScheme::factory()->penelitian()->create(['nama_skema' => 'Riset Dasar']);
        $belumDiajukan = ProposalScheme::factory()->penelitian()->create(['nama_skema' => 'Riset Terapan']);

        Proposal::factory()->forDosen($dosen)->forScheme($sudahDiajukan)
            ->create(['tahun_anggaran' => (int) now()->year]);

        $eligibilitas = Livewire::withQueryParams(['kategori' => 'penelitian', 'tab' => 'usulan'])
            ->test(Kegiatan::class)->instance()->getEligibilitasProperty();

        $this->assertContains('Riset Terapan', $eligibilitas['eligible']);
        $this->assertNotContains('Riset Dasar', $eligibilitas['eligible']);
        $this->assertSame('Riset Dasar', $eligibilitas['tidak'][0]['nama']);
    }
}
