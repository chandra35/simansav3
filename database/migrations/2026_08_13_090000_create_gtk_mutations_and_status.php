<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gtks', function (Blueprint $table) {
            $table->boolean('status_aktif')->default(true)->after('tmt_kerja')->index();
            $table->string('alasan_nonaktif', 40)->nullable()->after('status_aktif')->index();
            $table->date('tanggal_status')->nullable()->after('alasan_nonaktif');
            $table->text('status_keterangan')->nullable()->after('tanggal_status');
        });

        DB::table('gtks')->leftJoin('users', 'users.id', '=', 'gtks.user_id')->update([
            'gtks.status_aktif' => DB::raw('COALESCE(users.is_active, 1)'),
            'gtks.tanggal_status' => DB::raw('DATE(COALESCE(gtks.updated_at, gtks.created_at, NOW()))'),
        ]);

        Schema::create('mutasi_gtk', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('gtk_id');
            $table->boolean('status_sebelumnya');
            $table->boolean('status_baru')->index();
            $table->string('alasan', 40)->index();
            $table->date('tanggal_efektif')->index();
            $table->string('instansi_asal_tujuan')->nullable();
            $table->text('keterangan')->nullable();
            $table->json('dampak_operasional')->nullable();
            $table->uuid('created_by')->nullable();
            $table->timestamps();

            $table->foreign('gtk_id')->references('id')->on('gtks')->cascadeOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->index(['gtk_id', 'tanggal_efektif']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mutasi_gtk');
        Schema::table('gtks', function (Blueprint $table) {
            $table->dropColumn(['status_aktif', 'alasan_nonaktif', 'tanggal_status', 'status_keterangan']);
        });
    }
};
