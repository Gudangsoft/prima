<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Rincian Rancangan Anggaran Biaya (RAB) per tahun. Total = harga_satuan * volume. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('proposal_rab_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proposal_id')->constrained('proposals')->cascadeOnDelete();

            $table->unsignedTinyInteger('tahun_ke')->default(1);
            $table->string('kelompok');       // "Bahan", "Analisis Data", "Sewa Peralatan", ...
            $table->string('komponen');
            $table->string('item');
            $table->string('satuan', 40);     // "Unit", "OJ", "OH", "Paket", ...
            $table->decimal('harga_satuan', 15, 2)->default(0);
            $table->decimal('volume', 10, 2)->default(0);

            $table->timestamps();

            $table->index(['proposal_id', 'tahun_ke']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proposal_rab_items');
    }
};
