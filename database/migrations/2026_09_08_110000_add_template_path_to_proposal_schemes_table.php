<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('proposal_schemes', function (Blueprint $table) {
            $table->string('template_path')->nullable()->after('deskripsi'); // path di disk public
        });
    }

    public function down(): void
    {
        Schema::table('proposal_schemes', function (Blueprint $table) {
            $table->dropColumn('template_path');
        });
    }
};
