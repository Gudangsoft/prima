<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Field "Identitas Usulan" gaya BIMA. Semua nullable agar tidak memutus data
 * lama; wajib-nya ditegakkan di form wizard, bukan di skema.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('proposals', function (Blueprint $table) {
            $table->string('kelompok_skema')->nullable()->after('scheme_id');   // "Riset Dasar"
            $table->string('bidang_fokus')->nullable()->after('kelompok_skema');
            $table->string('tema')->nullable()->after('bidang_fokus');
            $table->string('topik')->nullable()->after('tema');
            $table->string('rumpun_ilmu')->nullable()->after('topik');
            $table->unsignedTinyInteger('target_tkt')->nullable()->after('rumpun_ilmu');
            $table->unsignedTinyInteger('lama_kegiatan')->default(1)->after('target_tkt'); // tahun
            $table->unsignedSmallInteger('tahun_usulan')->nullable()->after('tahun_anggaran');
            $table->string('makro_riset')->nullable()->after('tahun_usulan');
            $table->string('substansi_file')->nullable()->after('file_proposal'); // PDF substansi
        });
    }

    public function down(): void
    {
        Schema::table('proposals', function (Blueprint $table) {
            $table->dropColumn([
                'kelompok_skema', 'bidang_fokus', 'tema', 'topik', 'rumpun_ilmu',
                'target_tkt', 'lama_kegiatan', 'tahun_usulan', 'makro_riset', 'substansi_file',
            ]);
        });
    }
};
