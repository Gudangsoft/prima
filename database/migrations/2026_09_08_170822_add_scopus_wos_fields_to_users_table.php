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
        Schema::table('users', function (Blueprint $table) {
            // Data indeksasi Scopus & Web of Science (gaya BIMA) — diisi manual
            // oleh dosen sendiri lewat Profil Saya, karena aplikasi ini tidak
            // terhubung ke API Scopus/WOS.
            $table->string('scopus_id', 50)->nullable()->after('sinta_score_3yr_v3');
            $table->unsignedInteger('scopus_h_index')->nullable()->after('scopus_id');
            $table->unsignedInteger('scopus_articles')->nullable()->after('scopus_h_index');
            $table->unsignedInteger('scopus_citation')->nullable()->after('scopus_articles');
            $table->unsignedInteger('wos_score')->nullable()->after('scopus_citation');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['scopus_id', 'scopus_h_index', 'scopus_articles', 'scopus_citation', 'wos_score']);
        });
    }
};
