<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('monitoring_reports', function (Blueprint $table) {
            $table->id();

            $table->foreignId('proposal_id')
                ->constrained('proposals')
                ->cascadeOnDelete();

            $table->enum('jenis', ['kemajuan', 'akhir']);
            $table->string('file_laporan');                 // path PDF
            $table->text('ringkasan')->nullable();
            $table->date('tanggal_submit');

            $table->foreignId('submitted_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->index(['proposal_id', 'jenis']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('monitoring_reports');
    }
};
