<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('funding_decisions', function (Blueprint $table) {
            $table->id();

            // unique -> relasi one-to-zero-or-one dengan proposals.
            $table->foreignId('proposal_id')
                ->unique()
                ->constrained('proposals')
                ->cascadeOnDelete();

            $table->enum('status_danai', ['didanai', 'didanai_sebagian', 'tidak_didanai']);
            $table->decimal('jumlah_dana', 15, 2)->default(0);
            $table->string('sk_pendanaan')->nullable();   // nomor SK penetapan
            $table->string('file_sk')->nullable();        // path PDF SK (opsional)

            $table->foreignId('decided_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('funding_decisions');
    }
};
