<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\Jenjang;
use App\Enums\Role;
use App\Models\ProgramStudi;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProgramStudiSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['55201', 'Teknik Informatika', Jenjang::S1, 'Fakultas Teknik & Informatika'],
            ['57201', 'Sistem Informasi', Jenjang::S1, 'Fakultas Teknik & Informatika'],
            ['55301', 'Teknologi Informasi', Jenjang::D3, 'Fakultas Teknik & Informatika'],
            ['61201', 'Manajemen', Jenjang::S1, 'Fakultas Ekonomi & Bisnis'],
            ['62201', 'Akuntansi', Jenjang::S1, 'Fakultas Ekonomi & Bisnis'],
            ['55101', 'Magister Ilmu Komputer', Jenjang::S2, 'Program Pascasarjana'],
        ];

        foreach ($data as [$kode, $nama, $jenjang, $fakultas]) {
            ProgramStudi::updateOrCreate(
                ['kode' => $kode],
                ['nama' => $nama, 'jenjang' => $jenjang->value, 'fakultas' => $fakultas, 'aktif' => true],
            );
        }

        // Tautkan dosen contoh ke prodi.
        $ti = ProgramStudi::where('kode', '55201')->first();
        if ($ti) {
            User::query()->role(Role::Dosen->value)->whereNull('program_studi_id')
                ->update(['program_studi_id' => $ti->id]);
        }
    }
}
