<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Target luaran per urutan tahun (bagian "Substansi dan Luaran"). */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('proposal_output_targets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proposal_id')->constrained('proposals')->cascadeOnDelete();

            $table->unsignedTinyInteger('tahun_ke')->default(1);
            $table->string('kelompok_luaran');     // "Artikel di Jurnal"
            $table->string('jenis_luaran');        // "Artikel di Jurnal Bereputasi Internasional"
            $table->string('target')->nullable();  // "Accepted/Published"
            $table->text('keterangan')->nullable();

            $table->timestamps();

            $table->index(['proposal_id', 'tahun_ke']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proposal_output_targets');
    }
};
