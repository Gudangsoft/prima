<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Enums\ProposalStatus;
use App\Enums\ReportType;
use App\Models\MonitoringReport;
use App\Models\Proposal;
use Filament\Widgets\Widget;

/**
 * Kartu biru "Monitoring Pelaksanaan Kegiatan" (gaya BIMA V2): tiga sub-kartu
 * putih berisi progres Belum Submit vs Sudah Submit.
 */
class MonitoringPelaksanaanCard extends Widget
{
    protected static string $view = 'filament.widgets.monitoring-pelaksanaan-card';

    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = [
        'default' => 'full',
        'lg' => 2,
    ];

    public static function canView(): bool
    {
        return auth()->user()?->hasAnyRole(['admin_lppm', 'pimpinan', 'super_admin']) ?? false;
    }

    private const BERJALAN = [
        ProposalStatus::Funded->value,
        ProposalStatus::InProgress->value,
        ProposalStatus::Reported->value,
        ProposalStatus::OutputValidated->value,
    ];

    public function getViewData(): array
    {
        $berjalanIds = Proposal::query()->whereIn('status', self::BERJALAN)->pluck('id');
        $total = $berjalanIds->count();

        $withReport = fn (ReportType $j): int => MonitoringReport::query()
            ->whereIn('proposal_id', $berjalanIds)
            ->where('jenis', $j->value)
            ->distinct('proposal_id')
            ->count('proposal_id');

        $kemajuan = $withReport(ReportType::Kemajuan);
        $akhir = $withReport(ReportType::Akhir);

        $luaranSudah = Proposal::query()->where('status', ProposalStatus::OutputValidated->value)->count();
        $luaranBelum = Proposal::query()->where('status', ProposalStatus::Reported->value)->count();

        return [
            'cards' => [
                [
                    'icon' => 'heroicon-o-arrow-path',
                    'tint' => 'amber',
                    'title' => 'Laporan Kemajuan',
                    'belum' => max(0, $total - $kemajuan),
                    'sudah' => $kemajuan,
                ],
                [
                    'icon' => 'heroicon-o-presentation-chart-line',
                    'tint' => 'blue',
                    'title' => 'Laporan Akhir',
                    'belum' => max(0, $total - $akhir),
                    'sudah' => $akhir,
                ],
                [
                    'icon' => 'heroicon-o-flag',
                    'tint' => 'green',
                    'title' => 'Validasi Luaran',
                    'belum' => $luaranBelum,
                    'sudah' => $luaranSudah,
                ],
            ],
        ];
    }
}
