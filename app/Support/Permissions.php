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
    private const MODULES = [
        'users' => 'Pengguna',
        'schemes' => 'Skema Usulan',
        'proposals' => 'Usulan',
        'announcements' => 'Berita / Pengumuman',
        'prodi' => 'Program Studi',
        'hero_slides' => 'Slider Beranda',
    ];

    private const ACTIONS = [
        'viewAny' => 'Lihat daftar',
        'view' => 'Lihat detail',
        'create' => 'Tambah',
        'update' => 'Ubah',
        'delete' => 'Hapus',
    ];

    /** Nama modul yang ramah-baca dari sebuah permission. */
    public static function moduleLabel(string $permission): string
    {
        $module = explode('.', $permission, 2)[0];

        return self::MODULES[$module] ?? ucfirst($module);
    }

    /** Label ramah-baca lengkap: "Usulan — Lihat daftar". */
    public static function label(string $permission): string
    {
        [$module, $action] = array_pad(explode('.', $permission, 2), 2, '');

        return (self::MODULES[$module] ?? ucfirst($module))
            .' — '.(self::ACTIONS[$action] ?? $action);
    }

    /** Semua permission yang dikenal aplikasi. */
    public static function all(): array
    {
        return array_values(array_unique(array_merge(
            self::users(),
            self::schemes(),
            self::proposals(),
            self::announcements(),
            self::prodi(),
            self::heroSlides(),
        )));
    }

    /** Modul: berita / pengumuman (Admin LPPM). */
    public static function announcements(): array
    {
        return [
            'announcements.viewAny',
            'announcements.view',
            'announcements.create',
            'announcements.update',
            'announcements.delete',
        ];
    }

    /** Modul: slider beranda halaman publik (Admin LPPM). */
    public static function heroSlides(): array
    {
        return [
            'hero_slides.viewAny',
            'hero_slides.view',
            'hero_slides.create',
            'hero_slides.update',
            'hero_slides.delete',
        ];
    }

    /** Modul: program studi (Admin LPPM). */
    public static function prodi(): array
    {
        return [
            'prodi.viewAny',
            'prodi.view',
            'prodi.create',
            'prodi.update',
            'prodi.delete',
        ];
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
                ...self::announcements(),
                ...self::prodi(),
                ...self::heroSlides(),
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
