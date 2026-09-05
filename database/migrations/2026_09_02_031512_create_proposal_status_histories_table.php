<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('proposal_status_histories', function (Blueprint $table) {
            $table->id();

            $table->foreignId('proposal_id')
                ->constrained('proposals')
                ->cascadeOnDelete();

            $table->foreignId('changed_by')      // user pemicu transisi; null bila sistem
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('status', 30);
            $table->text('catatan')->nullable();

            // Spesifikasi hanya meminta created_at (tabel append-only, tidak pernah di-update).
            $table->timestamp('created_at')->nullable()->useCurrent();

            $table->index(['proposal_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proposal_status_histories');
    }
};
