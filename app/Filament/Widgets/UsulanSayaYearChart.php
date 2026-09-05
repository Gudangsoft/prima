<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Enums\ProposalStatus;
use App\Filament\Widgets\Concerns\ForDosen;
use App\Models\Proposal;
use Filament\Widgets\ChartWidget;

/**
 * Grafik garis "Usulan vs Didanai per tahun" untuk usulan milik dosen (gaya BIMA).
 */
class UsulanSayaYearChart extends ChartWidget
{
    use ForDosen;

    protected static ?string $heading = 'Usulan Saya per Tahun';

    protected static ?int $sort = 2;

    protected static ?string $maxHeight = '260px';

    protected int|string|array $columnSpan = [
        'default' => 1,
        'lg' => 2,
    ];

    private const DIDANAI = [
        ProposalStatus::Funded->value,
        ProposalStatus::InProgress->value,
        ProposalStatus::Reported->value,
        ProposalStatus::OutputValidated->value,
    ];

    protected function getType(): string
    {
        return 'line';
    }

    protected function getData(): array
    {
        $didanai = "'".implode("','", self::DIDANAI)."'";

        $rows = Proposal::query()
            ->where('user_id', auth()->id())
            ->selectRaw('tahun_anggaran as tahun, COUNT(*) as usulan, '
                ."SUM(CASE WHEN status IN ({$didanai}) THEN 1 ELSE 0 END) as didanai")
            ->groupBy('tahun_anggaran')
            ->orderBy('tahun_anggaran')
            ->get();

        if ($rows->isEmpty()) {
            $rows = collect([(object) ['tahun' => (int) now()->year, 'usulan' => 0, 'didanai' => 0]]);
        }

        $years = $rows->pluck('tahun')->map(fn ($y): string => (string) $y)->all();

        return [
            'datasets' => [
                [
                    'label' => 'Usulan',
                    'data' => $rows->pluck('usulan')->map(fn ($v): int => (int) $v)->all(),
                    'borderColor' => '#6366f1',
                    'backgroundColor' => '#6366f1',
                    'tension' => 0.4,
                    'fill' => false,
                ],
                [
                    'label' => 'Didanai',
                    'data' => $rows->pluck('didanai')->map(fn ($v): int => (int) $v)->all(),
                    'borderColor' => '#d946ef',
                    'backgroundColor' => '#d946ef',
                    'tension' => 0.4,
                    'fill' => false,
                ],
            ],
            'labels' => $years,
        ];
    }
}
