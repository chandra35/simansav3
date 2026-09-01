<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('lms_assessment_scores', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('siswa_id')->index();
            $table->string('external_event_id', 80)->unique();
            $table->string('assessment_type', 24)->index();
            $table->string('assessment_title', 190);
            $table->string('subject', 190)->nullable();
            $table->decimal('score', 7, 2);
            $table->timestamp('graded_at');
            $table->json('payload')->nullable();
            $table->timestamps();
            $table->foreign('siswa_id')->references('id')->on('siswa')->cascadeOnDelete();
        });
    }

    public function down(): void { Schema::dropIfExists('lms_assessment_scores'); }
};
