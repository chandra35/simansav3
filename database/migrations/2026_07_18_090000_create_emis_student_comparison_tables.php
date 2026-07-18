<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('emis_student_syncs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->unsignedBigInteger('institution_id')->nullable()->index();
            $table->string('status', 20)->default('running')->index();
            $table->unsignedInteger('total_pages')->default(0);
            $table->unsignedInteger('processed_pages')->default(0);
            $table->unsignedInteger('total_students')->default(0);
            $table->unsignedInteger('matched_students')->default(0);
            $table->unsignedInteger('different_students')->default(0);
            $table->text('error_message')->nullable();
            $table->foreignUuid('synced_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();
        });

        Schema::create('emis_student_snapshots', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('sync_id')->nullable()->constrained('emis_student_syncs')->nullOnDelete();
            $table->foreignUuid('siswa_id')->nullable()->constrained('siswa')->nullOnDelete();
            $table->unsignedBigInteger('emis_student_id')->unique();
            $table->unsignedBigInteger('learning_activity_id')->nullable();
            $table->string('nisn', 20)->nullable()->index();
            $table->string('full_name')->nullable()->index();
            $table->string('birth_place')->nullable();
            $table->date('birth_date')->nullable();
            $table->string('gender', 30)->nullable();
            $table->unsignedTinyInteger('student_status_id')->nullable();
            $table->string('student_status')->nullable();
            $table->string('status_description')->nullable();
            $table->unsignedTinyInteger('dukcapil_verification_status_id')->nullable();
            $table->boolean('valid_nisn')->nullable();
            $table->string('level_name')->nullable()->index();
            $table->string('study_group_name')->nullable()->index();
            $table->string('major_name')->nullable();
            $table->string('academic_year')->nullable();
            $table->string('academic_year_status')->nullable();
            $table->string('comparison_status', 30)->default('only_emis')->index();
            $table->decimal('name_similarity', 5, 2)->nullable();
            $table->json('comparison_details')->nullable();
            $table->timestamp('synced_at')->nullable()->index();
            $table->timestamps();

            $table->index(['siswa_id', 'comparison_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('emis_student_snapshots');
        Schema::dropIfExists('emis_student_syncs');
    }
};
