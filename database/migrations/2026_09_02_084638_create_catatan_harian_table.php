<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('catatan_harian', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proposal_id')->constrained('proposals')->cascadeOnDelete();
            $table->date('tanggal');
            $table->text('kegiatan');
            $table->text('capaian')->nullable();
            $table->unsignedTinyInteger('persentase')->nullable(); // progres kumulatif 0-100
            $table->string('berkas')->nullable();                   // path PDF di disk public
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['proposal_id', 'tanggal']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('catatan_harian');
    }
};
