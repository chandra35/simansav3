<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asrama_units', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('kode', 30)->unique();
            $table->string('nama');
            $table->enum('jenis', ['putra', 'putri', 'campuran'])->default('campuran');
            $table->foreignUuid('kepala_gtk_id')->nullable()->constrained('gtks')->nullOnDelete();
            $table->text('alamat')->nullable();
            $table->string('telepon', 30)->nullable();
            $table->text('deskripsi')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('asrama_santri', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('asrama_id')->constrained('asrama_units')->cascadeOnDelete();
            $table->foreignUuid('siswa_id')->constrained('siswa')->cascadeOnDelete();
            $table->string('nomor_induk_asrama', 50)->unique();
            $table->date('tanggal_masuk')->nullable();
            $table->date('tanggal_keluar')->nullable();
            $table->enum('status', ['aktif', 'nonaktif'])->default('aktif')->index();
            $table->text('catatan')->nullable();
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUuid('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['asrama_id', 'siswa_id']);
        });

        Schema::create('asrama_asatidz', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('asrama_id')->constrained('asrama_units')->cascadeOnDelete();
            $table->foreignUuid('gtk_id')->constrained('gtks')->cascadeOnDelete();
            $table->string('nomor_identitas', 50)->nullable();
            $table->string('jabatan', 100)->default('Asatidz');
            $table->date('tanggal_mulai')->nullable();
            $table->date('tanggal_selesai')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->text('catatan')->nullable();
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUuid('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['asrama_id', 'gtk_id']);
        });

        Schema::create('asrama_kelas', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('asrama_id')->constrained('asrama_units')->cascadeOnDelete();
            $table->foreignUuid('tahun_pelajaran_id')->constrained('tahun_pelajaran')->cascadeOnDelete();
            $table->string('nama_kelas', 100);
            $table->string('nama_arab')->nullable();
            $table->unsignedTinyInteger('tingkat')->nullable();
            $table->enum('jenis', ['putra', 'putri', 'campuran'])->default('campuran');
            $table->foreignUuid('wali_asatidz_id')->nullable()->constrained('asrama_asatidz')->nullOnDelete();
            $table->unsignedSmallInteger('kapasitas')->default(40);
            $table->string('ruang')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->text('deskripsi')->nullable();
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUuid('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['asrama_id', 'tahun_pelajaran_id', 'nama_kelas']);
        });

        Schema::create('asrama_kelas_santri', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('asrama_kelas_id')->constrained('asrama_kelas')->cascadeOnDelete();
            $table->foreignUuid('asrama_santri_id')->constrained('asrama_santri')->cascadeOnDelete();
            $table->unsignedSmallInteger('nomor_urut')->nullable();
            $table->boolean('is_ketua_kelas')->default(false);
            $table->date('tanggal_masuk')->nullable();
            $table->date('tanggal_keluar')->nullable();
            $table->enum('status', ['aktif', 'keluar'])->default('aktif')->index();
            $table->foreignUuid('ditetapkan_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['asrama_kelas_id', 'asrama_santri_id'], 'asrama_kelas_santri_unique');
        });

        Schema::create('asrama_mapel', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('asrama_id')->nullable()->constrained('asrama_units')->cascadeOnDelete();
            $table->string('kode', 30)->unique();
            $table->string('nama_latin');
            $table->string('nama_arab');
            $table->string('kategori', 80)->nullable();
            $table->decimal('skala_maksimum', 5, 2)->default(10);
            $table->decimal('nilai_minimum', 5, 2)->nullable();
            $table->unsignedSmallInteger('urutan')->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->text('deskripsi')->nullable();
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUuid('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('asrama_pengampu', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('asrama_kelas_id')->constrained('asrama_kelas')->cascadeOnDelete();
            $table->foreignUuid('asrama_mapel_id')->constrained('asrama_mapel')->cascadeOnDelete();
            $table->foreignUuid('asrama_asatidz_id')->constrained('asrama_asatidz')->cascadeOnDelete();
            $table->enum('semester', ['Ganjil', 'Genap']);
            $table->boolean('is_active')->default(true)->index();
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUuid('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(
                ['asrama_kelas_id', 'asrama_mapel_id', 'asrama_asatidz_id', 'semester'],
                'asrama_pengampu_unique'
            );
        });

        Schema::create('asrama_nilai', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('asrama_pengampu_id')->constrained('asrama_pengampu')->cascadeOnDelete();
            $table->foreignUuid('asrama_kelas_santri_id')->constrained('asrama_kelas_santri')->cascadeOnDelete();
            $table->decimal('nilai', 5, 2)->nullable();
            $table->text('catatan')->nullable();
            $table->foreignUuid('input_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('input_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(
                ['asrama_pengampu_id', 'asrama_kelas_santri_id'],
                'asrama_nilai_unique'
            );
        });

        Schema::create('asrama_rapor', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('asrama_kelas_santri_id')->constrained('asrama_kelas_santri')->cascadeOnDelete();
            $table->enum('semester', ['Ganjil', 'Genap']);
            $table->decimal('nilai_kebersihan', 5, 2)->nullable();
            $table->decimal('nilai_kelakuan', 5, 2)->nullable();
            $table->decimal('nilai_kerajinan', 5, 2)->nullable();
            $table->unsignedSmallInteger('sakit')->default(0);
            $table->unsignedSmallInteger('izin')->default(0);
            $table->unsignedSmallInteger('lain_lain')->default(0);
            $table->string('predikat', 80)->nullable();
            $table->string('keputusan', 80)->nullable();
            $table->text('catatan_wali')->nullable();
            $table->date('tanggal_rapor')->nullable();
            $table->string('tanggal_hijriah')->nullable();
            $table->enum('status', ['draft', 'terbit'])->default('draft')->index();
            $table->json('snapshot')->nullable();
            $table->foreignUuid('published_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->unique(['asrama_kelas_santri_id', 'semester'], 'asrama_rapor_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asrama_rapor');
        Schema::dropIfExists('asrama_nilai');
        Schema::dropIfExists('asrama_pengampu');
        Schema::dropIfExists('asrama_mapel');
        Schema::dropIfExists('asrama_kelas_santri');
        Schema::dropIfExists('asrama_kelas');
        Schema::dropIfExists('asrama_asatidz');
        Schema::dropIfExists('asrama_santri');
        Schema::dropIfExists('asrama_units');
    }
};
