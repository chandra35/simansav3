<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE users MODIFY role ENUM('super_admin','admin','gtk','operator','siswa','matrikulasi') NOT NULL DEFAULT 'siswa'");

        Schema::table('matrikulasi_pesertas', function (Blueprint $table) {
            if (!Schema::hasColumn('matrikulasi_pesertas', 'user_id')) {
                $table->uuid('user_id')->nullable()->after('siswa_id');
                $table->timestamp('akun_created_at')->nullable()->after('user_id');
                $table->timestamp('akun_last_reset_at')->nullable()->after('akun_created_at');
                $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
                $table->index(['matrikulasi_periode_id', 'user_id'], 'matrikulasi_peserta_periode_user_idx');
            }
        });
    }

    public function down(): void
    {
        Schema::table('matrikulasi_pesertas', function (Blueprint $table) {
            if (Schema::hasColumn('matrikulasi_pesertas', 'user_id')) {
                $table->dropForeign(['user_id']);
                $table->dropIndex('matrikulasi_peserta_periode_user_idx');
                $table->dropColumn(['user_id', 'akun_created_at', 'akun_last_reset_at']);
            }
        });

        DB::statement("ALTER TABLE users MODIFY role ENUM('super_admin','admin','gtk','operator','siswa') NOT NULL DEFAULT 'siswa'");
    }
};
