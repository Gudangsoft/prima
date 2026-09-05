<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('proposal_reviews', function (Blueprint $table) {
            $table->id();

            $table->foreignId('proposal_id')
                ->constrained('proposals')
                ->cascadeOnDelete();

            $table->foreignId('reviewer_id')
                ->constrained('users')
                ->restrictOnDelete();

            $table->unsignedTinyInteger('skor')->nullable();          // 0-100, null selama belum dinilai
            $table->enum('rekomendasi', ['danai', 'tolak', 'revisi'])->nullable();
            $table->text('catatan')->nullable();

            $table->timestamp('submitted_at')->nullable();            // null = penugasan belum diisi
            $table->timestamps();

            // Satu reviewer hanya sekali per usulan.
            $table->unique(['proposal_id', 'reviewer_id']);
            $table->index('reviewer_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proposal_reviews');
    }
};
