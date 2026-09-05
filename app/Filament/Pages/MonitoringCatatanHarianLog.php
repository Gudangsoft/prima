<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Models\Proposal;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Livewire\Attributes\Url;

/**
 * Level 3 "Monitoring Catatan Harian": logbook (entri harian) satu usulan —
 * tanggal, kegiatan, berkas pendukung, dan persentase capaian kumulatif.
 */
class MonitoringCatatanHarianLog extends Page
{
    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $slug = 'monitoring-catatan-harian-log';

    protected static ?string $title = 'Monitoring Catatan Harian';

    protected static string $view = 'filament.pages.monitoring-catatan-harian-log';

    #[Url]
    public ?int $usulan = null;

    #[Url]
    public ?int $skema = null;

    #[Url]
    public string $kategori = 'penelitian';

    #[Url]
    public ?int $tahun = null;

    public ?Proposal $proposal = null;

    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyRole(['admin_lppm', 'pimpinan', 'super_admin']) ?? false;
    }

    public static function urlFor(int $usulanId, int $schemeId, string $kategori, int $tahun): string
    {
        return static::getUrl([
            'usulan' => $usulanId,
            'skema' => $schemeId,
            'kategori' => $kategori,
            'tahun' => $tahun,
        ]);
    }

    public function mount(): void
    {
        abort_if($this->usulan === null, 404);

        $this->proposal = Proposal::query()->with('scheme:id,nama_skema,kategori')->findOrFail($this->usulan);
        $this->tahun ??= (int) now()->year;
    }

    public function getHeadingLabel(): string
    {
        return 'Data Kegiatan '.Str::title($this->proposal->scheme?->kategori?->value ?? 'penelitian');
    }

    protected function getHeaderActions(): array
    {
        $backUrl = $this->skema !== null
            ? MonitoringCatatanHarianDetail::urlFor($this->skema, $this->kategori, (int) $this->tahun)
            : MonitoringCatatanHarian::getUrl();

        return [
            Action::make('kembali')
                ->label('Kembali')
                ->icon('heroicon-m-arrow-left')
                ->color('primary')
                ->url($backUrl),
        ];
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function getEntriesProperty(): Collection
    {
        return $this->proposal->catatanHarian()
            ->reorder('tanggal', 'asc')
            ->orderBy('id')
            ->get()
            ->map(fn ($c): array => [
                'tanggal' => $c->tanggal,
                'kegiatan' => $c->kegiatan,
                'persentase' => $c->persentase,
                // Skema menyimpan satu berkas per entri; tetap sebagai daftar
                // agar tampilannya konsisten dengan BIMA (nomor + nama file).
                'berkas' => $c->berkas
                    ? [['name' => basename((string) $c->berkas), 'url' => $c->berkasUrl()]]
                    : [],
            ]);
    }
}
