<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Enums\ProposalStatus;
use App\Filament\Pages\DaftarUsulan;
use App\Filament\Resources\ProposalResource\Pages\ListProposals;
use App\Models\ProposalScheme;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Ringkasan di atas tabel "Monitoring Usulan" (gaya BIMA):
 *  - 7 kartu status yang sekaligus jadi tautan ke halaman drill-down;
 *  - tabel "Rekap Usulan" — pecahan jumlah per skema.
 * Semua angka mengikuti filter tabel pada halaman.
 */
class MonitoringUsulanStats extends Widget
{
    use InteractsWithPageTable;

    protected static string $view = 'filament.widgets.monitoring-usulan-stats';

    protected int|string|array $columnSpan = 'full';

    protected function getTablePage(): string
    {
        return ListProposals::class;
    }

    private const DISETUJUI = [
        ProposalStatus::ApprovedLppm->value,
        ProposalStatus::UnderReview->value,
        ProposalStatus::Funded->value,
        ProposalStatus::InProgress->value,
        ProposalStatus::Reported->value,
        ProposalStatus::OutputValidated->value,
    ];

    private const DIDANAI = [
        ProposalStatus::Funded->value,
        ProposalStatus::InProgress->value,
        ProposalStatus::Reported->value,
        ProposalStatus::OutputValidated->value,
    ];

    public function getViewData(): array
    {
        $base = $this->getPageTableQuery();
        $count = fn (Builder $q): int => $q->toBase()->getCountForPagination();

        return [
            'cards' => $this->cards($base, $count),
            'rekap' => $this->rekapPerSkema($base),
        ];
    }

    /** @param  \Closure(Builder): int  $count */
    private function cards(Builder $base, \Closure $count): array
    {
        return [
            [
                'label' => 'Usulan Draft',
                'value' => $count((clone $base)->where('status', ProposalStatus::Draft->value)),
                'bg' => '#64748b',
                'url' => DaftarUsulan::urlFor('draft'),
                'icon' => 'M7 3h7l5 5v13a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1Zm7 1v4h4',
            ],
            [
                'label' => 'Usulan Dikirim',
                'value' => $count((clone $base)->where('status', '!=', ProposalStatus::Draft->value)),
                'bg' => '#3b5bd9',
                'url' => DaftarUsulan::urlFor('dikirim'),
                'icon' => 'M22 2 11 13M22 2l-7 20-4-9-9-4 20-7Z',
            ],
            [
                'label' => 'Usulan Belum Ditinjau',
                'value' => $count((clone $base)->where('status', ProposalStatus::Submitted->value)),
                'bg' => '#f0b429',
                'url' => DaftarUsulan::urlFor('belum-ditinjau'),
                'icon' => 'M12 22a10 10 0 1 0 0-20 10 10 0 0 0 0 20Zm-1.5-6.5h3M9.1 9a3 3 0 1 1 4.4 3c-.9.6-1.5 1-1.5 2.5',
            ],
            [
                'label' => 'Usulan Disetujui',
                'value' => $count((clone $base)->whereIn('status', self::DISETUJUI)),
                'bg' => '#16a34a',
                'url' => DaftarUsulan::urlFor('disetujui'),
                'icon' => 'M12 22a10 10 0 1 0 0-20 10 10 0 0 0 0 20ZM8 12l3 3 5-6',
            ],
            [
                'label' => 'Usulan Ditolak',
                'value' => $count((clone $base)->where('status', ProposalStatus::Rejected->value)),
                'bg' => '#dc2626',
                'url' => DaftarUsulan::urlFor('ditolak'),
                'icon' => 'M12 22a10 10 0 1 0 0-20 10 10 0 0 0 0 20ZM9 9l6 6M15 9l-6 6',
            ],
            [
                'label' => 'Usulan Didanai',
                'value' => $count((clone $base)->whereIn('status', self::DIDANAI)),
                'bg' => '#16a34a',
                'url' => DaftarUsulan::urlFor('didanai'),
                'icon' => 'M4 20V10M10 20V4M16 20v-7M22 20H2',
            ],
            [
                'label' => 'Hasil Review Masuk',
                'value' => $count((clone $base)->whereHas('reviews', fn (Builder $r) => $r->whereNotNull('submitted_at'))),
                'bg' => '#16a34a',
                'url' => DaftarUsulan::urlFor('review-masuk'),
                'icon' => 'M9 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2h-2M9 3a2 2 0 0 0 4 0M8 13l2 2 4-4',
            ],
        ];
    }

    /**
     * Rekap jumlah usulan per skema — satu baris tabel per skema yang punya usulan.
     *
     * @return list<array{skema: string, url: string, draft: int, dikirim: int, belum_ditinjau: int, disetujui: int, ditolak: int, didanai: int}>
     */
    private function rekapPerSkema(Builder $base): array
    {
        /** @var Collection<int, object{scheme_id: int, status: string}> $rows */
        $rows = (clone $base)->reorder()->toBase()->get(['scheme_id', 'status']);

        if ($rows->isEmpty()) {
            return [];
        }

        $schemes = ProposalScheme::query()
            ->whereIn('id', $rows->pluck('scheme_id')->unique()->all())
            ->orderBy('nama_skema')
            ->get(['id', 'nama_skema']);

        return $schemes->map(function (ProposalScheme $scheme) use ($rows): array {
            $forScheme = $rows->where('scheme_id', $scheme->getKey());
            $in = fn (array $statuses): int => $forScheme->whereIn('status', $statuses)->count();

            return [
                'skema' => $scheme->nama_skema,
                'url' => ListProposals::getUrl().'?'.http_build_query([
                    'tableFilters' => ['scheme_id' => ['value' => $scheme->getKey()]],
                ]),
                'draft' => $forScheme->where('status', ProposalStatus::Draft->value)->count(),
                'dikirim' => $forScheme->where('status', '!=', ProposalStatus::Draft->value)->count(),
                'belum_ditinjau' => $forScheme->where('status', ProposalStatus::Submitted->value)->count(),
                'disetujui' => $in(self::DISETUJUI),
                'ditolak' => $forScheme->where('status', ProposalStatus::Rejected->value)->count(),
                'didanai' => $in(self::DIDANAI),
            ];
        })->values()->all();
    }
}
