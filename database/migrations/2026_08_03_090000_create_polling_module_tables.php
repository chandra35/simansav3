<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    private array $permissions = ['manage-polling', 'view-polling-results'];

    public function up(): void
    {
        Schema::create('pollings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('slug')->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('audience', ['siswa', 'gtk', 'both'])->index();
            $table->enum('status', ['draft', 'published', 'closed'])->default('draft')->index();
            $table->dateTime('starts_at')->index();
            $table->dateTime('ends_at')->index();
            $table->boolean('allow_changes')->default(true);
            $table->boolean('show_results_after_submit')->default(false);
            $table->boolean('require_consent')->default(false);
            $table->text('consent_text')->nullable();
            $table->unsignedSmallInteger('reminder_interval_hours')->default(6);
            $table->timestamp('published_at')->nullable();
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUuid('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('polling_questions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('polling_id')->constrained('pollings')->cascadeOnDelete();
            $table->text('prompt');
            $table->enum('type', ['single', 'multiple', 'short_text', 'long_text', 'yes_no']);
            $table->boolean('is_required')->default(true);
            $table->unsignedSmallInteger('min_selections')->nullable();
            $table->unsignedSmallInteger('max_selections')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
            $table->index(['polling_id', 'sort_order']);
        });

        Schema::create('polling_options', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('polling_question_id')->constrained('polling_questions')->cascadeOnDelete();
            $table->string('label');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
            $table->index(['polling_question_id', 'sort_order'], 'polling_option_order_idx');
        });

        Schema::create('polling_targets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('polling_id')->constrained('pollings')->cascadeOnDelete();
            $table->enum('audience_type', ['siswa', 'gtk'])->index();
            $table->enum('scope_type', ['all', 'tingkat', 'kelas', 'jenis_ptk', 'role']);
            $table->string('scope_value')->nullable();
            $table->timestamps();
            $table->unique(['polling_id', 'audience_type', 'scope_type', 'scope_value'], 'polling_target_unique');
        });

        Schema::create('polling_responses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('polling_id')->constrained('pollings')->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('respondent_type', ['siswa', 'gtk'])->index();
            $table->uuid('respondent_id')->nullable()->index();
            $table->string('respondent_name');
            $table->uuid('class_id')->nullable()->index();
            $table->string('class_name')->nullable();
            $table->unsignedTinyInteger('grade')->nullable()->index();
            $table->dateTime('submitted_at');
            $table->timestamps();
            $table->unique(['polling_id', 'user_id']);
        });

        Schema::create('polling_answers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('polling_response_id')->constrained('polling_responses')->cascadeOnDelete();
            $table->foreignUuid('polling_question_id')->constrained('polling_questions')->cascadeOnDelete();
            $table->text('answer_text')->nullable();
            $table->timestamps();
            $table->unique(['polling_response_id', 'polling_question_id'], 'polling_answer_unique');
        });

        Schema::create('polling_answer_options', function (Blueprint $table) {
            $table->foreignUuid('polling_answer_id')->constrained('polling_answers')->cascadeOnDelete();
            $table->foreignUuid('polling_option_id')->constrained('polling_options')->cascadeOnDelete();
            $table->primary(['polling_answer_id', 'polling_option_id'], 'polling_answer_option_pk');
        });

        Schema::create('polling_notification_states', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('polling_id')->constrained('pollings')->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('last_prompted_at')->nullable();
            $table->timestamp('snoozed_until')->nullable()->index();
            $table->unsignedSmallInteger('dismiss_count')->default(0);
            $table->timestamps();
            $table->unique(['polling_id', 'user_id']);
        });

        $this->registerPermissions();
    }

    public function down(): void
    {
        Schema::dropIfExists('polling_notification_states');
        Schema::dropIfExists('polling_answer_options');
        Schema::dropIfExists('polling_answers');
        Schema::dropIfExists('polling_responses');
        Schema::dropIfExists('polling_targets');
        Schema::dropIfExists('polling_options');
        Schema::dropIfExists('polling_questions');
        Schema::dropIfExists('pollings');

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Permission::query()->whereIn('name', $this->permissions)->where('guard_name', 'web')->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    private function registerPermissions(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        foreach ($this->permissions as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        Role::query()->whereIn('name', ['Super Admin', 'Admin', 'Operator'])
            ->get()->each(fn (Role $role) => $role->givePermissionTo($this->permissions));
        Role::query()->whereIn('name', ['Kepala Madrasah', 'WAKA'])
            ->get()->each(fn (Role $role) => $role->givePermissionTo('view-polling-results'));
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
