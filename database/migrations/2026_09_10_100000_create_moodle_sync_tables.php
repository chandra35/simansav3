<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('moodle_integrations', function (Blueprint $table) {
            $table->id();
            $table->string('name')->default('Moodle E-Learning MAN 1 Metro');
            $table->string('base_url')->nullable();
            $table->text('webservice_token')->nullable();
            $table->boolean('enabled')->default(false);
            $table->boolean('sync_users')->default(true);
            $table->boolean('sync_cohorts')->default(true);
            $table->boolean('sync_categories')->default(true);
            $table->string('student_email_domain')->default('@man1metro.sch.id');
            $table->timestamp('last_tested_at')->nullable();
            $table->string('last_test_status')->nullable();
            $table->text('last_test_message')->nullable();
            $table->timestamps();
        });

        Schema::create('moodle_sync_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('moodle_integration_id')->constrained('moodle_integrations')->cascadeOnDelete();
            $table->uuid('started_by')->nullable();
            $table->string('type', 40);
            $table->string('status', 20)->default('running');
            $table->json('summary')->nullable();
            $table->text('error')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();
            $table->index(['type', 'status']);
        });
        Schema::table('moodle_sync_runs', function (Blueprint $table) {
            $table->foreign('started_by')->references('id')->on('users')->nullOnDelete();
        });

        Schema::create('moodle_sync_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('moodle_sync_run_id')->constrained()->cascadeOnDelete();
            $table->string('entity_type', 30);
            $table->string('local_id')->nullable();
            $table->string('identifier')->nullable();
            $table->string('action', 30);
            $table->string('status', 20);
            $table->unsignedBigInteger('moodle_id')->nullable();
            $table->text('message')->nullable();
            $table->json('payload')->nullable();
            $table->timestamps();
            $table->index(['entity_type', 'identifier']);
            $table->index(['moodle_sync_run_id', 'status']);
        });

        if (Schema::hasTable('permissions')) {
            foreach ([
                'view-moodle-sync' => 'Lihat integrasi Moodle',
                'manage-moodle-sync' => 'Kelola sinkronisasi Moodle',
            ] as $name => $label) {
                $permissionId = DB::table('permissions')->insertGetId(['name' => $name, 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()]);
                foreach (['Super Admin', 'Admin', 'Operator'] as $roleName) {
                    $role = DB::table('roles')->where('name', $roleName)->where('guard_name', 'web')->first();
                    if ($role) {
                        DB::table('role_has_permissions')->insertOrIgnore(['permission_id' => $permissionId, 'role_id' => $role->id]);
                    }
                }
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('permissions')) {
            $permissionIds = DB::table('permissions')->whereIn('name', ['view-moodle-sync', 'manage-moodle-sync'])->pluck('id');
            DB::table('role_has_permissions')->whereIn('permission_id', $permissionIds)->delete();
            DB::table('permissions')->whereIn('id', $permissionIds)->delete();
        }
        Schema::dropIfExists('moodle_sync_items');
        Schema::dropIfExists('moodle_sync_runs');
        Schema::dropIfExists('moodle_integrations');
    }
};
