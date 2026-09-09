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
            // Kisaran biaya yang boleh diajukan dosen pada skema ini.
            $table->decimal('dana_min', 15, 2)->nullable()->after('deskripsi');
            $table->decimal('dana_max', 15, 2)->nullable()->after('dana_min');
            // Syarat/target luaran wajib skema ini (teks bebas, ditampilkan ke dosen).
            $table->text('target_luaran')->nullable()->after('dana_max');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('proposal_schemes', function (Blueprint $table) {
            $table->dropColumn(['dana_min', 'dana_max', 'target_luaran']);
        });
    }
};
