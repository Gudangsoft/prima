<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Support\Settings;
use Filament\Widgets\Widget;

/**
 * Kartu "Profil Pimpinan Lembaga Penelitian" (kolom kanan dasbor, gaya BIMA V2).
 */
class ProfilPimpinanCard extends Widget
{
    protected static string $view = 'filament.widgets.profil-pimpinan-card';

    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = [
        'default' => 'full',
        'lg' => 1,
    ];

    public static function canView(): bool
    {
        return auth()->user()?->isActingAs(['admin_lppm', 'pimpinan', 'super_admin']) ?? false;
    }

    public function getViewData(): array
    {
        $i = Settings::institution('penelitian');
        $pimpinan = Settings::pimpinanFor('penelitian');

        return [
            'rows' => [
                ['Nama Jabatan', $pimpinan['jabatan']],
                ['Nama Pimpinan', $pimpinan['nama']],
                ['NIDN', $pimpinan['nidn']],
                ['Nama Institusi', $i['nama'] ?: '-'],
            ],
        ];
    }
}
