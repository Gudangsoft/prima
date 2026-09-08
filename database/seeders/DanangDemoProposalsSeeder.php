<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\BidangFokus;
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
 * Seeder sekali-pakai: mengisi contoh usulan (Penelitian & Pengabdian) untuk
 * akun dosen "Danang" (hasil impor SINTA nyata), agar dashboard & menu
 * usulannya terisi data contoh seperti pada BIMA. Aman dijalankan ulang
 * (idempoten via cek judul) — hapus file ini setelah tidak lagi diperlukan.
 */
class DanangDemoProposalsSeeder extends Seeder
{
    public function run(): void
    {
        $danang = User::where('nidn', '0615098702')->first();

        if (! $danang) {
            $this->command?->error('Akun Danang (NIDN 0615098702) tidak ditemukan.');

            return;
        }

        if (Proposal::where('user_id', $danang->id)->exists()) {
            $this->command?->warn('Danang sudah punya usulan, seeder dilewati.');

            return;
        }

        $edwin = User::role('dosen')->where('id', '!=', $danang->id)->inRandomOrder()->first();

        // Dicocokkan berdasarkan nama skema (bukan ID) — ID auto-increment
        // bisa berbeda antara database lokal dan produksi.
        $skemaByName = fn (string $nama): ?ProposalScheme => ProposalScheme::where('nama_skema', $nama)->first();

        $skemaDosenPemula = $skemaByName('Penelitian Dosen Pemula');
        $skemaDasar = $skemaByName('Penelitian Dasar');
        $skemaTerapan = $skemaByName('Penelitian Terapan');
        $skemaKemitraan = $skemaByName('Pengabdian Kemitraan Masyarakat');
        $skemaDesaBinaan = $skemaByName('Pengabdian Desa Binaan');

        if (! $skemaDosenPemula || ! $skemaDasar || ! $skemaTerapan || ! $skemaKemitraan || ! $skemaDesaBinaan) {
            $this->command?->error('Skema dasar (Penelitian Dosen Pemula/Dasar/Terapan, Pengabdian Kemitraan Masyarakat/Desa Binaan) belum lengkap — jalankan ProposalSchemeSeeder dahulu.');

            return;
        }

        DB::transaction(function () use ($danang, $edwin, $skemaDosenPemula, $skemaDasar, $skemaTerapan, $skemaKemitraan, $skemaDesaBinaan) {
            Proposal::factory()->forDosen($danang)->forScheme($skemaDosenPemula)
                ->status(ProposalStatus::OutputValidated)
                ->create([
                    'judul' => 'Sistem Pendukung Keputusan Pemilihan Bibit Unggul Berbasis Metode SAW',
                    'abstrak' => 'Penelitian ini mengembangkan sistem pendukung keputusan untuk membantu petani memilih bibit unggul menggunakan metode Simple Additive Weighting (SAW), diuji pada studi kasus kelompok tani mitra.',
                    'bidang_fokus' => BidangFokus::Tik->value,
                    'tahun_anggaran' => 2023,
                ]);

            Proposal::factory()->forDosen($danang)->forScheme($skemaDasar)
                ->status(ProposalStatus::InProgress)
                ->create([
                    'judul' => 'Rancang Bangun Aplikasi Klasifikasi Kualitas Kopi Menggunakan Convolutional Neural Network',
                    'abstrak' => 'Penelitian bertujuan membangun model klasifikasi citra biji kopi menggunakan CNN untuk mengukur mutu biji kopi secara otomatis, sebagai alternatif penilaian manual yang subjektif.',
                    'bidang_fokus' => BidangFokus::PanganPertanian->value,
                    'tahun_anggaran' => 2024,
                ]);

            Proposal::factory()->forDosen($danang)->forScheme($skemaTerapan)
                ->status(ProposalStatus::UnderReview)
                ->create([
                    'judul' => 'Implementasi Internet of Things untuk Monitoring Kualitas Air Tambak Udang',
                    'abstrak' => 'Penelitian ini merancang purwarupa perangkat IoT untuk memantau suhu, pH, dan kadar oksigen terlarut pada tambak udang secara real-time guna mendukung produktivitas budidaya.',
                    'bidang_fokus' => BidangFokus::Kemaritiman->value,
                    'tahun_anggaran' => 2025,
                ]);

            if ($edwin) {
                $p4 = Proposal::factory()->forDosen($edwin)->forScheme($skemaDosenPemula)
                    ->status(ProposalStatus::Rejected)
                    ->create([
                        'judul' => 'Analisis Sentimen Ulasan Produk UMKM pada Marketplace Menggunakan Naive Bayes',
                        'abstrak' => 'Penelitian ini menganalisis sentimen ulasan pelanggan UMKM di marketplace untuk membantu pelaku usaha memahami persepsi konsumen menggunakan algoritma Naive Bayes Classifier.',
                        'bidang_fokus' => BidangFokus::Tik->value,
                        'tahun_anggaran' => 2023,
                    ]);

                ProposalMember::create([
                    'proposal_id' => $p4->id,
                    'jenis' => MemberType::Dosen->value,
                    'user_id' => $danang->id,
                    'nama' => $danang->name,
                    'tugas' => 'Anggota',
                    'status' => MemberApprovalStatus::Menyetujui->value,
                ]);
            }

            Proposal::factory()->forDosen($danang)->forScheme($skemaKemitraan)
                ->status(ProposalStatus::Reported)
                ->create([
                    'judul' => 'Pelatihan Digital Marketing bagi Pelaku UMKM Kelurahan Binaan',
                    'abstrak' => 'Kegiatan pengabdian ini memberikan pelatihan pemasaran digital dan pengelolaan media sosial bagi pelaku UMKM mitra guna meningkatkan jangkauan pasar dan omzet penjualan.',
                    'bidang_fokus' => BidangFokus::SosialHumaniora->value,
                    'tahun_anggaran' => 2024,
                ]);

            Proposal::factory()->forDosen($danang)->forScheme($skemaDesaBinaan)
                ->status(ProposalStatus::Submitted)
                ->create([
                    'judul' => 'Pendampingan Penerapan Sistem Informasi Administrasi Desa Berbasis Web',
                    'abstrak' => 'Kegiatan ini mendampingi perangkat desa mitra dalam menerapkan sistem informasi administrasi kependudukan berbasis web untuk mempercepat pelayanan masyarakat.',
                    'bidang_fokus' => BidangFokus::Tik->value,
                    'tahun_anggaran' => 2025,
                ]);
        });

        $this->command?->info('Selesai: usulan contoh untuk Danang berhasil dibuat.');
    }
}
