<?php

declare(strict_types=1);

namespace App\Support;

use App\Enums\Role;

/**
 * Sumber tunggal daftar permission dan pemetaannya ke role.
 *
 * Dipakai oleh RolePermissionSeeder. Ditambah bertahap seiring modul baru.
 * Konvensi nama: "{modul}.{aksi}".
 */
final class Permissions
{
    /** Semua permission yang dikenal aplikasi. */
    public static function all(): array
    {
        return array_values(array_unique(array_merge(
            self::users(),
            self::schemes(),
            self::proposals(),
        )));
    }

    /** Modul: manajemen pengguna & role (Admin LPPM). */
    public static function users(): array
    {
        return [
            'users.viewAny',
            'users.view',
            'users.create',
            'users.update',
            'users.delete',
        ];
    }

    /** Modul: skema usulan (Admin LPPM). */
    public static function schemes(): array
    {
        return [
            'schemes.viewAny',
            'schemes.view',
            'schemes.create',
            'schemes.update',
            'schemes.delete',
        ];
    }

    /** Modul: usulan / proposal. Cakupan baris dipersempit lagi oleh ProposalPolicy. */
    public static function proposals(): array
    {
        return [
            'proposals.viewAny',
            'proposals.view',
            'proposals.create',
            'proposals.update',
            'proposals.delete',
        ];
    }

    /**
     * Permission per role. Super admin tidak didaftarkan (di-bypass Gate::before).
     *
     * @return array<string, list<string>>
     */
    public static function forRoles(): array
    {
        return [
            Role::Dosen->value => [
                'proposals.viewAny',
                'proposals.view',
                'proposals.create',
                'proposals.update',
                'proposals.delete',
            ],

            Role::AdminLppm->value => [
                ...self::users(),
                ...self::schemes(),
                'proposals.viewAny',
                'proposals.view',
                'proposals.update',
                'proposals.delete',
            ],

            Role::Reviewer->value => [
                'proposals.viewAny',
                'proposals.view',
            ],

            Role::Pimpinan->value => [
                'users.viewAny',
                'users.view',
                'schemes.viewAny',
                'schemes.view',
                'proposals.viewAny',
                'proposals.view',
            ],
        ];
    }
}
