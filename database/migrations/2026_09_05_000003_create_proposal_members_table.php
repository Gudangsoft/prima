<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Anggota tim usulan — dosen (tertaut akun, perlu persetujuan) maupun non-dosen
 * / mahasiswa (data bebas).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('proposal_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proposal_id')->constrained('proposals')->cascadeOnDelete();

            $table->string('jenis', 20)->default('dosen'); // dosen | mahasiswa | non_dosen

            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->string('nama');
            $table->string('identitas_no')->nullable();   // NIDN / NIM / lainnya
            $table->string('institusi')->nullable();
            $table->string('prodi')->nullable();
            $table->string('jenjang')->nullable();         // S1/S2/S3 (non-dosen)
            $table->text('tugas')->nullable();

            $table->string('status', 20)->default('menunggu'); // menunggu | menyetujui | menolak

            $table->timestamps();

            $table->index(['proposal_id', 'jenis']);
            $table->unique(['proposal_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proposal_members');
    }
};
