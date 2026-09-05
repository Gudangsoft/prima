<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->string('judul');
            $table->text('isi');
            $table->date('tanggal_terbit');
            $table->string('lampiran_pdf')->nullable();   // path di disk public
            $table->boolean('disematkan')->default(false); // pinned
            $table->boolean('terbit')->default(true);      // published
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['terbit', 'tanggal_terbit']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('announcements');
    }
};
