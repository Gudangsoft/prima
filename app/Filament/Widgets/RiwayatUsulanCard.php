<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Filament\Resources\ProposalResource;
use App\Filament\Widgets\Concerns\ForDosen;
use App\Models\Proposal;
use Filament\Widgets\Widget;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Kartu "Riwayat Usulan" di kolom kanan dasbor dosen (gaya BIMA): daftar usulan
 * terbaru milik dosen, dikelompokkan menurut kategori skema.
 */
class RiwayatUsulanCard extends Widget
{
    use ForDosen;

    protected static string $view = 'filament.widgets.riwayat-usulan-card';

    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = [
        'default' => 1,
        'lg' => 1,
    ];

    public function getViewData(): array
    {
        $mine = Proposal::query()
            ->where('user_id', auth()->id())
            ->with('scheme:id,kategori')
            ->latest('updated_at')
            ->get(['id', 'judul', 'scheme_id']);

        $group = fn (string $kategori): Collection => $mine
            ->filter(fn (Proposal $p): bool => $p->scheme?->kategori?->value === $kategori)
            ->take(3)
            ->map(fn (Proposal $p): array => [
                'judul' => Str::limit($p->judul, 90),
                'url' => ProposalResource::getUrl('view', ['record' => $p]),
            ])
            ->values();

        return [
            'groups' => [
                ['label' => 'Penelitian', 'items' => $group('penelitian')],
                ['label' => 'Pengabdian kepada Masyarakat', 'items' => $group('pengabdian')],
            ],
            'moreUrl' => ProposalResource::getUrl(),
        ];
    }
}
