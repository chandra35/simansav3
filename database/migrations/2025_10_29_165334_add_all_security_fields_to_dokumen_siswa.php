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
        Schema::table('dokumen_siswa', function (Blueprint $table) {
            // Add all new security fields
            $table->string('file_uuid')->nullable()->after('siswa_id')->index();
            $table->string('original_name')->nullable()->after('file_path');
            $table->string('tahun_pelajaran')->nullable()->after('keterangan')->index();
            $table->char('kelas_id', 36)->nullable()->after('tahun_pelajaran');
            $table->string('uploaded_by_role')->default('siswa')->after('kelas_id');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending')->after('uploaded_by_role')->index();
            $table->char('approved_by', 36)->nullable()->after('status');
            $table->timestamp('approved_at')->nullable()->after('approved_by');
            $table->timestamp('accessed_at')->nullable()->after('approved_at');
            $table->unsignedInteger('access_count')->default(0)->after('accessed_at');
            
            // Add foreign keys
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('kelas_id')->references('id')->on('kelas')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dokumen_siswa', function (Blueprint $table) {
            // Drop foreign keys first
            $table->dropForeign(['approved_by']);
            $table->dropForeign(['kelas_id']);
            
            // Drop columns
            $table->dropColumn([
                'file_uuid',
                'original_name',
                'tahun_pelajaran',
                'kelas_id',
                'uploaded_by_role',
                'status',
                'approved_by',
                'approved_at',
                'accessed_at',
                'access_count'
            ]);
        });
    }
};
