<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Kategori kegiatan pada skema usulan.
 */
enum Kategori: string
{
    case Penelitian = 'penelitian';
    case Pengabdian = 'pengabdian';

    public function label(): string
    {
        return match ($this) {
            self::Penelitian => 'Penelitian',
            self::Pengabdian => 'Pengabdian kepada Masyarakat',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Penelitian => 'info',
            self::Pengabdian => 'success',
        };
    }

    /** @return array<string,string> value => label untuk komponen Select. */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $k) => [$k->value => $k->label()])
            ->all();
    }
}
