<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('pendidikan_terakhir', 10)->nullable()->after('jabatan'); // S1/S2/S3, dst.

            $table->decimal('sinta_score_overall_v2', 10, 2)->nullable()->after('sinta_id');
            $table->decimal('sinta_score_3yr_v2', 10, 2)->nullable()->after('sinta_score_overall_v2');
            $table->decimal('sinta_score_overall_v3', 10, 2)->nullable()->after('sinta_score_3yr_v2');
            $table->decimal('sinta_score_3yr_v3', 10, 2)->nullable()->after('sinta_score_overall_v3');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'pendidikan_terakhir',
                'sinta_score_overall_v2',
                'sinta_score_3yr_v2',
                'sinta_score_overall_v3',
                'sinta_score_3yr_v3',
            ]);
        });
    }
};
