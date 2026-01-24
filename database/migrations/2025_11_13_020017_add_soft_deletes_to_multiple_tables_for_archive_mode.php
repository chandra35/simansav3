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
        // Add soft deletes to Siswa
        if (!Schema::hasColumn('siswa', 'deleted_at')) {
            Schema::table('siswa', function (Blueprint $table) {
                $table->softDeletes();
            });
        }

        // Add soft deletes to Ortu
        if (!Schema::hasColumn('ortu', 'deleted_at')) {
            Schema::table('ortu', function (Blueprint $table) {
                $table->softDeletes();
            });
        }

        // Add soft deletes to DokumenSiswa
        if (!Schema::hasColumn('dokumen_siswa', 'deleted_at')) {
            Schema::table('dokumen_siswa', function (Blueprint $table) {
                $table->softDeletes();
            });
        }

        // Add soft deletes to SiswaKelas
        if (!Schema::hasColumn('siswa_kelas', 'deleted_at')) {
            Schema::table('siswa_kelas', function (Blueprint $table) {
                $table->softDeletes();
            });
        }

        // Add soft deletes to MutasiSiswa
        if (!Schema::hasColumn('mutasi_siswa', 'deleted_at')) {
            Schema::table('mutasi_siswa', function (Blueprint $table) {
                $table->softDeletes();
            });
        }

        // Add soft deletes to GTK
        if (!Schema::hasColumn('gtks', 'deleted_at')) {
            Schema::table('gtks', function (Blueprint $table) {
                $table->softDeletes();
            });
        }

        // Add soft deletes to TugasTambahan
        if (!Schema::hasColumn('tugas_tambahan', 'deleted_at')) {
            Schema::table('tugas_tambahan', function (Blueprint $table) {
                $table->softDeletes();
            });
        }

        // Add soft deletes to Kelas
        if (!Schema::hasColumn('kelas', 'deleted_at')) {
            Schema::table('kelas', function (Blueprint $table) {
                $table->softDeletes();
            });
        }

        // Add soft deletes to TahunPelajaran
        if (!Schema::hasColumn('tahun_pelajaran', 'deleted_at')) {
            Schema::table('tahun_pelajaran', function (Blueprint $table) {
                $table->softDeletes();
            });
        }

        // Add soft deletes to Kurikulum
        if (!Schema::hasColumn('kurikulum', 'deleted_at')) {
            Schema::table('kurikulum', function (Blueprint $table) {
                $table->softDeletes();
            });
        }

        // Add soft deletes to Jurusan
        if (!Schema::hasColumn('jurusan', 'deleted_at')) {
            Schema::table('jurusan', function (Blueprint $table) {
                $table->softDeletes();
            });
        }

        // Add soft deletes to Users (for siswa/gtk user accounts)
        if (!Schema::hasColumn('users', 'deleted_at')) {
            Schema::table('users', function (Blueprint $table) {
                $table->softDeletes();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('siswa', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('ortu', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('dokumen_siswa', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('siswa_kelas', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('mutasi_siswa', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('gtks', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('tugas_tambahan', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('kelas', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('tahun_pelajaran', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('kurikulum', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('jurusan', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
