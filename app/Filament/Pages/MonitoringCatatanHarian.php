<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Enums\Kategori;
use App\Enums\ProposalStatus;
use App\Models\Proposal;
use App\Support\Settings;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Livewire\Attributes\Url;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * "Monitoring Catatan Harian" (grup Monitoring) — gaya BIMA.
 *
 * Level 1: rekap jumlah catatan harian per skema untuk usulan yang berjalan.
 * Tombol Detail membuka daftar usulan skema itu (MonitoringCatatanHarianDetail),
 * lalu logbook tiap usulan (MonitoringCatatanHarianLog).
 */
class MonitoringCatatanHarian extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationGroup = 'Monitoring';

    protected static ?string $navigationLabel = 'Monitoring Catatan Harian';

    protected static ?int $navigationSort = 30;

    protected static ?string $title = 'Monitoring Catatan Harian';

    protected static string $view = 'filament.pages.monitoring-catatan-harian';

    #[Url]
    public string $kategori = 'penelitian';

    #[Url]
    public ?int $tahun = null;

    #[Url]
    public string $cari = '';

    public const BERJALAN = [
        ProposalStatus::Funded->value,
        ProposalStatus::InProgress->value,
        ProposalStatus::Reported->value,
        ProposalStatus::OutputValidated->value,
    ];

    public static function canAccess(): bool
    {
        return auth()->user()?->isActingAs(['admin_lppm', 'pimpinan', 'super_admin']) ?? false;
    }

    public function mount(): void
    {
        $this->tahun ??= (int) now()->year;

        if (! array_key_exists($this->kategori, Kategori::options())) {
            $this->kategori = 'penelitian';
        }
    }

    /** @return array<string, string> */
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

    public function getNamaPtProperty(): string
    {
        return Settings::institution($this->kategori)['nama'] ?: config('app.name');
    }

    /**
     * Satu baris rekap per skema: jumlah total catatan harian dari usulan berjalan.
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
            ->withCount('catatanHarian')
            ->get(['id', 'scheme_id']);

        return $proposals
            ->groupBy('scheme_id')
            ->map(function (Collection $grup): array {
                $scheme = $grup->first()->scheme;

                return [
                    'scheme_id' => $scheme->getKey(),
                    'skema' => $scheme->nama_skema,
                    'jumlah' => (int) $grup->sum('catatan_harian_count'),
                    'url' => MonitoringCatatanHarianDetail::urlFor($scheme->getKey(), $this->kategori, $this->tahun),
                ];
            })
            ->when(
                $this->cari !== '',
                fn (Collection $rows) => $rows->filter(
                    fn (array $r) => Str::contains(Str::lower($r['skema']), Str::lower($this->cari)),
                ),
            )
            ->sortBy('skema')
            ->values();
    }

    public function downloadExcel(): StreamedResponse
    {
        $rows = $this->rekap;
        $tahun = $this->tahun;

        return response()->streamDownload(function () use ($rows): void {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['No', 'Nama Skema', 'Jumlah Catatan']);

            foreach ($rows as $i => $r) {
                fputcsv($out, [$i + 1, $r['skema'], $r['jumlah']]);
            }

            fclose($out);
        }, "catatan-harian-{$tahun}.csv", ['Content-Type' => 'text/csv']);
    }
}
