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
        Schema::create('proposal_scheme_luarans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proposal_scheme_id')->constrained()->cascadeOnDelete();
            $table->string('jenis_luaran');
            // true = Luaran Wajib, false = Luaran Tambahan (opsional bagi dosen).
            $table->boolean('wajib')->default(true)->index();
            $table->string('keterangan')->nullable();
            $table->unsignedSmallInteger('urutan')->default(0);
            $table->timestamps();
        });

        // Diganti struktur wajib/tambahan di atas — kolom teks bebas lama tak
        // dipakai lagi (fitur baru saja ditambahkan, belum ada data produksi).
        Schema::table('proposal_schemes', function (Blueprint $table) {
            $table->dropColumn('target_luaran');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('proposal_schemes', function (Blueprint $table) {
            $table->text('target_luaran')->nullable()->after('dana_max');
        });

        Schema::dropIfExists('proposal_scheme_luarans');
    }
};
