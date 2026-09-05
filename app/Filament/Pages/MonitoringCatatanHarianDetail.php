<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Enums\Kategori;
use App\Models\Proposal;
use App\Models\ProposalScheme;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Livewire\Attributes\Url;

/**
 * Level 2 "Monitoring Catatan Harian": daftar usulan berjalan pada satu skema
 * beserta jumlah catatan & persentase capaian terakhir. Tombol Detail membuka
 * logbook usulan (MonitoringCatatanHarianLog).
 */
class MonitoringCatatanHarianDetail extends Page
{
    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $slug = 'monitoring-catatan-harian-detail';

    protected static ?string $title = 'Monitoring Catatan Harian';

    protected static string $view = 'filament.pages.monitoring-catatan-harian-detail';

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
        return static::getUrl(['skema' => $schemeId, 'kategori' => $kategori, 'tahun' => $tahun]);
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
                ->url(MonitoringCatatanHarian::getUrl(['kategori' => $this->kategori, 'tahun' => $this->tahun])),
        ];
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function getRowsProperty(): Collection
    {
        return Proposal::query()
            ->where('scheme_id', $this->skema)
            ->where('tahun_anggaran', $this->tahun)
            ->whereIn('status', MonitoringCatatanHarian::BERJALAN)
            ->with(['submitter:id,name', 'fundingDecision:id,proposal_id,jumlah_dana'])
            ->withCount('catatanHarian')
            ->withMax('catatanHarian', 'persentase')
            ->orderBy('id')
            ->get()
            ->map(fn (Proposal $p): array => [
                'pengusul' => $p->submitter?->name ?? '—',
                'judul' => $p->judul,
                'dana' => $p->fundingDecision?->jumlah_dana,
                'jumlah_catatan' => (int) $p->catatan_harian_count,
                'persentase' => (int) ($p->catatan_harian_max_persentase ?? 0),
                'detail_url' => MonitoringCatatanHarianLog::urlFor($p->getKey(), $this->skema, $this->kategori, $this->tahun),
            ]);
    }
}
