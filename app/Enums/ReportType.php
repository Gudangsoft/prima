<?php

declare(strict_types=1);

namespace App\Enums;

enum ReportType: string
{
    case Kemajuan = 'kemajuan';
    case Akhir = 'akhir';

    public function label(): string
    {
        return match ($this) {
            self::Kemajuan => 'Laporan Kemajuan',
            self::Akhir => 'Laporan Akhir',
        };
    }

    /** @return array<string,string> */
    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn (self $c) => [$c->value => $c->label()])->all();
    }
}
