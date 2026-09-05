<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Enums\Kategori;
use App\Enums\ProposalStatus;
use App\Enums\ReportType;
use App\Models\Proposal;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Livewire\Attributes\Url;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * "Monitoring Pelaksanaan Kegiatan" (grup Monitoring) — gaya BIMA.
 *
 * Rekap per skema untuk usulan yang sedang berjalan: jumlah didanai/dibatalkan
 * dan progres unggah Revisi Proposal, Laporan Kemajuan, dan Laporan Akhir
 * (masing-masing dipecah "sudah" vs "belum" unggah). Tombol Detail membuka
 * rincian per usulan di halaman MonitoringPelaksanaanDetail.
 */
class MonitoringPelaksanaan extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-presentation-chart-line';

    protected static ?string $navigationGroup = 'Monitoring';

    protected static ?string $navigationLabel = 'Monitoring Pelaksanaan Kegiatan';

    protected static ?int $navigationSort = 20;

    protected static ?string $title = 'Monitoring Pelaksanaan';

    protected static string $view = 'filament.pages.monitoring-pelaksanaan';

    #[Url]
    public string $kategori = 'penelitian';

    #[Url]
    public ?int $tahun = null;

    /** Status yang dihitung sebagai "sedang berjalan". */
    public const BERJALAN = [
        ProposalStatus::Funded->value,
        ProposalStatus::InProgress->value,
        ProposalStatus::Reported->value,
        ProposalStatus::OutputValidated->value,
    ];

    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyRole(['admin_lppm', 'pimpinan', 'super_admin']) ?? false;
    }

    public function mount(): void
    {
        $this->tahun ??= (int) now()->year;

        if (! array_key_exists($this->kategori, Kategori::options())) {
            $this->kategori = 'penelitian';
        }
    }

    /** @return array<int, string> */
    public function getKategoriOptions(): array
    {
        return Kategori::options();
    }

    /** @return array<int, int> */
    public function getTahunOptions(): array
    {
        return Proposal::query()
            ->distinct()
            ->orderByDesc('tahun_anggaran')
            ->pluck('tahun_anggaran')
            ->push((int) now()->year)
            ->unique()
            ->sortDesc()
            ->values()
            ->all();
    }

    /**
     * Satu baris rekap per skema yang punya usulan berjalan pada filter aktif.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function getRekapProperty(): Collection
    {
        $proposals = Proposal::query()
            ->whereIn('status', self::BERJALAN)
            ->where('tahun_anggaran', $this->tahun)
            ->whereHas('scheme', fn ($q) => $q->where('kategori', $this->kategori))
            ->with('scheme:id,nama_skema')
            ->withCount([
                'monitoringReports as kemajuan_count' => fn ($q) => $q->where('jenis', ReportType::Kemajuan->value),
                'monitoringReports as akhir_count' => fn ($q) => $q->where('jenis', ReportType::Akhir->value),
            ])
            ->get(['id', 'scheme_id', 'status', 'file_proposal']);

        return $proposals
            ->groupBy('scheme_id')
            ->map(function (Collection $grup): array {
                $total = $grup->count();
                $revisiSudah = $grup->whereNotNull('file_proposal')->count();
                $kemajuanSudah = $grup->where('kemajuan_count', '>', 0)->count();
                $akhirSudah = $grup->where('akhir_count', '>', 0)->count();
                $scheme = $grup->first()->scheme;

                return [
                    'scheme_id' => $scheme->getKey(),
                    'skema' => $scheme->nama_skema,
                    'didanai' => $total,
                    'dibatalkan' => 0,
                    'revisi_sudah' => $revisiSudah,
                    'revisi_belum' => $total - $revisiSudah,
                    'kemajuan_sudah' => $kemajuanSudah,
                    'kemajuan_belum' => $total - $kemajuanSudah,
                    'akhir_sudah' => $akhirSudah,
                    'akhir_belum' => $total - $akhirSudah,
                    'url' => MonitoringPelaksanaanDetail::urlFor($scheme->getKey(), $this->kategori, $this->tahun),
                ];
            })
            ->sortBy('skema')
            ->values();
    }

    public function downloadExcel(): StreamedResponse
    {
        $rows = $this->rekap;
        $tahun = $this->tahun;

        return response()->streamDownload(function () use ($rows): void {
            $out = fopen('php://output', 'w');
            fputcsv($out, [
                'No', 'Skema', 'Didanai', 'Dibatalkan',
                'Revisi Proposal - Sudah', 'Revisi Proposal - Belum',
                'Laporan Kemajuan - Sudah', 'Laporan Kemajuan - Belum',
                'Laporan Akhir - Sudah', 'Laporan Akhir - Belum',
            ]);

            foreach ($rows as $i => $r) {
                fputcsv($out, [
                    $i + 1, $r['skema'], $r['didanai'], $r['dibatalkan'],
                    $r['revisi_sudah'], $r['revisi_belum'],
                    $r['kemajuan_sudah'], $r['kemajuan_belum'],
                    $r['akhir_sudah'], $r['akhir_belum'],
                ]);
            }

            fclose($out);
        }, "monitoring-pelaksanaan-{$tahun}.csv", ['Content-Type' => 'text/csv']);
    }
}
