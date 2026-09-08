<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Support\Settings;
use Filament\Widgets\Widget;

/**
 * Kartu "Profil Lembaga Penelitian" (kolom kanan dasbor, gaya BIMA V2).
 * Sumber data: Settings::institution() (menu Pengaturan Web).
 */
class ProfilLembagaCard extends Widget
{
    protected static string $view = 'filament.widgets.profil-lembaga-card';

    protected static ?int $sort = 2;

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
        $i = Settings::institution();

        return [
            'rows' => [
                ['Kode PT', $i['kode_pt'] ?? '-'],
                ['Nama Institusi', $i['nama'] ?? '-'],
                ['Klaster', $i['klaster'] ?? '-'],
                ['Website', $i['website'] ?? '-'],
                ['Nama Lembaga', $i['nama_lembaga'] ?? '-'],
            ],
        ];
    }
}
