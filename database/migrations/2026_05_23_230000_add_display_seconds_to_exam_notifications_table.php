<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exam_notifications', function (Blueprint $table) {
            $table->unsignedSmallInteger('display_seconds')
                ->default(10)
                ->after('message')
                ->comment('Durasi overlay di app saat notifikasi diterima dalam keadaan foreground');
        });
    }

    public function down(): void
    {
        Schema::table('exam_notifications', function (Blueprint $table) {
            $table->dropColumn('display_seconds');
        });
    }
};