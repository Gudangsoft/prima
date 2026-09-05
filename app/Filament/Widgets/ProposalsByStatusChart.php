<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Enums\ProposalStatus;
use App\Models\Proposal;
use Filament\Widgets\ChartWidget;

/**
 * Rekap jumlah usulan per status.
 */
class ProposalsByStatusChart extends ChartWidget
{
    protected static ?string $heading = 'Usulan per Status';

    protected static ?int $sort = 2;

    public static function canView(): bool
    {
        return auth()->user()?->hasAnyRole(['admin_lppm', 'pimpinan', 'super_admin']) ?? false;
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $counts = Proposal::query()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $labels = [];
        $data = [];

        foreach (ProposalStatus::cases() as $status) {
            $labels[] = $status->label();
            $data[] = (int) ($counts[$status->value] ?? 0);
        }

        return [
            'datasets' => [[
                'label' => 'Jumlah usulan',
                'data' => $data,
                'backgroundColor' => '#3b82f6',
            ]],
            'labels' => $labels,
        ];
    }
}
