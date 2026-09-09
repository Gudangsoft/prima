<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('proposal_schemes', function (Blueprint $table) {
            // Periode pengajuan (gaya "Buka Usulan" BIMA) — opsional; kosong berarti
            // terbuka selama toggle "aktif" menyala, tanpa batas tanggal otomatis.
            $table->date('tanggal_buka')->nullable()->after('aktif');
            $table->date('tanggal_tutup')->nullable()->after('tanggal_buka');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('proposal_schemes', function (Blueprint $table) {
            $table->dropColumn(['tanggal_buka', 'tanggal_tutup']);
        });
    }
};
