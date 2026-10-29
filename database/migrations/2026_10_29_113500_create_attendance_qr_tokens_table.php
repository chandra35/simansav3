<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_qr_tokens', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('context', 20);
            $table->foreignUuid('absensi_siswa_session_id')->nullable()->constrained('absensi_siswa_sessions')->cascadeOnDelete();
            $table->foreignUuid('sholat_session_id')->nullable()->constrained('sholat_sessions')->cascadeOnDelete();
            $table->string('token_hash', 64)->unique();
            $table->timestamp('expires_at')->index();
            $table->foreignUuid('issued_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('revoked_at')->nullable()->index();
            $table->timestamps();

            $table->index(['context', 'absensi_siswa_session_id'], 'attendance_qr_daily_scope');
            $table->index(['context', 'sholat_session_id'], 'attendance_qr_sholat_scope');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_qr_tokens');
    }
};
