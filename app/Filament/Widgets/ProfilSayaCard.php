<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Enums\ProposalStatus;
use App\Filament\Widgets\Concerns\ForDosen;
use App\Models\Proposal;
use App\Support\Settings;
use Filament\Facades\Filament;
use Filament\Widgets\Widget;

/**
 * Kartu "Profil Saya" di kolom kanan dasbor dosen (gaya BIMA).
 */
class ProfilSayaCard extends Widget
{
    use ForDosen;

    protected static string $view = 'filament.widgets.profil-saya-card';

    protected static ?int $sort = -1;

    protected int|string|array $columnSpan = [
        'default' => 1,
        'lg' => 1,
    ];

    private const DIDANAI = [
        ProposalStatus::Funded->value,
        ProposalStatus::InProgress->value,
        ProposalStatus::Reported->value,
        ProposalStatus::OutputValidated->value,
    ];

    public function getViewData(): array
    {
        $user = auth()->user();
        $own = Proposal::query()->where('user_id', $user->getKey());

        return [
            'nama' => $user->name,
            'avatarUrl' => $user->getFilamentAvatarUrl(),
            'prodi' => $user->programStudi?->nama,
            'institusi' => Settings::institution()['nama'] ?: config('app.name'),
            'editUrl' => Filament::getProfileUrl(),
            'stats' => [
                ['label' => 'Total Usulan', 'value' => (clone $own)->count()],
                ['label' => 'Usulan Didanai', 'value' => (clone $own)->whereIn('status', self::DIDANAI)->count()],
                ['label' => 'Skor SINTA', 'value' => $user->sinta_score_overall_v2 !== null ? number_format((float) $user->sinta_score_overall_v2, 0) : '—'],
                ['label' => 'Jenjang Pendidikan', 'value' => $user->pendidikan_terakhir ?: '—'],
                ['label' => 'Jabatan Akademik', 'value' => $user->jabatan ?: '—'],
            ],
        ];
    }
}
