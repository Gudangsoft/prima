<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Enums\ProposalStatus;
use App\Models\Proposal;
use Filament\Widgets\ChartWidget;

/**
 * Distribusi usulan menurut status (donat berwarna, gaya BIMA V2).
 */
class ProposalsByStatusChart extends ChartWidget
{
    protected static ?string $heading = 'Distribusi Status Usulan';

    protected static ?int $sort = 5;

    protected static ?string $maxHeight = '280px';

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return auth()->user()?->isActingAs(['admin_lppm', 'pimpinan', 'super_admin']) ?? false;
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    /** Warna token Filament -> hex untuk Chart.js. */
    private const HEX = [
        'gray' => '#64748b',
        'info' => '#0C8FA5',
        'primary' => '#0C8FA5',
        'warning' => '#f59e0b',
        'success' => '#16a34a',
        'danger' => '#dc2626',
    ];

    protected function getData(): array
    {
        $counts = Proposal::query()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $labels = [];
        $data = [];
        $colors = [];

        foreach (ProposalStatus::cases() as $status) {
            $n = (int) ($counts[$status->value] ?? 0);
            if ($n === 0) {
                continue;
            }
            $labels[] = $status->label();
            $data[] = $n;
            $colors[] = self::HEX[$status->color()] ?? '#94a3b8';
        }

        return [
            'datasets' => [[
                'label' => 'Usulan',
                'data' => $data,
                'backgroundColor' => $colors,
                'borderColor' => '#ffffff',
                'borderWidth' => 2,
            ]],
            'labels' => $labels,
        ];
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'position' => 'right',
                    'labels' => ['boxWidth' => 12, 'padding' => 14],
                ],
            ],
            'cutout' => '58%',
        ];
    }
}
