<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Proposal;
use Filament\Widgets\ChartWidget;

/**
 * Rekap jumlah usulan per skema.
 */
class ProposalsBySchemeChart extends ChartWidget
{
    protected static ?string $heading = 'Usulan per Skema';

    protected static ?int $sort = 6;

    protected static ?string $maxHeight = '280px';

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return auth()->user()?->isActingAs(['admin_lppm', 'pimpinan', 'super_admin']) ?? false;
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $rows = Proposal::query()
            ->join('proposal_schemes', 'proposal_schemes.id', '=', 'proposals.scheme_id')
            ->selectRaw('proposal_schemes.nama_skema as nama, COUNT(*) as total')
            ->groupBy('proposal_schemes.nama_skema')
            ->orderByDesc('total')
            ->pluck('total', 'nama');

        return [
            'datasets' => [[
                'label' => 'Jumlah usulan',
                'data' => $rows->values()->all(),
                'backgroundColor' => '#22c55e',
            ]],
            'labels' => $rows->keys()->all(),
        ];
    }
}
