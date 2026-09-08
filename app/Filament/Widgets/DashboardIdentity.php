<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

/**
 * Kartu sambutan di puncak dasbor (gaya BIMA V2).
 */
class DashboardIdentity extends Widget
{
    protected static string $view = 'filament.widgets.dashboard-identity';

    protected static ?int $sort = -2;

    protected int|string|array $columnSpan = 'full';

    /** Dosen: hero 2/3 (kartu "Profil Saya" mengisi 1/3 di sebelahnya); peran lain: penuh. */
    public function getColumnSpan(): int|string|array
    {
        return auth()->user()?->isActingAs('dosen')
            ? ['default' => 1, 'lg' => 2]
            : 'full';
    }

    public function getViewData(): array
    {
        $user = auth()->user();

        return [
            'nama' => $user->name,
            'avatarUrl' => $user->getFilamentAvatarUrl(),
            'tanggal' => now()->translatedFormat('j F Y'),
            'hariEn' => now()->locale('en')->isoFormat('dddd'),
        ];
    }
}
