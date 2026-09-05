<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\Kategori;
use App\Models\ProposalScheme;
use Illuminate\Database\Seeder;

class ProposalSchemeSeeder extends Seeder
{
    public function run(): void
    {
        $skema = [
            ['Penelitian Dosen Pemula', Kategori::Penelitian, 'Skema untuk dosen dengan jabatan fungsional awal.'],
            ['Penelitian Dasar', Kategori::Penelitian, 'Riset fundamental untuk pengembangan ilmu.'],
            ['Penelitian Terapan', Kategori::Penelitian, 'Riset berorientasi produk/penerapan.'],
            ['Pengabdian Kemitraan Masyarakat', Kategori::Pengabdian, 'Program pengabdian berbasis kemitraan dengan mitra sasaran.'],
            ['Pengabdian Desa Binaan', Kategori::Pengabdian, 'Pendampingan berkelanjutan pada desa binaan institusi.'],
        ];

        foreach ($skema as [$nama, $kategori, $deskripsi]) {
            ProposalScheme::updateOrCreate(
                ['nama_skema' => $nama, 'kategori' => $kategori->value],
                ['deskripsi' => $deskripsi, 'aktif' => true],
            );
        }
    }
}
