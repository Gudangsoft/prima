<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Actions\Proposal\RecordMonevInternal;
use App\Enums\MonevRekomendasi;
use App\Enums\ProposalStatus;
use App\Filament\Pages\MonitoringCatatanHarian;
use App\Filament\Pages\MonitoringCatatanHarianDetail;
use App\Filament\Pages\MonitoringCatatanHarianLog;
use App\Filament\Pages\MonitoringPelaksanaan;
use App\Filament\Pages\MonitoringPelaksanaanDetail;
use App\Models\CatatanHarian;
use App\Models\MonevInternal;
use App\Models\MonitoringReport;
use App\Models\Proposal;
use App\Models\ProposalScheme;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MonitoringExtraTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRoles();
    }

    private function user(string $role): User
    {
        $u = User::factory()->create(['phone_number' => '0812']);
        $u->assignRole($role);

        return $u;
    }

    private function actAs(User $u): self
    {
        $this->actingAs($u)->withSession([config('sip2m.otp.session_key') => $u->getKey()]);

        return $this;
    }

    public function test_monitoring_pages_are_gated_to_oversight(): void
    {
        $this->actAs($this->user('admin_lppm'));
        $this->get('/admin/monitoring-catatan-harian')->assertOk();
        $this->get('/admin/monev-internal-pt')->assertOk();

        $this->actAs($this->user('dosen'));
        $this->get('/admin/monitoring-catatan-harian')->assertForbidden();
        $this->get('/admin/monev-internal-pt')->assertForbidden();
    }

    public function test_logbook_policy_allows_owner_of_running_proposal_only(): void
    {
        $dosen = $this->user('dosen');
        $other = $this->user('dosen');

        $running = Proposal::factory()->status(ProposalStatus::InProgress)->forDosen($dosen)->create();
        $draft = Proposal::factory()->forDosen($dosen)->create();

        $this->assertTrue($dosen->can('logbook', $running));
        $this->assertFalse($dosen->can('logbook', $draft), 'usulan belum berjalan');
        $this->assertFalse($other->can('logbook', $running), 'bukan pemilik');
    }

    public function test_catatan_harian_cascade_deletes_with_proposal(): void
    {
        $p = Proposal::factory()->status(ProposalStatus::InProgress)->create();
        CatatanHarian::create(['proposal_id' => $p->id, 'tanggal' => now(), 'kegiatan' => 'x']);

        $p->delete();

        $this->assertDatabaseCount('catatan_harian', 0);
    }

    public function test_monev_internal_is_recorded_and_updated_once_per_proposal(): void
    {
        $admin = $this->user('admin_lppm');
        $proposal = Proposal::factory()->status(ProposalStatus::InProgress)->create();

        $this->assertTrue($admin->can('monevInternal', $proposal));

        app(RecordMonevInternal::class)($proposal, $admin, now()->toDateString(), 70, MonevRekomendasi::LanjutPerbaikan, 'perbaiki metode');
        app(RecordMonevInternal::class)($proposal->fresh(), $admin, now()->toDateString(), 85, MonevRekomendasi::Lanjut, 'sudah membaik');

        $this->assertSame(1, MonevInternal::where('proposal_id', $proposal->id)->count());
        $monev = $proposal->fresh()->monevInternal;
        $this->assertSame(85, $monev->skor_capaian);
        $this->assertSame(MonevRekomendasi::Lanjut, $monev->rekomendasi);
    }

    public function test_dosen_cannot_run_monev(): void
    {
        $dosen = $this->user('dosen');
        $proposal = Proposal::factory()->status(ProposalStatus::InProgress)->forDosen($dosen)->create();

        $this->assertFalse($dosen->can('monevInternal', $proposal));
    }

    public function test_monitoring_pelaksanaan_recap_counts_upload_progress_per_scheme(): void
    {
        $this->actAs($this->user('admin_lppm'));

        $year = (int) now()->year;
        $scheme = ProposalScheme::factory()->penelitian()->create(['nama_skema' => 'Skema Uji']);
        $other = ProposalScheme::factory()->pengabdian()->create(['nama_skema' => 'Skema Abai']);

        $p1 = Proposal::factory()->forScheme($scheme)->status(ProposalStatus::InProgress)->create(['tahun_anggaran' => $year]);
        Proposal::factory()->forScheme($scheme)->status(ProposalStatus::Funded)->create(['tahun_anggaran' => $year]);
        Proposal::factory()->forScheme($other)->status(ProposalStatus::Funded)->create(['tahun_anggaran' => $year]);          // kategori lain
        Proposal::factory()->forScheme($scheme)->status(ProposalStatus::Funded)->create(['tahun_anggaran' => $year - 1]);    // tahun lain

        MonitoringReport::create([
            'proposal_id' => $p1->id, 'jenis' => 'kemajuan',
            'file_laporan' => 'laporan/x.pdf', 'tanggal_submit' => now(),
        ]);

        $component = Livewire::withQueryParams(['kategori' => 'penelitian', 'tahun' => $year])
            ->test(MonitoringPelaksanaan::class);

        $component->call('downloadExcel')
            ->assertFileDownloaded("monitoring-pelaksanaan-{$year}.csv");

        $rekap = $component->instance()->getRekapProperty();

        $this->assertCount(1, $rekap, 'hanya skema penelitian pada tahun berjalan');

        $row = $rekap->firstWhere('skema', 'Skema Uji');
        $this->assertSame(2, $row['didanai']);
        $this->assertSame(2, $row['revisi_sudah']);        // factory status() mengisi file_proposal
        $this->assertSame(1, $row['kemajuan_sudah']);
        $this->assertSame(1, $row['kemajuan_belum']);
        $this->assertSame(0, $row['akhir_sudah']);
        $this->assertSame(2, $row['akhir_belum']);
    }

    public function test_monitoring_pelaksanaan_detail_lists_proposals_of_the_scheme(): void
    {
        $this->actAs($this->user('admin_lppm'));

        $year = (int) now()->year;
        $scheme = ProposalScheme::factory()->penelitian()->create(['nama_skema' => 'Skema Detail']);
        $p = Proposal::factory()->forScheme($scheme)->status(ProposalStatus::InProgress)->create(['tahun_anggaran' => $year]);

        Livewire::withQueryParams(['skema' => $scheme->id, 'tahun' => $year])
            ->test(MonitoringPelaksanaanDetail::class)
            ->assertOk()
            ->assertSee('Skema Detail')
            ->assertSee($p->judul);
    }

    public function test_monitoring_pelaksanaan_detail_requires_scheme_and_oversight(): void
    {
        $scheme = ProposalScheme::factory()->create();

        $this->actAs($this->user('dosen'));
        $this->get(MonitoringPelaksanaanDetail::getUrl(['skema' => $scheme->id]))->assertForbidden();

        $this->actAs($this->user('admin_lppm'));
        $this->get('/admin/monitoring-pelaksanaan-detail')->assertNotFound();
    }

    public function test_catatan_harian_recap_sums_notes_per_scheme(): void
    {
        $this->actAs($this->user('admin_lppm'));

        $year = (int) now()->year;
        $scheme = ProposalScheme::factory()->penelitian()->create(['nama_skema' => 'Skema CH']);
        $abai = ProposalScheme::factory()->penelitian()->create(['nama_skema' => 'Skema Lain']);

        $p1 = Proposal::factory()->forScheme($scheme)->status(ProposalStatus::InProgress)->create(['tahun_anggaran' => $year]);
        $p2 = Proposal::factory()->forScheme($scheme)->status(ProposalStatus::Funded)->create(['tahun_anggaran' => $year]);
        Proposal::factory()->forScheme($abai)->status(ProposalStatus::Funded)->create(['tahun_anggaran' => $year]);

        CatatanHarian::create(['proposal_id' => $p1->id, 'tanggal' => now()->subDay(), 'kegiatan' => 'a', 'persentase' => 40]);
        CatatanHarian::create(['proposal_id' => $p1->id, 'tanggal' => now(), 'kegiatan' => 'b', 'persentase' => 80]);
        CatatanHarian::create(['proposal_id' => $p2->id, 'tanggal' => now(), 'kegiatan' => 'c', 'persentase' => 10]);

        $rekap = Livewire::withQueryParams(['kategori' => 'penelitian', 'tahun' => $year])
            ->test(MonitoringCatatanHarian::class)
            ->instance()
            ->getRekapProperty();

        $this->assertSame(3, $rekap->firstWhere('skema', 'Skema CH')['jumlah']);
        $this->assertSame(0, $rekap->firstWhere('skema', 'Skema Lain')['jumlah']);

        // Kotak "Cari Nama..." menyaring nama skema.
        $filtered = Livewire::withQueryParams(['kategori' => 'penelitian', 'tahun' => $year, 'cari' => 'CH'])
            ->test(MonitoringCatatanHarian::class)
            ->instance()
            ->getRekapProperty();

        $this->assertSame(['Skema CH'], $filtered->pluck('skema')->all());
    }

    public function test_catatan_harian_detail_and_log_drilldown(): void
    {
        $this->actAs($this->user('admin_lppm'));

        $year = (int) now()->year;
        $scheme = ProposalScheme::factory()->penelitian()->create(['nama_skema' => 'Skema Drill']);
        $p = Proposal::factory()->forScheme($scheme)->status(ProposalStatus::InProgress)->create(['tahun_anggaran' => $year]);

        CatatanHarian::create([
            'proposal_id' => $p->id, 'tanggal' => now(),
            'kegiatan' => 'uji coba alat ke responden', 'persentase' => 80,
            'berkas' => 'logbook/substansi-laporan-abc123.pdf',
        ]);

        $rows = Livewire::withQueryParams(['skema' => $scheme->id, 'tahun' => $year])
            ->test(MonitoringCatatanHarianDetail::class)
            ->assertOk()
            ->assertSee('Skema Drill')
            ->assertSee($p->judul)
            ->instance()
            ->getRowsProperty();

        $this->assertSame(1, $rows->first()['jumlah_catatan']);
        $this->assertSame(80, $rows->first()['persentase']);

        Livewire::withQueryParams(['usulan' => $p->id, 'skema' => $scheme->id, 'tahun' => $year])
            ->test(MonitoringCatatanHarianLog::class)
            ->assertOk()
            ->assertSee('Data Kegiatan Penelitian')
            ->assertSee('uji coba alat ke responden')
            ->assertSee('80 %')
            ->assertSee('Berkas Pendukung')
            ->assertSee('substansi-laporan-abc123.pdf');
    }

    public function test_catatan_harian_log_requires_usulan(): void
    {
        $this->actAs($this->user('admin_lppm'));
        $this->get('/admin/monitoring-catatan-harian-log')->assertNotFound();
    }
}
