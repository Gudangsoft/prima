<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Enums\Kategori;
use App\Enums\ReportType;
use App\Filament\Resources\ProposalResource;
use App\Models\Proposal;
use App\Models\ProposalScheme;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Livewire\Attributes\Url;

/**
 * Rincian "Monitoring Pelaksanaan" per usulan untuk satu skema — tujuan tombol
 * Detail pada rekap MonitoringPelaksanaan. Menampilkan status unggah Revisi
 * Proposal / Laporan Kemajuan / Laporan Akhir tiap usulan beserta tautan berkas.
 */
class MonitoringPelaksanaanDetail extends Page
{
    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $slug = 'monitoring-pelaksanaan-detail';

    protected static ?string $title = 'Monitoring Pelaksanaan';

    protected static string $view = 'filament.pages.monitoring-pelaksanaan-detail';

    #[Url]
    public ?int $skema = null;

    #[Url]
    public string $kategori = 'penelitian';

    #[Url]
    public ?int $tahun = null;

    public ?ProposalScheme $scheme = null;

    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyRole(['admin_lppm', 'pimpinan', 'super_admin']) ?? false;
    }

    public static function urlFor(int $schemeId, string $kategori, int $tahun): string
    {
        return static::getUrl([
            'skema' => $schemeId,
            'kategori' => $kategori,
            'tahun' => $tahun,
        ]);
    }

    public function mount(): void
    {
        abort_if($this->skema === null, 404);

        $this->scheme = ProposalScheme::query()->findOrFail($this->skema);
        $this->tahun ??= (int) now()->year;

        if (! array_key_exists($this->kategori, Kategori::options())) {
            $this->kategori = 'penelitian';
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('kembali')
                ->label('Kembali')
                ->icon('heroicon-m-arrow-left')
                ->color('primary')
                ->url(MonitoringPelaksanaan::getUrl([
                    'kategori' => $this->kategori,
                    'tahun' => $this->tahun,
                ])),
        ];
    }

    /**
     * Satu baris per usulan berjalan pada skema ini.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function getRowsProperty(): Collection
    {
        return Proposal::query()
            ->where('scheme_id', $this->skema)
            ->where('tahun_anggaran', $this->tahun)
            ->whereIn('status', MonitoringPelaksanaan::BERJALAN)
            ->with(['submitter:id,name,nidn', 'monitoringReports'])
            ->orderBy('id')
            ->get()
            ->map(function (Proposal $p): array {
                $kemajuan = $p->monitoringReports->firstWhere('jenis', ReportType::Kemajuan);
                $akhir = $p->monitoringReports->firstWhere('jenis', ReportType::Akhir);

                return [
                    'pengusul' => $p->submitter?->name ?? '—',
                    'nidn' => $p->submitter?->nidn,
                    'judul' => $p->judul,
                    'detail_url' => ProposalResource::getUrl('view', ['record' => $p]),
                    'revisi_ada' => $p->file_proposal !== null,
                    'revisi_url' => $p->file_proposal !== null ? route('download.proposal', $p) : null,
                    'kemajuan_ada' => $kemajuan !== null,
                    'kemajuan_url' => $kemajuan !== null ? route('download.report', $kemajuan) : null,
                    'akhir_ada' => $akhir !== null,
                    'akhir_url' => $akhir !== null ? route('download.report', $akhir) : null,
                ];
            });
    }
}
