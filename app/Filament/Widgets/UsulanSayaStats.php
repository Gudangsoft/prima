<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Enums\ProposalStatus;
use App\Filament\Resources\ProposalResource;
use App\Filament\Widgets\Concerns\ForDosen;
use App\Models\Proposal;
use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Builder;

/**
 * Empat kartu berwarna "Penelitian / Pengabdian / Didanai / Berjalan" di dasbor
 * dosen (gaya BIMA). Menghitung usulan milik dosen yang sedang login.
 */
class UsulanSayaStats extends Widget
{
    use ForDosen;

    protected static string $view = 'filament.widgets.usulan-saya-stats';

    protected static ?int $sort = 0;

    protected int|string|array $columnSpan = 'full';

    private const DIDANAI = [
        ProposalStatus::Funded->value,
        ProposalStatus::InProgress->value,
        ProposalStatus::Reported->value,
        ProposalStatus::OutputValidated->value,
    ];

    public function getViewData(): array
    {
        $own = fn (): Builder => Proposal::query()->where('user_id', auth()->id());
        $ofKategori = fn (string $k) => fn (Builder $q) => $q->where('kategori', $k);
        $listUrl = ProposalResource::getUrl();

        return [
            'cards' => [
                [
                    'label' => 'Penelitian',
                    'value' => $own()->whereHas('scheme', $ofKategori('penelitian'))->count(),
                    'bg' => '#ec4899',
                    'url' => $listUrl,
                    'icon' => 'M11 3a8 8 0 1 0 4.9 14.32l4.4 4.4 1.4-1.42-4.4-4.4A8 8 0 0 0 11 3Z',
                ],
                [
                    'label' => 'Pengabdian',
                    'value' => $own()->whereHas('scheme', $ofKategori('pengabdian'))->count(),
                    'bg' => '#0ea5b7',
                    'url' => $listUrl,
                    'icon' => 'M7 3h10a2 2 0 0 1 2 2v16l-7-3-7 3V5a2 2 0 0 1 2-2Z',
                ],
                [
                    'label' => 'Usulan Didanai',
                    'value' => $own()->whereIn('status', self::DIDANAI)->count(),
                    'bg' => '#16a34a',
                    'url' => $listUrl,
                    'icon' => 'M4 20V10M10 20V4M16 20v-7M22 20H2',
                ],
                [
                    'label' => 'Sedang Berjalan',
                    'value' => $own()->whereIn('status', [
                        ProposalStatus::InProgress->value,
                        ProposalStatus::Reported->value,
                    ])->count(),
                    'bg' => '#f59e0b',
                    'url' => $listUrl,
                    'icon' => 'M12 8v4l3 2M12 3a9 9 0 1 0 0 18 9 9 0 0 0 0-18Z',
                ],
            ],
        ];
    }
}
