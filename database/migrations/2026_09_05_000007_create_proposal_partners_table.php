<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Mitra usulan (opsional). */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('proposal_partners', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proposal_id')->constrained('proposals')->cascadeOnDelete();

            $table->string('nama_mitra');
            $table->string('institusi')->nullable();
            $table->text('alamat')->nullable();
            $table->string('negara')->nullable()->default('Indonesia');
            $table->string('surel')->nullable();
            $table->string('surat_kesanggupan')->nullable(); // path berkas
            $table->decimal('dana', 15, 2)->default(0);

            $table->timestamps();

            $table->index('proposal_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proposal_partners');
    }
};
