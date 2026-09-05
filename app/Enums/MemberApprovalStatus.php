<?php

declare(strict_types=1);

namespace App\Enums;

/** Status persetujuan seorang anggota dosen atas keikutsertaannya. */
enum MemberApprovalStatus: string
{
    case Menunggu = 'menunggu';
    case Menyetujui = 'menyetujui';
    case Menolak = 'menolak';

    public function label(): string
    {
        return match ($this) {
            self::Menunggu => 'Menunggu',
            self::Menyetujui => 'Menyetujui',
            self::Menolak => 'Menolak',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Menunggu => 'warning',
            self::Menyetujui => 'success',
            self::Menolak => 'danger',
        };
    }

    /** @return array<string,string> */
    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn (self $c) => [$c->value => $c->label()])->all();
    }
}
