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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');

            // Identitas dosen (nullable — hanya relevan untuk aktor "dosen").
            $table->string('nidn', 20)->nullable()->unique();

            // Kontak & gerbang OTP login.
            $table->string('phone_number', 30)->nullable();
            $table->string('otp_code')->nullable();                 // di-hash, bukan plaintext
            $table->timestamp('otp_expires_at')->nullable();
            $table->timestamp('otp_verified_at')->nullable();       // audit: kapan OTP terakhir lolos
            $table->unsignedTinyInteger('otp_attempts')->default(0); // throttle tebakan salah
            $table->timestamp('otp_last_sent_at')->nullable();      // cooldown kirim ulang

            $table->rememberToken();
            $table->timestamps();

            $table->index('phone_number');
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
