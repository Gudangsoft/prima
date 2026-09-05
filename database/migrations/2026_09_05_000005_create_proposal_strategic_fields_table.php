<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Bagian "8 Bidang Strategis" — rumusan masalah & uraian kegiatan per bidang. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('proposal_strategic_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proposal_id')->constrained('proposals')->cascadeOnDelete();

            $table->string('bidang');
            $table->text('rumusan_masalah')->nullable();
            $table->text('uraian_kegiatan')->nullable();

            $table->timestamps();

            $table->index('proposal_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proposal_strategic_fields');
    }
};
