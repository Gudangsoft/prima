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
 * Impor / "Sinkronisasi Dosen" dari CSV. Baris dicocokkan berdasarkan NIDN
 * (atau email). Akun baru otomatis diberi peran "dosen" dan sandi acak.
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
                ->label('Nama lengkap')
                ->requiredMapping()
                ->rules(['required', 'string', 'max:255'])
                ->example('Dr. Budi Santoso, M.Kom.'),

            ImportColumn::make('email')
                ->label('Email')
                ->requiredMapping()
                ->rules(['required', 'email', 'max:255'])
                ->example('budi@kampus.ac.id'),

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

        $user = User::query()
            ->where('nidn', $nidn)
            ->orWhere('email', $this->data['email'])
            ->first() ?? new User;

        if (! $user->exists) {
            $user->password = Hash::make(Str::random(16));
            $user->email_verified_at = now();
        }

        $user->nidn = $nidn;

        return $user;
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
