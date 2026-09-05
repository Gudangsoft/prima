<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('monev_internal', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proposal_id')->unique()->constrained('proposals')->cascadeOnDelete();
            $table->foreignId('penilai_id')->nullable()->constrained('users')->nullOnDelete(); // tim monev PT
            $table->date('tanggal_monev');
            $table->unsignedTinyInteger('skor_capaian')->nullable();     // 0-100
            $table->text('catatan')->nullable();
            $table->enum('rekomendasi', ['lanjut', 'lanjut_perbaikan', 'dihentikan'])->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('monev_internal');
    }
};
