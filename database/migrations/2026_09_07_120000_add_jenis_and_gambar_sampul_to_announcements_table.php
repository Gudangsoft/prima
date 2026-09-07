<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            $table->string('jenis')->default('pengumuman')->after('judul');
            $table->string('gambar_sampul')->nullable()->after('lampiran_pdf'); // path di disk public, khusus Berita

            $table->index(['jenis', 'terbit', 'tanggal_terbit']);
        });
    }

    public function down(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            $table->dropIndex(['jenis', 'terbit', 'tanggal_terbit']);
            $table->dropColumn(['jenis', 'gambar_sampul']);
        });
    }
};
