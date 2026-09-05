<?php

declare(strict_types=1);

namespace App\Enums;

enum Jenjang: string
{
    case D3 = 'D3';
    case D4 = 'D4';
    case S1 = 'S1';
    case S2 = 'S2';
    case S3 = 'S3';
    case Profesi = 'Profesi';

    public function label(): string
    {
        return match ($this) {
            self::D3 => 'D-3',
            self::D4 => 'D-4 / Sarjana Terapan',
            self::S1 => 'S-1 / Sarjana',
            self::S2 => 'S-2 / Magister',
            self::S3 => 'S-3 / Doktor',
            self::Profesi => 'Profesi',
        };
    }

    /** @return array<string,string> */
    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn (self $j) => [$j->value => $j->label()])->all();
    }
}
