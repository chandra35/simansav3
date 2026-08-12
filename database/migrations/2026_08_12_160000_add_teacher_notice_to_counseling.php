<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('catatan_konseling', function (Blueprint $table) {
            $table->boolean('share_with_teachers')->default(false)->after('is_confidential');
            $table->text('teacher_notice')->nullable()->after('share_with_teachers');
            $table->index(['share_with_teachers', 'status'], 'counseling_teacher_notice_idx');
        });
    }

    public function down(): void
    {
        Schema::table('catatan_konseling', function (Blueprint $table) {
            $table->dropIndex('counseling_teacher_notice_idx');
            $table->dropColumn(['share_with_teachers', 'teacher_notice']);
        });
    }
};
