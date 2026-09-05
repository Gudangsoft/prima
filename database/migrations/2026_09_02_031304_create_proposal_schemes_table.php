<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('proposal_schemes', function (Blueprint $table) {
            $table->id();
            $table->string('nama_skema');
            $table->enum('kategori', ['penelitian', 'pengabdian'])->index();
            $table->text('deskripsi')->nullable();
            $table->boolean('aktif')->default(true)->index();
            $table->timestamps();

            $table->unique(['nama_skema', 'kategori']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proposal_schemes');
    }
};
