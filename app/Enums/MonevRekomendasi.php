<?php

declare(strict_types=1);

namespace App\Enums;

enum MonevRekomendasi: string
{
    case Lanjut = 'lanjut';
    case LanjutPerbaikan = 'lanjut_perbaikan';
    case Dihentikan = 'dihentikan';

    public function label(): string
    {
        return match ($this) {
            self::Lanjut => 'Dilanjutkan',
            self::LanjutPerbaikan => 'Dilanjutkan dengan Perbaikan',
            self::Dihentikan => 'Dihentikan',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Lanjut => 'success',
            self::LanjutPerbaikan => 'warning',
            self::Dihentikan => 'danger',
        };
    }

    /** @return array<string,string> */
    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn (self $r) => [$r->value => $r->label()])->all();
    }
}
