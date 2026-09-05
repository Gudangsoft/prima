<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Enums\Kategori;
use App\Enums\ReportType;
use App\Filament\Resources\ProposalResource;
use App\Models\Proposal;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Livewire\Attributes\Url;

/**
 * Halaman kegiatan dosen bergaya BIMA: satu halaman per kategori (Penelitian /
 * Pengabdian) dengan bilah tab — Usulan, Bimbingan Teknis, Perbaikan Usulan,
 * Catatan Harian, Laporan Kemajuan, Laporan Akhir, Pengkinian Capaian Luaran.
 * Penelitian & pengabdian tidak pernah tercampur (kategori dikunci di query).
 */
class Kegiatan extends Page
{
    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $slug = 'kegiatan';

    protected static string $view = 'filament.pages.kegiatan';

    #[Url]
    public string $kategori = 'penelitian';

    #[Url]
    public string $tab = 'usulan';

    #[Url]
    public ?int $tahun = null;

    public const TABS = [
        'usulan' => 'Usulan',
        'bimtek' => 'Bimbingan Teknis',
        'perbaikan' => 'Perbaikan Usulan',
        'catatan' => 'Catatan Harian',
        'kemajuan' => 'Laporan Kemajuan',
        'akhir' => 'Laporan Akhir',
        'luaran' => 'Pengkinian Capaian Luaran',
    ];

    private const BERJALAN = MonitoringPelaksanaan::BERJALAN;

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return $user !== null
            && $user->hasRole('dosen')
            && ! $user->hasAnyRole(['admin_lppm', 'pimpinan', 'super_admin']);
    }

    public static function urlFor(string $kategori, string $tab = 'usulan'): string
    {
        return static::getUrl(['kategori' => $kategori, 'tab' => $tab]);
    }

    public function mount(): void
    {
        if (! array_key_exists($this->kategori, Kategori::options())) {
            $this->kategori = 'penelitian';
        }

        if (! array_key_exists($this->tab, self::TABS)) {
            $this->tab = 'usulan';
        }
    }

    public function getTitle(): string
    {
        return $this->kategori === 'pengabdian' ? 'Pengabdian' : 'Penelitian';
    }

    public function selectTab(string $tab): void
    {
        $this->tab = array_key_exists($tab, self::TABS) ? $tab : 'usulan';
    }

    private function katLabel(): string
    {
        return $this->kategori === 'pengabdian' ? 'Pengabdian' : 'Penelitian';
    }

    public function getSectionHeading(): string
    {
        return match ($this->tab) {
            'bimtek' => 'BIMTEK '.Str::upper($this->katLabel()),
            'perbaikan' => 'Daftar Usulan '.$this->katLabel().' didanai',
            'luaran' => 'Pengkinian Capaian Luaran '.Str::upper($this->katLabel()),
            default => self::TABS[$this->tab].' '.$this->katLabel(),
        };
    }

    /** @return array<int, int|string> */
    public function getTahunOptions(): array
    {
        return Proposal::query()
            ->where('user_id', auth()->id())
            ->distinct()
            ->orderByDesc('tahun_anggaran')
            ->pluck('tahun_anggaran')
            ->all();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('baru')
                ->label('Ajukan Usulan Baru')
                ->icon('heroicon-m-plus')
                ->visible(fn (): bool => $this->tab === 'usulan'
                    && auth()->user()->can('create', ProposalResource::getModel()))
                ->url(ProposalResource::getUrl('create', ['kat' => $this->kategori])),
        ];
    }

    /** @return array<int, string> */
    public function getColumnsProperty(): array
    {
        return match ($this->tab) {
            'usulan' => ['Ketua', 'Judul', 'Skema', 'Tahun', 'Peran', 'Status', 'Aksi'],
            'bimtek' => ['Ketua', 'Judul', 'Skema', 'Tahun', 'Status Bimtek', 'Aksi'],
            'perbaikan' => ['Skema', 'Judul', 'Tahun', 'Pendanaan', 'Dokumen', 'Status', 'Aksi'],
            'catatan' => ['Skema', 'Tahun', 'Judul', 'Keterangan', 'Aksi'],
            'kemajuan', 'akhir' => ['Program', 'Judul', 'Berkas', 'Aksi'],
            'luaran' => ['Program', 'Judul', 'Aktual', 'Aksi'],
            default => [],
        };
    }

    /**
     * @return Collection<int, array<int, array<string, mixed>>>
     */
    public function getRowsProperty(): Collection
    {
        if ($this->tab === 'bimtek') {
            return collect();
        }

        $me = auth()->id();

        $q = Proposal::query()
            ->whereHas('scheme', fn (Builder $s) => $s->where('kategori', $this->kategori))
            ->when($this->tahun, fn (Builder $b) => $b->where('tahun_anggaran', $this->tahun))
            ->with('scheme:id,nama_skema')
            ->orderByDesc('updated_at');

        // Tab "Usulan" & "Perbaikan Usulan": termasuk usulan yang mengikutsertakan
        // dosen ini sebagai anggota. Tab pelaksanaan lain: hanya milik sendiri.
        if (in_array($this->tab, ['usulan', 'perbaikan'], true)) {
            $q->where(fn (Builder $w) => $w
                ->where('user_id', $me)
                ->orWhereHas('members', fn (Builder $m) => $m->where('user_id', $me)));
        } else {
            $q->where('user_id', $me);
        }

        return match ($this->tab) {
            'usulan' => $this->rowsUsulan($q),
            'perbaikan' => $this->rowsPerbaikan($q),
            'catatan' => $this->rowsCatatan($q),
            'kemajuan' => $this->rowsLaporan($q, ReportType::Kemajuan),
            'akhir' => $this->rowsLaporan($q, ReportType::Akhir),
            'luaran' => $this->rowsLuaran($q),
            default => collect(),
        };
    }

    private function view(Proposal $p): string
    {
        return ProposalResource::getUrl('view', ['record' => $p]);
    }

    private function c(string $kind, mixed $value = null, array $extra = []): array
    {
        return array_merge(['kind' => $kind, 'value' => $value], $extra);
    }

    private function rowsUsulan(Builder $q): Collection
    {
        $me = auth()->id();

        return $q->with('submitter:id,name,nidn')->get()->map(fn (Proposal $p): array => [
            $this->c('text', Str::upper((string) $p->submitter?->name).($p->submitter?->nidn ? ' ('.$p->submitter->nidn.')' : '')),
            $this->c('text', $p->judul, ['strong' => true]),
            $this->c('text', $p->scheme?->nama_skema ?? '—', ['color' => 'primary']),
            $this->c('text', (string) $p->tahun_anggaran),
            $this->c('text', $p->user_id === $me ? 'Ketua' : 'Anggota'),
            $this->c('badge', $p->status->label(), ['color' => $p->status->color()]),
            $this->c('button', 'Detail', ['url' => $this->view($p)]),
        ]);
    }

    private function rowsPerbaikan(Builder $q): Collection
    {
        return $q->whereHas('fundingDecision', fn (Builder $f) => $f->where('status_danai', '!=', 'tidak_didanai'))
            ->with('fundingDecision')
            ->get()
            ->map(fn (Proposal $p): array => [
                $this->c('text', $p->scheme?->nama_skema ?? '—'),
                $this->c('text', $p->judul, ['strong' => true]),
                $this->c('text', (string) $p->tahun_anggaran),
                $this->c('money', (float) ($p->fundingDecision?->jumlah_dana ?? 0)),
                $this->c('download', 'Dokumen', ['url' => $p->file_proposal ? route('download.proposal', $p) : null]),
                $this->c('badge', $p->status->label(), ['color' => $p->status->color()]),
                $this->c('button', 'Detail', ['url' => $this->view($p)]),
            ]);
    }

    private function rowsCatatan(Builder $q): Collection
    {
        return $q->whereIn('status', self::BERJALAN)
            ->withCount('catatanHarian')
            ->withMax('catatanHarian', 'persentase')
            ->get()
            ->map(fn (Proposal $p): array => [
                $this->c('text', $p->scheme?->nama_skema ?? '—'),
                $this->c('text', (string) $p->tahun_anggaran),
                $this->c('text', $p->judul, ['strong' => true]),
                $this->c('multiline', [
                    'Jumlah Catatan : '.(int) $p->catatan_harian_count,
                    'Persentase Capaian : '.(int) ($p->catatan_harian_max_persentase ?? 0).' %',
                ]),
                $this->c('button', 'Detail', ['url' => $this->view($p)]),
            ]);
    }

    private function rowsLaporan(Builder $q, ReportType $jenis): Collection
    {
        return $q->whereIn('status', self::BERJALAN)
            ->with('monitoringReports')
            ->get()
            ->map(function (Proposal $p) use ($jenis): array {
                $rep = $p->monitoringReports->firstWhere('jenis', $jenis);

                return [
                    $this->c('multiline', [
                        $p->scheme?->nama_skema ?? '—',
                        'Tahun Pelaksanaan : '.$p->tahun_anggaran,
                    ], ['badge' => $rep ? 'Sudah Unggah' : 'Belum Unggah', 'badgeColor' => $rep ? 'success' : 'gray']),
                    $this->c('text', $p->judul),
                    $this->c('download', 'Unduh', ['url' => $rep ? route('download.report', $rep) : null]),
                    $this->c('button', 'Ubah', ['url' => $this->view($p)]),
                ];
            });
    }

    private function rowsLuaran(Builder $q): Collection
    {
        return $q->whereIn('status', self::BERJALAN)
            ->withCount('outputs')
            ->get()
            ->map(fn (Proposal $p): array => [
                $this->c('multiline', [
                    $p->scheme?->nama_skema ?? '—',
                    'Tahun Pelaksanaan : '.$p->tahun_anggaran,
                ]),
                $this->c('text', $p->judul),
                $this->c('text', $p->outputs_count > 0 ? $p->outputs_count.' luaran' : '–'),
                $this->c('button', 'Ubah', ['url' => $this->view($p)]),
            ]);
    }
}
