<?php

declare(strict_types=1);

namespace App\Enums;

/** Jenis anggota tim usulan. */
enum MemberType: string
{
    case Dosen = 'dosen';
    case Mahasiswa = 'mahasiswa';
    case NonDosen = 'non_dosen';

    public function label(): string
    {
        return match ($this) {
            self::Dosen => 'Dosen',
            self::Mahasiswa => 'Mahasiswa',
            self::NonDosen => 'Non Dosen',
        };
    }

    /** Butuh persetujuan sebelum usulan dikirim. */
    public function needsApproval(): bool
    {
        return $this === self::Dosen;
    }

    /** @return array<string,string> */
    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn (self $c) => [$c->value => $c->label()])->all();
    }
}
