<?php

declare(strict_types=1);

namespace App\Enums;

enum AnnouncementType: string
{
    case Berita = 'berita';
    case Pengumuman = 'pengumuman';

    public function label(): string
    {
        return match ($this) {
            self::Berita => 'Berita',
            self::Pengumuman => 'Pengumuman',
        };
    }

    /** @return array<string,string> */
    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn (self $c) => [$c->value => $c->label()])->all();
    }
}
