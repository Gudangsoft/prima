<?php

declare(strict_types=1);

namespace App\Filament\Imports;

use App\Enums\Role;
use App\Models\ProgramStudi;
use App\Models\User;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Impor / "Sinkronisasi Dosen" dari CSV. Baris dicocokkan berdasarkan NIDN.
 * Akun baru otomatis diberi peran "dosen", email placeholder, dan sandi acak
 * (login dosen memakai NIDN, bukan email).
 */
class DosenImporter extends Importer
{
    protected static ?string $model = User::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('nidn')
                ->label('NIDN')
                ->requiredMapping()
                ->rules(['required', 'string', 'max:20'])
                ->example('0401019001'),

            ImportColumn::make('name')
                ->label('Nama')
                ->requiredMapping()
                ->rules(['required', 'string', 'max:255'])
                ->example('BUDI SANTOSO')
                // Digabung dengan gelar depan/belakang di resolveRecord().
                ->fillRecordUsing(fn () => null),

            ImportColumn::make('gelar_depan')
                ->label('Gelar depan')
                ->rules(['nullable', 'string', 'max:50'])
                ->example('Dr')
                // Bukan kolom users; digabung ke `name` di resolveRecord().
                ->fillRecordUsing(fn () => null),

            ImportColumn::make('gelar_belakang')
                ->label('Gelar belakang')
                ->rules(['nullable', 'string', 'max:100'])
                ->example('S.Kom, M.Kom')
                ->fillRecordUsing(fn () => null),

            ImportColumn::make('sinta_id')
                ->label('SINTA ID')
                ->rules(['nullable', 'string', 'max:255'])
                ->example('257669'),

            ImportColumn::make('pendidikan_terakhir')
                ->label('Pendidikan terakhir')
                ->rules(['nullable', 'string', 'max:10'])
                ->example('S2'),

            ImportColumn::make('sinta_score_overall_v2')
                ->label('Skor SINTA Overall (v2)')
                ->rules(['nullable', 'numeric'])
                ->example('771.5'),

            ImportColumn::make('sinta_score_3yr_v2')
                ->label('Skor SINTA 3Yr (v2)')
                ->rules(['nullable', 'numeric'])
                ->example('391.5'),

            ImportColumn::make('sinta_score_overall_v3')
                ->label('Skor SINTA Overall (v3)')
                ->rules(['nullable', 'numeric'])
                ->example('1123.87'),

            ImportColumn::make('sinta_score_3yr_v3')
                ->label('Skor SINTA 3Yr (v3)')
                ->rules(['nullable', 'numeric'])
                ->example('620.2'),

            ImportColumn::make('phone_number')
                ->label('No. HP')
                ->rules(['nullable', 'string', 'max:30'])
                ->example('081234567890'),

            ImportColumn::make('jabatan')
                ->label('Jabatan fungsional')
                ->rules(['nullable', 'string', 'max:100'])
                ->example('Lektor'),

            ImportColumn::make('kompetensi')
                ->rules(['nullable', 'string', 'max:255'])
                ->example('Rekayasa Perangkat Lunak'),

            ImportColumn::make('kode_prodi')
                ->label('Kode program studi')
                ->rules(['nullable', 'string', 'max:20'])
                ->example('55201')
                // Bukan kolom users; ditangani manual di beforeSave().
                ->fillRecordUsing(fn () => null),
        ];
    }

    public function resolveRecord(): User
    {
        $nidn = trim((string) $this->data['nidn']);

        $user = User::query()->where('nidn', $nidn)->first() ?? new User;

        if (! $user->exists) {
            $user->password = Hash::make(Str::random(16));
            $user->email_verified_at = now();
            // Data sumber (export SINTA) tidak menyertakan email; login tetap
            // bisa lewat NIDN, jadi diberi email placeholder.
            $user->email = 'nidn'.$nidn.'@dosen.local';
        }

        $user->nidn = $nidn;
        $user->name = $this->namaLengkap();

        return $user;
    }

    private function namaLengkap(): string
    {
        $nama = trim((string) $this->data['name']);
        $gelarDepan = trim((string) ($this->data['gelar_depan'] ?? ''));
        $gelarBelakang = trim((string) ($this->data['gelar_belakang'] ?? ''));

        return trim(
            ($gelarDepan !== '' ? $gelarDepan.' ' : '').
            $nama.
            ($gelarBelakang !== '' ? ', '.$gelarBelakang : ''),
        );
    }

    public function beforeSave(): void
    {
        // Tautkan program studi berdasarkan kode (bila diberikan & dikenal).
        $kode = trim((string) ($this->data['kode_prodi'] ?? ''));

        if ($kode !== '') {
            $this->record->program_studi_id = ProgramStudi::query()
                ->where('kode', $kode)->value('id');
        }
    }

    public function afterSave(): void
    {
        if (! $this->record->hasRole(Role::Dosen->value)) {
            $this->record->assignRole(Role::Dosen->value);
        }
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Impor Dosen selesai: '.number_format($import->successful_rows).' baris berhasil.';

        if ($failed = $import->getFailedRowsCount()) {
            $body .= ' '.number_format($failed).' baris gagal.';
        }

        return $body;
    }
}
