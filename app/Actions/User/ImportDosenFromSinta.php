<?php

declare(strict_types=1);

namespace App\Actions\User;

use App\Enums\Jenjang;
use App\Enums\Role as RoleEnum;
use App\Models\ProgramStudi;
use App\Models\User;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

/**
 * Impor akun dosen dari file "Export Author" SINTA (kolom NIDN, NAMA, PRODI,
 * JABATAN FUNGSIONAL, dst). File SINTA punya 4 baris judul/metadata sebelum
 * baris header sesungguhnya — baris header terdeteksi otomatis (baris yang
 * memuat kolom NIDN & NAMA).
 *
 * Kunci pencocokan (upsert) adalah SINTAID, bukan NIDN — karena NIDN pada
 * sebagian baris data nyata terisi tidak valid (bukan angka). Akun baru
 * diberi email placeholder ({@see self::EMAIL_DOMAIN}) dan kata sandi
 * sementara ({@see self::DEFAULT_PASSWORD}) karena file SINTA tidak memuat
 * email — cocok untuk aktivasi awal selama OTP belum diaktifkan; ganti email
 * ke alamat asli sebelum mengaktifkan gerbang OTP.
 */
class ImportDosenFromSinta
{
    public const EMAIL_DOMAIN = 'dosen.local';

    public const DEFAULT_PASSWORD = 'GantiSaya123!';

    public function __invoke(string $filePath): ImportDosenResult
    {
        $handle = fopen($filePath, 'r');

        if ($handle === false) {
            throw new RuntimeException("Tidak bisa membuka file: {$filePath}");
        }

        // Lewati byte-order-mark UTF-8 bila ada di awal file.
        if (fread($handle, 3) !== "\xEF\xBB\xBF") {
            rewind($handle);
        }

        $created = 0;
        $updated = 0;
        $skipped = [];
        $headerMap = null;
        $baris = 0;

        while (($row = fgetcsv($handle)) !== false) {
            $baris++;

            if ($row === [null] || $row === ['']) {
                continue;
            }

            if ($headerMap === null) {
                $upper = array_map(fn ($c) => strtoupper(trim((string) $c)), $row);

                if (in_array('NIDN', $upper, true) && in_array('NAMA', $upper, true)) {
                    $headerMap = array_flip($upper);
                }

                continue;
            }

            $get = fn (string $kolom): string => trim((string) ($row[$headerMap[$kolom] ?? null] ?? ''));

            $sintaId = $get('SINTAID');
            $nama = $get('NAMA');

            if ($sintaId === '' || $nama === '') {
                $skipped[] = "Baris {$baris}: SINTAID atau NAMA kosong, dilewati.";

                continue;
            }

            try {
                $isNew = $this->upsert($sintaId, $nama, $get);
                $isNew ? $created++ : $updated++;
            } catch (Throwable $e) {
                $skipped[] = "Baris {$baris} ({$nama}): {$e->getMessage()}";
            }
        }

        fclose($handle);

        return new ImportDosenResult($created, $updated, $skipped);
    }

    /** @param  \Closure(string): string  $get */
    private function upsert(string $sintaId, string $nama, \Closure $get): bool
    {
        $user = User::firstOrNew(['sinta_id' => $sintaId]);
        $isNew = ! $user->exists;

        $user->name = $this->namaLengkap($nama, $get('GELAR DEPAN'), $get('GELAR BELAKANG'));
        $user->jabatan = $get('JABATAN FUNGSIONAL') ?: null;
        $user->pendidikan_terakhir = $get('PENDIDIKAN TERAKHIR') ?: null;
        $user->sinta_score_overall_v2 = $this->angka($get('SINTA SCORE OVERALL (VERSI 2)'));
        $user->sinta_score_3yr_v2 = $this->angka($get('SINTA SCORE 3YR (VERSI 2)'));
        $user->sinta_score_overall_v3 = $this->angka($get('SINTA SCORE OVERALL (VERSI 3)'));
        $user->sinta_score_3yr_v3 = $this->angka($get('SINTA SCORE 3YR (VERSI 3)'));
        $user->program_studi_id = $this->resolveProgramStudi($get('PRODI'));

        $nidn = $get('NIDN');
        if (preg_match('/^\d{6,20}$/', $nidn) === 1
            && ! User::where('nidn', $nidn)->where('id', '!=', $user->id ?? 0)->exists()) {
            $user->nidn = $nidn;
        }

        if ($isNew) {
            $user->email = 'sinta'.$sintaId.'@'.self::EMAIL_DOMAIN;
            $user->password = self::DEFAULT_PASSWORD;
        }

        $user->save();

        if ($isNew) {
            $user->assignRole(RoleEnum::Dosen->value);
        }

        return $isNew;
    }

    private function angka(string $nilai): ?float
    {
        return is_numeric($nilai) ? (float) $nilai : null;
    }

    private function namaLengkap(string $nama, string $gelarDepan, string $gelarBelakang): string
    {
        $namaTitle = Str::title(mb_strtolower($nama));

        return trim(
            ($gelarDepan !== '' ? $gelarDepan.' ' : '').
            $namaTitle.
            ($gelarBelakang !== '' ? ', '.$gelarBelakang : ''),
        );
    }

    private function resolveProgramStudi(string $prodiRaw): ?int
    {
        if ($prodiRaw === '' || ! preg_match('/^(D3|D4|S1|S2|S3|Profesi)\s+(.+)$/i', $prodiRaw, $m)) {
            return null;
        }

        $jenjang = Jenjang::tryFrom(strtoupper($m[1]) === 'PROFESI' ? 'Profesi' : strtoupper($m[1]));

        if ($jenjang === null) {
            return null;
        }

        $nama = trim($m[2]);

        $existing = ProgramStudi::query()
            ->where('jenjang', $jenjang->value)
            ->whereRaw('LOWER(nama) = ?', [mb_strtolower($nama)])
            ->first();

        if ($existing !== null) {
            return $existing->id;
        }

        return ProgramStudi::create([
            'kode' => $this->kodeUnik($jenjang->value, $nama),
            'nama' => $nama,
            'jenjang' => $jenjang->value,
            'aktif' => true,
        ])->id;
    }

    private function kodeUnik(string $jenjang, string $nama): string
    {
        $base = substr(strtoupper((string) preg_replace('/[^A-Z0-9]/i', '', $jenjang.$nama)), 0, 16);
        $kode = $base;
        $i = 1;

        while (ProgramStudi::where('kode', $kode)->exists()) {
            $kode = $base.$i;
            $i++;
        }

        return $kode;
    }
}
