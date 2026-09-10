<?php

declare(strict_types=1);

namespace App\Support;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Penyimpanan setelan situs berbasis tabel key-value (`settings`), di-cache.
 * Nilai default diambil dari config('sip2m.*') bila key belum diset.
 */
final class Settings
{
    private const CACHE_KEY = 'app.settings';

    /** @return array<string,string> */
    public static function all(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, function (): array {
            try {
                if (! Schema::hasTable('settings')) {
                    return [];
                }

                return DB::table('settings')->pluck('value', 'key')->all();
            } catch (Throwable) {
                return [];
            }
        });
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $value = self::all()[$key] ?? null;

        if ($value !== null && $value !== '') {
            return $value;
        }

        return $default ?? config('sip2m.'.self::configKey($key));
    }

    public static function set(string $key, mixed $value): void
    {
        DB::table('settings')->updateOrInsert(
            ['key' => $key],
            ['value' => $value === null ? null : (string) $value, 'updated_at' => now()],
        );

        Cache::forget(self::CACHE_KEY);
    }

    /**
     * @param  array<string,mixed>  $values
     */
    public static function setMany(array $values): void
    {
        foreach ($values as $key => $value) {
            self::set($key, $value);
        }
    }

    public static function forget(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /** Semua kategori lembaga yang dikenal. */
    public const KATEGORI = ['penelitian', 'pengabdian'];

    /** Daftar sufiks field per kategori lembaga. */
    public const LEMBAGA_FIELDS = [
        'nama_lembaga', 'sk_pendirian', 'alamat', 'telepon', 'fax',
        'email', 'website', 'jabatan_pimpinan', 'pimpinan_nama', 'pimpinan_nidn',
    ];

    /** Prefix key setelan untuk sebuah kategori (pen_ / pkm_). */
    public static function prefix(string $kategori): string
    {
        return $kategori === 'pengabdian' ? 'pkm_' : 'pen_';
    }

    /**
     * Profil lembaga (identitas PT + data kategori). Field kategori "pengabdian"
     * yang kosong di-fallback ke nilai "penelitian".
     *
     * @return array<string,mixed>
     */
    public static function institution(string $kategori = 'penelitian'): array
    {
        $kategori = in_array($kategori, self::KATEGORI, true) ? $kategori : 'penelitian';
        $prefix = self::prefix($kategori);

        $field = function (string $name) use ($prefix, $kategori): string {
            $value = (string) (self::all()["{$prefix}{$name}"]
                ?? config("sip2m.institution.{$kategori}.{$name}") ?? '');

            if ($value === '' && $kategori === 'pengabdian') {
                // fallback ke penelitian
                $value = (string) (self::all()["pen_{$name}"]
                    ?? config("sip2m.institution.penelitian.{$name}") ?? '');
            }

            return $value;
        };

        return [
            'kategori' => $kategori,
            'kode_pt' => self::get('institusi_kode_pt'),
            'nama' => self::get('institusi_nama'),
            'klaster' => self::get('institusi_klaster'),
            'nama_lembaga' => $field('nama_lembaga'),
            'sk_pendirian' => $field('sk_pendirian'),
            'alamat' => $field('alamat'),
            'telepon' => $field('telepon'),
            'fax' => $field('fax'),
            'email' => $field('email'),
            'website' => $field('website'),
            'jabatan_pimpinan' => $field('jabatan_pimpinan'),
            'pimpinan_nama' => $field('pimpinan_nama'),
            'pimpinan_nidn' => $field('pimpinan_nidn'),
        ];
    }

    /**
     * Data pimpinan lembaga untuk sebuah kategori. Bila nama belum diisi di
     * setelan, di-fallback ke akun pengguna berperan "pimpinan".
     *
     * @return array{jabatan:string, nama:string, nidn:string}
     */
    public static function pimpinanFor(string $kategori): array
    {
        $i = self::institution($kategori);
        $nama = (string) $i['pimpinan_nama'];
        $nidn = (string) $i['pimpinan_nidn'];

        if ($nama === '') {
            $user = User::query()->role(Role::Pimpinan->value)->orderBy('id')->first();
            $nama = $user?->name ?? '-';
            $nidn = $nidn ?: ($user?->nidn ?? '-');
        }

        return [
            'jabatan' => $i['jabatan_pimpinan'] ?: 'Ketua Lembaga',
            'nama' => $nama,
            'nidn' => $nidn ?: '-',
        ];
    }

    /** @return array<string,mixed> branding hasil resolve (setelan -> config). */
    public static function branding(): array
    {
        return [
            'app_name' => self::get('app_name', 'SIP2M'),
            'primary_color' => self::get('primary_color', '#3B5BD9'),
            'logo_url' => self::fileUrl('logo_path', 'images/logo-sip2m.svg'),
            'logo_instansi_url' => self::optionalFileUrl('logo_instansi_path'),
            'favicon_url' => self::fileUrl('favicon_path', 'images/favicon.svg'),
            'login_note' => self::get('login_note'),
            'hero_title' => self::get('hero_title'),
            'hero_subtitle' => self::get('hero_subtitle'),
        ];
    }

    /** Semua sufiks field footer publik. */
    public const FOOTER_FIELDS = ['lembaga', 'deskripsi', 'alamat', 'email', 'telepon', 'copyright'];

    /** @return array<string,string> konten footer halaman publik (setelan -> config). */
    public static function footer(): array
    {
        return collect(self::FOOTER_FIELDS)
            ->mapWithKeys(fn (string $f): array => [$f => (string) self::get("footer_{$f}")])
            ->all();
    }

    public static function fileUrl(string $key, string $default): string
    {
        $path = self::all()[$key] ?? null;

        return filled($path)
            ? Storage::disk('public')->url((string) $path)
            : '/'.ltrim($default, '/');
    }

    /** URL berkas unggahan opsional (mis. PDF buku panduan) atau null bila belum diset. */
    public static function optionalFileUrl(string $key): ?string
    {
        $path = self::all()[$key] ?? null;

        return filled($path) ? Storage::disk('public')->url((string) $path) : null;
    }

    /** Pemetaan key setelan -> path config default (best effort). */
    private static function configKey(string $key): string
    {
        return match (true) {
            $key === 'app_name' => 'branding.app_name',
            $key === 'primary_color' => 'branding.primary_color',
            $key === 'institusi_kode_pt' => 'institution.kode_pt',
            $key === 'institusi_nama' => 'institution.nama',
            $key === 'institusi_klaster' => 'institution.klaster',
            str_starts_with($key, 'pen_') => 'institution.penelitian.'.substr($key, 4),
            str_starts_with($key, 'pkm_') => 'institution.pengabdian.'.substr($key, 4),
            str_starts_with($key, 'footer_') => 'footer.'.substr($key, 7),
            default => 'branding.'.$key,
        };
    }
}
