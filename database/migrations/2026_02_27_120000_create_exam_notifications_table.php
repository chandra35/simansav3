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
        Schema::create('exam_notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title');
            $table->text('message');
            $table->enum('type', ['info', 'warning', 'urgent'])->default('info');
            $table->enum('target', ['all', 'exam_active'])->default('all')
                  ->comment('all=semua device, exam_active=hanya yang sedang ujian');
            $table->uuid('sent_by')->nullable()->comment('User ID yang mengirim');
            $table->timestamp('scheduled_at')->nullable()->comment('Jadwal kirim, null=langsung');
            $table->timestamp('expires_at')->nullable()->comment('Expired, null=tidak expired');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_notifications');
    }
};
