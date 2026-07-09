<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('matrikulasi_pesertas', function (Blueprint $table) {
            if (!Schema::hasColumn('matrikulasi_pesertas', 'status_pembayaran')) {
                $table->string('status_pembayaran', 40)
                    ->default('belum_bayar')
                    ->after('status')
                    ->comment('sudah_bayar_ppdb|susulan_bayar|belum_bayar|dibebaskan');
            }

            if (!Schema::hasColumn('matrikulasi_pesertas', 'status_matrikulasi')) {
                $table->string('status_matrikulasi', 40)
                    ->default('terdaftar')
                    ->after('status_pembayaran')
                    ->comment('terdaftar|hadir|tidak_hadir|mengundurkan_diri|siap_ditetapkan|dipromosikan');
            }

            if (!Schema::hasColumn('matrikulasi_pesertas', 'tanggal_hadir_matrikulasi')) {
                $table->date('tanggal_hadir_matrikulasi')->nullable()->after('status_matrikulasi');
            }

            if (!Schema::hasColumn('matrikulasi_pesertas', 'verified_at')) {
                $table->timestamp('verified_at')->nullable()->after('tanggal_hadir_matrikulasi');
            }

            if (!Schema::hasColumn('matrikulasi_pesertas', 'verified_by')) {
                $table->uuid('verified_by')->nullable()->after('verified_at');
                $table->foreign('verified_by')->references('id')->on('users')->nullOnDelete();
            }

            if (!Schema::hasColumn('matrikulasi_pesertas', 'catatan_validasi')) {
                $table->text('catatan_validasi')->nullable()->after('verified_by');
            }
        });

        DB::table('matrikulasi_pesertas')
            ->whereNull('status_pembayaran')
            ->orWhere('status_pembayaran', '')
            ->update(['status_pembayaran' => 'belum_bayar']);

        DB::table('matrikulasi_pesertas')
            ->where('status', 'dipromosikan')
            ->update(['status_matrikulasi' => 'dipromosikan']);

        Schema::table('matrikulasi_pesertas', function (Blueprint $table) {
            $table->index(['matrikulasi_periode_id', 'status_matrikulasi'], 'matrikulasi_peserta_periode_status_local_idx');
            $table->index(['matrikulasi_periode_id', 'status_pembayaran'], 'matrikulasi_peserta_periode_bayar_idx');
        });
    }

    public function down(): void
    {
        Schema::table('matrikulasi_pesertas', function (Blueprint $table) {
            if (Schema::hasColumn('matrikulasi_pesertas', 'status_matrikulasi')) {
                $table->dropIndex('matrikulasi_peserta_periode_status_local_idx');
            }

            if (Schema::hasColumn('matrikulasi_pesertas', 'status_pembayaran')) {
                $table->dropIndex('matrikulasi_peserta_periode_bayar_idx');
            }

            if (Schema::hasColumn('matrikulasi_pesertas', 'verified_by')) {
                $table->dropForeign(['verified_by']);
            }
        });

        Schema::table('matrikulasi_pesertas', function (Blueprint $table) {
            $columns = [
                'catatan_validasi',
                'verified_by',
                'verified_at',
                'tanggal_hadir_matrikulasi',
                'status_matrikulasi',
                'status_pembayaran',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('matrikulasi_pesertas', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
