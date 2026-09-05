<?php

declare(strict_types=1);

namespace App\Enums;

enum ReviewRecommendation: string
{
    case Danai = 'danai';
    case Tolak = 'tolak';
    case Revisi = 'revisi';

    public function label(): string
    {
        return match ($this) {
            self::Danai => 'Layak Didanai',
            self::Tolak => 'Tidak Layak / Tolak',
            self::Revisi => 'Perlu Revisi',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Danai => 'success',
            self::Tolak => 'danger',
            self::Revisi => 'warning',
        };
    }

    /** @return array<string,string> */
    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn (self $c) => [$c->value => $c->label()])->all();
    }
}
