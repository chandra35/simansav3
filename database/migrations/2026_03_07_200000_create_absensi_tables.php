<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ============================================
        // TABEL LOKASI ABSENSI
        // ============================================
        Schema::create('absensi_locations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nama'); // "Pos Satpam Utama", "Gerbang Belakang"
            $table->string('kode')->unique(); // "POS-1", "GERBANG-2"
            $table->text('alamat')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->integer('radius_meter')->default(100); // radius GPS yang diizinkan
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        // ============================================
        // TABEL SETTING ABSENSI
        // ============================================
        Schema::create('absensi_settings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type')->default('string'); // string, integer, boolean, time, json
            $table->string('group')->default('general'); // general, waktu, face, kiosk
            $table->string('label'); // Label untuk tampilan
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // ============================================
        // TABEL HARI LIBUR / TANGGAL MERAH
        // ============================================
        Schema::create('hari_liburs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->date('tanggal');
            $table->string('nama'); // "Hari Raya Idul Fitri", "Tahun Baru"
            $table->enum('jenis', ['nasional', 'keagamaan', 'sekolah', 'cuti_bersama'])->default('nasional');
            $table->text('keterangan')->nullable();
            $table->boolean('is_recurring')->default(false); // berulang tiap tahun? (misal Tahun Baru)
            $table->timestamps();

            $table->index('tanggal');
            $table->index('jenis');
        });

        // ============================================
        // TABEL FACE ENCODING
        // ============================================
        Schema::create('face_encodings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->onDelete('cascade');
            $table->string('user_type'); // 'gtk' atau 'siswa'
            $table->json('descriptors'); // Array of 128-float face descriptors (multi-angle)
            $table->json('capture_angles')->nullable(); // ['frontal','kanan','kiri','senyum','kedip']
            $table->integer('total_captures')->default(0);
            $table->float('quality_score', 5, 2)->nullable(); // rata-rata kualitas capture
            $table->boolean('is_active')->default(true);
            $table->boolean('is_verified')->default(false); // admin sudah verifikasi?
            $table->uuid('verified_by')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'user_type']);
            $table->index('is_active');
            $table->foreign('verified_by')->references('id')->on('users')->onDelete('set null');
        });

        // ============================================
        // TABEL ABSENSI UTAMA
        // ============================================
        Schema::create('absensis', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->onDelete('cascade');
            $table->string('user_type'); // 'gtk' atau 'siswa'
            $table->foreignUuid('tahun_pelajaran_id')->nullable()->constrained('tahun_pelajaran')->onDelete('set null');

            // Waktu detail
            $table->date('tanggal');
            $table->timestamp('waktu_masuk')->nullable();
            $table->timestamp('waktu_pulang')->nullable();

            // Status
            $table->enum('status', ['hadir', 'terlambat', 'izin', 'sakit', 'alpa', 'dinas_luar', 'cuti'])->default('alpa');
            $table->enum('status_pulang', ['tepat_waktu', 'pulang_cepat', 'lembur', 'belum_pulang'])->nullable();

            // Metode absensi
            $table->enum('metode_masuk', ['face', 'manual', 'qrcode'])->default('face');
            $table->enum('metode_pulang', ['face', 'manual', 'qrcode'])->nullable();

            // Face recognition data
            $table->float('face_confidence_masuk', 5, 4)->nullable(); // 0.0000 - 1.0000
            $table->float('face_confidence_pulang', 5, 4)->nullable();
            $table->string('foto_masuk')->nullable(); // path foto capture masuk
            $table->string('foto_pulang')->nullable(); // path foto capture pulang

            // Lokasi
            $table->foreignUuid('location_id')->nullable()->constrained('absensi_locations')->onDelete('set null');
            $table->decimal('latitude_masuk', 10, 8)->nullable();
            $table->decimal('longitude_masuk', 11, 8)->nullable();
            $table->decimal('latitude_pulang', 10, 8)->nullable();
            $table->decimal('longitude_pulang', 11, 8)->nullable();

            // Device info
            $table->string('device_masuk')->nullable(); // "Chrome/Windows", "Kiosk-POS1"
            $table->string('ip_masuk')->nullable();
            $table->string('device_pulang')->nullable();
            $table->string('ip_pulang')->nullable();

            // Keterangan
            $table->text('catatan')->nullable();
            $table->string('file_bukti')->nullable(); // upload surat izin dll

            // Audit
            $table->uuid('input_by')->nullable(); // siapa yang input (jika manual)
            $table->uuid('edited_by')->nullable();
            $table->timestamp('edited_at')->nullable();
            $table->text('edit_reason')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes for fast queries
            $table->unique(['user_id', 'tanggal'], 'unique_user_tanggal');
            $table->index(['tanggal', 'status']);
            $table->index(['user_type', 'tanggal']);
            $table->index(['tahun_pelajaran_id', 'tanggal']);
            $table->index('location_id');
            $table->foreign('input_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('edited_by')->references('id')->on('users')->onDelete('set null');
        });

        // ============================================
        // TABEL LOG ABSENSI (AUDIT TRAIL)
        // ============================================
        Schema::create('absensi_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('absensi_id')->constrained('absensis')->onDelete('cascade');
            $table->foreignUuid('user_id')->constrained('users')->onDelete('cascade'); // siapa yang melakukan
            $table->string('action'); // 'created', 'updated', 'deleted', 'status_changed'
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->text('reason')->nullable();
            $table->timestamps();

            $table->index(['absensi_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('absensi_logs');
        Schema::dropIfExists('absensis');
        Schema::dropIfExists('face_encodings');
        Schema::dropIfExists('hari_liburs');
        Schema::dropIfExists('absensi_settings');
        Schema::dropIfExists('absensi_locations');
    }
};
