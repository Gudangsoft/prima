<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\BidangFokus;
use App\Enums\Kategori;
use App\Enums\MemberApprovalStatus;
use App\Enums\MemberType;
use App\Enums\ProposalStatus;
use App\Models\Proposal;
use App\Models\ProposalMember;
use App\Models\ProposalScheme;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeder sekali-pakai: menyamakan data usulan skema "Penelitian Fundamental -
 * Reguler" dengan tampilan tab Usulan Danang di BIMA (2 baris nyata) — bikin
 * skemanya bila belum ada, lalu 2 usulan: Agustinus Budi Santoso sebagai
 * ketua (Danang anggota, Tidak Didanai) dan Danang sebagai ketua (Didanai).
 * Aman dijalankan ulang (idempoten via cek judul) — hapus file ini setelah
 * tidak lagi diperlukan.
 */
class PenelitianFundamentalRegulerSeeder extends Seeder
{
    public function run(): void
    {
        $judulAgustinus = 'Platform E-Katalog UMKM Berbasis CMS dengan Pendekatan Geo-Marketing untuk Meningkatkan Daya Saing Digital Produk Lokal';
        $judulDanang = 'Peran Corporate Social Responsibility (CSR) dalam Meningkatkan Kesadaran Keamanan Siber untuk Penguatan Budaya Digital yang Berkelanjutan';

        if (Proposal::where('judul', $judulAgustinus)->exists() || Proposal::where('judul', $judulDanang)->exists()) {
            $this->command?->warn('Usulan Penelitian Fundamental - Reguler sudah ada, seeder dilewati.');

            return;
        }

        // NIDN dicocokkan dengan/tanpa nol di depan (sebagian tool CSV/spreadsheet
        // menghilangkan nol di depan angka), plus fallback ke nama.
        $danang = User::where('nidn', '0615098702')->orWhere('nidn', '615098702')
            ->orWhere('name', 'LIKE', 'Danang%')->first();
        $agustinus = User::where('nidn', '0603099003')->orWhere('nidn', '603099003')
            ->orWhere('name', 'LIKE', 'Agustinus Budi Santoso%')->first();

        if (! $danang || ! $agustinus) {
            $this->command?->error('Akun Danang dan/atau Agustinus Budi Santoso tidak ditemukan.');

            return;
        }

        DB::transaction(function () use ($danang, $agustinus, $judulAgustinus, $judulDanang) {
            $skema = ProposalScheme::firstOrCreate(
                ['nama_skema' => 'Penelitian Fundamental - Reguler'],
                [
                    'kategori' => Kategori::Penelitian->value,
                    'deskripsi' => 'Skema penelitian fundamental reguler untuk dosen, berorientasi luaran ilmiah dasar.',
                    'aktif' => true,
                ],
            );

            $p1 = Proposal::factory()->forDosen($agustinus)->forScheme($skema)
                ->status(ProposalStatus::Rejected)
                ->create([
                    'judul' => $judulAgustinus,
                    'abstrak' => 'Penelitian ini mengembangkan platform e-katalog berbasis CMS dengan pendekatan geo-marketing untuk membantu UMKM meningkatkan daya saing produk lokal secara digital.',
                    'bidang_fokus' => BidangFokus::SosialHumaniora->value,
                    'tahun_anggaran' => 2026,
                ]);

            ProposalMember::create([
                'proposal_id' => $p1->id,
                'jenis' => MemberType::Dosen->value,
                'user_id' => $danang->id,
                'nama' => $danang->name,
                'tugas' => 'Anggota',
                'status' => MemberApprovalStatus::Menyetujui->value,
            ]);

            Proposal::factory()->forDosen($danang)->forScheme($skema)
                ->status(ProposalStatus::InProgress)
                ->create([
                    'judul' => $judulDanang,
                    'abstrak' => 'Penelitian ini mengkaji peran Corporate Social Responsibility (CSR) dalam meningkatkan kesadaran keamanan siber masyarakat guna memperkuat budaya digital yang berkelanjutan.',
                    'bidang_fokus' => BidangFokus::SosialHumaniora->value,
                    'tahun_anggaran' => 2026,
                ]);
        });

        $this->command?->info('Selesai: usulan Penelitian Fundamental - Reguler berhasil dibuat.');
    }
}
