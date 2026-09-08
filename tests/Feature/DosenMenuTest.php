<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\ProposalStatus;
use App\Filament\Pages\Kegiatan;
use App\Filament\Pages\ModulBelumTersedia;
use App\Filament\Resources\ProposalResource;
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

    public function test_dosen_top_menu_lists_active_schemes_bima_style(): void
    {
        $this->actAs('dosen');

        $this->get('/admin')
            ->assertOk()
            ->assertSee('Konsorsium')
            ->assertSee('Prototipe')
            ->assertSee('Kekayaan Intelektual');
    }

    /**
     * dosenNavigationItems() dievaluasi sekali saat boot panel (array biasa,
     * bukan closure — batasan API Filament), jadi di aplikasi nyata ia selalu
     * membaca skema terbaru karena tiap request PHP-FPM mem-boot ulang app
     * dari nol. Di dalam satu metode tes, app sudah ter-boot sebelum baris
     * kode tes ini berjalan, jadi kita panggil method-nya langsung (bukan
     * lewat HTTP) supaya query skema memakai data yang baru dibuat di sini.
     */
    public function test_dosen_menu_items_are_generated_from_active_schemes(): void
    {
        $pen = ProposalScheme::factory()->penelitian()->create(['nama_skema' => 'Riset Dasar Unggulan']);
        ProposalScheme::factory()->penelitian()->nonaktif()->create(['nama_skema' => 'Riset Nonaktif Tersembunyi']);
        $pkm = ProposalScheme::factory()->pengabdian()->create(['nama_skema' => 'PKM Kemitraan Masyarakat']);

        $method = new \ReflectionMethod(\App\Providers\Filament\AdminPanelProvider::class, 'dosenNavigationItems');
        $method->setAccessible(true);
        $items = $method->invoke(new \App\Providers\Filament\AdminPanelProvider(app()));

        $labels = array_map(fn ($item) => $item->getLabel(), $items);

        $this->assertContains('Riset Dasar Unggulan', $labels);
        $this->assertContains('PKM Kemitraan Masyarakat', $labels);
        $this->assertNotContains('Riset Nonaktif Tersembunyi', $labels);
        $this->assertNotContains('Kelola Usulan Saya', $labels);

        $penItem = collect($items)->first(fn ($item) => $item->getLabel() === 'Riset Dasar Unggulan');
        $this->assertSame(
            Kegiatan::urlFor('penelitian', 'usulan', $pen->id),
            $penItem->getUrl(),
        );

        $pkmItem = collect($items)->first(fn ($item) => $item->getLabel() === 'PKM Kemitraan Masyarakat');
        $this->assertSame(
            Kegiatan::urlFor('pengabdian', 'usulan', $pkm->id),
            $pkmItem->getUrl(),
        );
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

    public function test_create_form_preselects_scheme_from_query_params(): void
    {
        $this->actAs('dosen');

        $pen = ProposalScheme::factory()->penelitian()->create();

        Livewire::withQueryParams(['kat' => 'penelitian', 'scheme' => $pen->id])
            ->test(CreateProposal::class)
            ->assertOk()
            ->assertFormSet(['scheme_id' => $pen->id]);
    }

    public function test_menu_link_opens_usulan_table_filtered_to_that_scheme(): void
    {
        $dosen = $this->actAs('dosen');

        $pen = ProposalScheme::factory()->penelitian()->create(['nama_skema' => 'Riset Dasar Unggulan']);
        $penLain = ProposalScheme::factory()->penelitian()->create(['nama_skema' => 'Riset Terapan Lain']);

        Proposal::factory()->forDosen($dosen)->forScheme($pen)->create(['judul' => 'Usulan Skema Ini']);
        Proposal::factory()->forDosen($dosen)->forScheme($penLain)->create(['judul' => 'Usulan Skema Lain']);

        $test = Livewire::withQueryParams(['kategori' => 'penelitian', 'tab' => 'usulan', 'skema' => $pen->id])
            ->test(Kegiatan::class)
            ->assertOk()
            ->assertSee('Riset Dasar Unggulan')
            ->assertSee('Hapus filter');

        $this->assertCount(1, $test->instance()->getRowsProperty());

        // Tombol "Ajukan Usulan Baru" pada halaman terfilter membawa skema ini.
        $test->assertSeeHtml(e(ProposalResource::getUrl('create', ['kat' => 'penelitian', 'scheme' => $pen->id])));
    }

    public function test_kegiatan_ignores_inactive_or_foreign_kategori_scheme_param(): void
    {
        $this->actAs('dosen');

        $nonaktif = ProposalScheme::factory()->penelitian()->nonaktif()->create();
        $pengabdian = ProposalScheme::factory()->pengabdian()->create();

        Livewire::withQueryParams(['kategori' => 'penelitian', 'tab' => 'usulan', 'skema' => $nonaktif->id])
            ->test(Kegiatan::class)
            ->assertOk()
            ->assertSet('skema', null);

        Livewire::withQueryParams(['kategori' => 'penelitian', 'tab' => 'usulan', 'skema' => $pengabdian->id])
            ->test(Kegiatan::class)
            ->assertOk()
            ->assertSet('skema', null);
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
