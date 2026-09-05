<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Daftar role aplikasi. Nilai string harus sama persis dengan yang
 * tersimpan di tabel `roles` (Spatie). Dipakai untuk menghindari
 * magic string saat memeriksa/menetapkan role.
 */
enum Role: string
{
    case Dosen = 'dosen';
    case AdminLppm = 'admin_lppm';
    case Reviewer = 'reviewer';
    case Pimpinan = 'pimpinan';
    case SuperAdmin = 'super_admin';

    /** Label tampilan Bahasa Indonesia. */
    public function label(): string
    {
        return match ($this) {
            self::Dosen => 'Dosen',
            self::AdminLppm => 'Admin LPPM',
            self::Reviewer => 'Reviewer',
            self::Pimpinan => 'Pimpinan / Ketua LPPM',
            self::SuperAdmin => 'Super Admin',
        };
    }

    /** @return array<string,string> value => label, untuk komponen Select. */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $role) => [$role->value => $role->label()])
            ->all();
    }

    /** Role yang boleh melakukan persetujuan institusi (gerbang tunggal). */
    public static function institutionalApprovers(): array
    {
        return [self::AdminLppm->value, self::Pimpinan->value];
    }

    /** @return list<string> semua nilai role. */
    public static function values(): array
    {
        return array_map(fn (self $r) => $r->value, self::cases());
    }
}
