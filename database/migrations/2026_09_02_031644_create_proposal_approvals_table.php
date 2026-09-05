<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('proposal_approvals', function (Blueprint $table) {
            $table->id();

            $table->foreignId('proposal_id')
                ->constrained('proposals')
                ->cascadeOnDelete();

            $table->foreignId('approved_by')
                ->constrained('users')
                ->restrictOnDelete();

            $table->enum('status', ['approved', 'rejected']);
            $table->text('catatan')->nullable();

            $table->timestamp('created_at')->nullable()->useCurrent();

            $table->index(['proposal_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proposal_approvals');
    }
};
