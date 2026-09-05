<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('outputs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('proposal_id')
                ->constrained('proposals')
                ->cascadeOnDelete();

            $table->enum('jenis_luaran', ['publikasi', 'hki', 'produk', 'lainnya']);
            $table->string('judul_luaran')->nullable();
            $table->string('bukti_file')->nullable();       // path PDF/gambar bukti
            $table->string('tautan')->nullable();           // URL publikasi/repositori (opsional)

            $table->enum('status_validasi', ['pending', 'valid', 'revisi'])->default('pending');
            $table->text('catatan_validasi')->nullable();

            $table->foreignId('validated_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamp('validated_at')->nullable();

            $table->timestamps();

            $table->index(['proposal_id', 'status_validasi']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('outputs');
    }
};
