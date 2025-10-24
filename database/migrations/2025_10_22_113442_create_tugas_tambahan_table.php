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
        Schema::create('tugas_tambahan', function (Blueprint $table) {
            $table->id();
            
            // Foreign keys
            $table->uuid('user_id')->comment('User yang diberi tugas tambahan');
            $table->unsignedBigInteger('role_id')->comment('Role tugas tambahan (Kepala, WAKA, Admin, dll)');
            
            // Status & Period
            $table->boolean('is_active')->default(1)->comment('Apakah tugas tambahan masih aktif');
            $table->date('mulai_tugas')->nullable()->comment('Tanggal mulai tugas tambahan');
            $table->date('selesai_tugas')->nullable()->comment('Tanggal selesai tugas tambahan (null = masih aktif)');
            
            // SK (Surat Keputusan)
            $table->string('sk_number', 100)->nullable()->comment('Nomor SK pengangkatan');
            $table->date('sk_date')->nullable()->comment('Tanggal SK');
            
            // Additional info
            $table->text('keterangan')->nullable()->comment('Keterangan tambahan');
            
            // Audit fields
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            
            $table->timestamps();
            
            // Foreign key constraints
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
            
            $table->foreign('role_id')
                ->references('id')
                ->on('roles')
                ->onDelete('cascade');
            
            $table->foreign('created_by')
                ->references('id')
                ->on('users')
                ->onDelete('set null');
            
            $table->foreign('updated_by')
                ->references('id')
                ->on('users')
                ->onDelete('set null');
            
            // Indexes for better query performance
            $table->index(['user_id', 'is_active']);
            $table->index(['role_id', 'is_active']);
            $table->index('mulai_tugas');
            $table->index('selesai_tugas');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tugas_tambahan');
    }
};
