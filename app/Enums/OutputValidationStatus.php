<?php

declare(strict_types=1);

namespace App\Enums;

enum OutputValidationStatus: string
{
    case Pending = 'pending';
    case Valid = 'valid';
    case Revisi = 'revisi';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Menunggu Validasi',
            self::Valid => 'Tervalidasi',
            self::Revisi => 'Perlu Revisi',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending => 'gray',
            self::Valid => 'success',
            self::Revisi => 'warning',
        };
    }

    /** @return array<string,string> */
    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn (self $c) => [$c->value => $c->label()])->all();
    }
}
