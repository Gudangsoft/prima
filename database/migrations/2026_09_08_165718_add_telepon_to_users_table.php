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
            // Nomor telepon kantor/rumah, terpisah dari phone_number (HP/WhatsApp) —
            // melengkapi field "Nomor Telepon" pada Detail Profil gaya BIMA.
            $table->string('telepon', 30)->nullable()->after('phone_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('telepon');
        });
    }
};
