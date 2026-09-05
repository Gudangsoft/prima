<?php

declare(strict_types=1);

namespace App\Enums;

enum OutputType: string
{
    case Publikasi = 'publikasi';
    case Hki = 'hki';
    case Produk = 'produk';
    case Lainnya = 'lainnya';

    public function label(): string
    {
        return match ($this) {
            self::Publikasi => 'Publikasi',
            self::Hki => 'HKI',
            self::Produk => 'Produk',
            self::Lainnya => 'Lainnya',
        };
    }

    /** @return array<string,string> */
    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn (self $c) => [$c->value => $c->label()])->all();
    }
}
