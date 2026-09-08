<?php

declare(strict_types=1);

namespace App\Filament\Pages\Concerns;

use Filament\Pages\Page;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;

/**
 * Basis halaman-tabel untuk peran pengawas (Admin LPPM / Pimpinan / Super Admin).
 * Sub-kelas cukup mendefinisikan properti navigasi + method table().
 */
abstract class OversightTablePage extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string $view = 'filament.pages.table-page';

    public static function canAccess(): bool
    {
        return auth()->user()?->isActingAs(['admin_lppm', 'pimpinan', 'super_admin']) ?? false;
    }
}
