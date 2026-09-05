<?php

use App\Enums\ProposalStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('proposals', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')          // dosen pengusul
                ->constrained('users')
                ->restrictOnDelete();

            $table->foreignId('scheme_id')
                ->constrained('proposal_schemes')
                ->restrictOnDelete();

            $table->string('judul', 500);
            $table->text('abstrak');
            $table->string('file_proposal')->nullable(); // path PDF; null selama draf belum unggah

            $table->string('status', 30)->default(ProposalStatus::Draft->value);
            $table->unsignedSmallInteger('tahun_anggaran');

            $table->timestamps();

            $table->index('status');
            $table->index('tahun_anggaran');
            $table->index(['status', 'tahun_anggaran']);
            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proposals');
    }
};
