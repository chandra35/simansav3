<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('moodle_check_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('moodle_integration_id')->constrained('moodle_integrations')->cascadeOnDelete();
            $table->foreignUuid('checked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('subject', 20); // students atau gtk
            $table->string('status', 20)->default('success');
            $table->json('summary')->nullable();
            $table->text('error')->nullable();
            $table->timestamp('checked_at')->nullable();
            $table->timestamps();
            $table->index(['moodle_integration_id', 'subject', 'checked_at']);
        });

        Schema::create('moodle_check_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('moodle_check_run_id')->constrained()->cascadeOnDelete();
            $table->string('local_id')->nullable();
            $table->string('identifier')->nullable();
            $table->string('local_name')->nullable();
            $table->string('local_group')->nullable();
            $table->string('moodle_name')->nullable();
            $table->string('moodle_email')->nullable();
            $table->string('status', 30);
            $table->json('payload')->nullable();
            $table->timestamps();
            $table->index(['moodle_check_run_id', 'status']);
            $table->index(['identifier', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('moodle_check_results');
        Schema::dropIfExists('moodle_check_runs');
    }
};
