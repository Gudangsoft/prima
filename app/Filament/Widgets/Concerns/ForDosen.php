<?php

declare(strict_types=1);

namespace App\Filament\Widgets\Concerns;

/**
 * Widget dasbor yang hanya tampil untuk dosen/pengusul murni
 * (bukan admin_lppm / pimpinan / super_admin).
 */
trait ForDosen
{
    public static function canView(): bool
    {
        return auth()->user()?->isActingAs('dosen') ?? false;
    }
}
