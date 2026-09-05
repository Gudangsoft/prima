<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('program_studi', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 20)->unique();   // kode prodi (mis. PDDIKTI)
            $table->string('nama');
            $table->enum('jenjang', ['D3', 'D4', 'S1', 'S2', 'S3', 'Profesi'])->default('S1');
            $table->string('fakultas')->nullable();
            $table->boolean('aktif')->default(true);
            $table->timestamps();

            $table->index('aktif');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('program_studi');
    }
};
