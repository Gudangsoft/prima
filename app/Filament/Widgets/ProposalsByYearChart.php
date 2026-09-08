<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Enums\Kategori;
use App\Models\Proposal;
use Filament\Widgets\ChartWidget;

/**
 * Rekap jumlah usulan per tahun anggaran, dipecah menurut kategori.
 */
class ProposalsByYearChart extends ChartWidget
{
    protected static ?string $heading = 'Usulan per Tahun Anggaran';

    protected static ?int $sort = 7;

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
            ->selectRaw('proposals.tahun_anggaran as tahun, proposal_schemes.kategori as kategori, COUNT(*) as total')
            ->groupBy('proposals.tahun_anggaran', 'proposal_schemes.kategori')
            ->orderBy('proposals.tahun_anggaran')
            ->get();

        $years = $rows->pluck('tahun')->unique()->sort()->values();

        $dataset = fn (Kategori $kat, string $color): array => [
            'label' => $kat->label(),
            'data' => $years->map(fn ($y) => (int) $rows
                ->where('tahun', $y)
                ->where('kategori', $kat->value)
                ->sum('total'))->all(),
            'backgroundColor' => $color,
        ];

        return [
            'datasets' => [
                $dataset(Kategori::Penelitian, '#3b82f6'),
                $dataset(Kategori::Pengabdian, '#f59e0b'),
            ],
            'labels' => $years->map(fn ($y) => (string) $y)->all(),
        ];
    }
}
