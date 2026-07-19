<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    private array $permissions = [
        'view-student-attendance',
        'input-daily-attendance',
        'input-subject-attendance',
        'finalize-student-attendance',
        'edit-final-student-attendance',
        'view-attendance-analytics',
        'view-attendance-counseling',
        'manage-attendance-alerts',
        'view-attendance-audit',
    ];

    public function up(): void
    {
        Schema::table('absensi_siswa_sessions', function (Blueprint $table) {
            $table->string('session_key', 190)->nullable()->after('id');
            $table->string('semester', 20)->nullable()->after('tanggal');
            $table->unsignedTinyInteger('tingkat')->nullable()->after('semester');
            $table->string('kelas_snapshot')->nullable()->after('tingkat');
            $table->string('mapel_snapshot')->nullable()->after('kelas_snapshot');
            $table->string('guru_snapshot')->nullable()->after('mapel_snapshot');
            $table->time('scheduled_start')->nullable()->after('guru_snapshot');
            $table->time('scheduled_end')->nullable()->after('scheduled_start');
            $table->timestamp('finalized_at')->nullable()->after('status');
            $table->timestamp('locked_at')->nullable()->after('finalized_at');
            $table->foreignUuid('finalized_by')->nullable()->after('locked_at')->constrained('users')->nullOnDelete();
            $table->unsignedInteger('version')->default(1)->after('finalized_by');
            $table->text('revision_reason')->nullable()->after('version');
        });

        $usedKeys = [];
        DB::table('absensi_siswa_sessions')->orderBy('created_at')->get()->each(function ($session) use (&$usedKeys) {
            $schedulePart = $session->mode === 'mapel'
                ? ($session->jadwal_pelajaran_id ?: $session->id)
                : 'daily';
            $baseKey = implode(':', [$session->tanggal, $session->kelas_id, $session->mode, $schedulePart]);
            $sessionKey = isset($usedKeys[$baseKey]) ? $baseKey.':'.substr($session->id, 0, 8) : $baseKey;
            $usedKeys[$baseKey] = true;

            $kelas = DB::table('kelas')->where('id', $session->kelas_id)->first();
            $mapel = $session->mapel_id ? DB::table('mata_pelajaran')->where('id', $session->mapel_id)->first() : null;
            $guru = $session->guru_user_id ? DB::table('users')->where('id', $session->guru_user_id)->first() : null;
            $year = $session->tahun_pelajaran_id ? DB::table('tahun_pelajaran')->where('id', $session->tahun_pelajaran_id)->first() : null;
            $finalizerId = $session->updated_by ?: $session->created_by;
            if ($finalizerId && ! DB::table('users')->where('id', $finalizerId)->exists()) {
                $finalizerId = null;
            }

            DB::table('absensi_siswa_sessions')->where('id', $session->id)->update([
                'session_key' => $sessionKey,
                'semester' => $year->semester_aktif ?? null,
                'tingkat' => $kelas->tingkat ?? null,
                'kelas_snapshot' => $kelas->nama_kelas ?? null,
                'mapel_snapshot' => $mapel->nama_mapel ?? null,
                'guru_snapshot' => $guru->name ?? null,
                'finalized_at' => $session->status === 'final' ? ($session->updated_at ?: $session->created_at) : null,
                'locked_at' => $session->status === 'final' ? ($session->updated_at ?: $session->created_at) : null,
                'finalized_by' => $session->status === 'final' ? $finalizerId : null,
            ]);
        });

        Schema::table('absensi_siswa_sessions', function (Blueprint $table) {
            $table->unique('session_key', 'attendance_session_key_unique');
            $table->index(['tahun_pelajaran_id', 'tingkat', 'tanggal'], 'attendance_history_scope_index');
            $table->index(['status', 'locked_at'], 'attendance_workflow_index');
        });

        DB::statement("ALTER TABLE absensi_siswa_records MODIFY status VARCHAR(24) NOT NULL DEFAULT 'hadir'");
        Schema::table('absensi_siswa_records', function (Blueprint $table) {
            $table->unsignedSmallInteger('late_minutes')->nullable()->after('status');
            $table->unsignedSmallInteger('left_early_minutes')->nullable()->after('late_minutes');
            $table->string('source_reference')->nullable()->after('attendance_method');
            $table->index(['siswa_id', 'status', 'checked_at'], 'attendance_student_status_index');
        });

        Schema::create('absensi_siswa_audits', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('session_id')->constrained('absensi_siswa_sessions')->cascadeOnDelete();
            $table->foreignUuid('record_id')->nullable()->constrained('absensi_siswa_records')->nullOnDelete();
            $table->foreignUuid('siswa_id')->nullable()->constrained('siswa')->nullOnDelete();
            $table->foreignUuid('actor_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 40);
            $table->json('before_values')->nullable();
            $table->json('after_values')->nullable();
            $table->text('reason')->nullable();
            $table->ipAddress('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
            $table->index(['session_id', 'created_at']);
            $table->index(['siswa_id', 'created_at']);
            $table->index(['actor_user_id', 'created_at']);
        });

        Schema::create('attendance_alerts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('siswa_id')->constrained('siswa')->cascadeOnDelete();
            $table->foreignUuid('tahun_pelajaran_id')->constrained('tahun_pelajaran')->cascadeOnDelete();
            $table->string('fingerprint', 190)->unique();
            $table->string('rule_code', 60)->index();
            $table->string('severity', 20)->index();
            $table->unsignedTinyInteger('score')->default(0);
            $table->string('title');
            $table->text('explanation');
            $table->json('evidence')->nullable();
            $table->date('period_start');
            $table->date('period_end');
            $table->string('status', 20)->default('new')->index();
            $table->boolean('is_active')->default(true)->index();
            // Nullable menjaga kompatibilitas MySQL/MariaDB lama yang menolak
            // beberapa TIMESTAMP wajib tanpa default pada satu tabel.
            $table->timestamp('first_detected_at')->nullable();
            $table->timestamp('last_detected_at')->nullable();
            $table->foreignUuid('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUuid('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_notes')->nullable();
            $table->timestamps();
            $table->index(['tahun_pelajaran_id', 'is_active', 'severity'], 'attendance_alert_queue_index');
            $table->index(['siswa_id', 'period_end']);
        });

        Schema::create('attendance_analysis_runs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tahun_pelajaran_id')->constrained('tahun_pelajaran')->cascadeOnDelete();
            $table->foreignUuid('actor_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('source', 20)->default('manual');
            $table->string('status', 20)->default('completed');
            $table->json('result')->nullable();
            $table->ipAddress('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
            $table->index(['tahun_pelajaran_id', 'created_at'], 'attendance_analysis_history_index');
        });

        collect($this->permissions)->each(fn ($name) => Permission::firstOrCreate([
            'name' => $name,
            'guard_name' => 'web',
        ]));

        $this->grantPermissions();
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }

    private function grantPermissions(): void
    {
        $grants = [
            'Super Admin' => $this->permissions,
            'Kepala Madrasah' => ['view-student-attendance', 'view-attendance-analytics', 'view-attendance-audit'],
            'WAKA' => ['view-student-attendance', 'input-daily-attendance', 'input-subject-attendance', 'finalize-student-attendance', 'edit-final-student-attendance', 'view-attendance-analytics', 'manage-attendance-alerts', 'view-attendance-audit'],
            'Admin' => ['view-student-attendance', 'input-daily-attendance', 'input-subject-attendance', 'finalize-student-attendance', 'edit-final-student-attendance', 'view-attendance-analytics', 'manage-attendance-alerts', 'view-attendance-audit'],
            'Operator' => ['view-student-attendance', 'input-daily-attendance', 'input-subject-attendance', 'finalize-student-attendance', 'edit-final-student-attendance', 'view-attendance-analytics', 'view-attendance-audit'],
            'BK' => ['view-student-attendance', 'view-attendance-analytics', 'view-attendance-counseling', 'manage-attendance-alerts', 'view-attendance-audit'],
            'Wali Kelas' => ['view-student-attendance', 'input-daily-attendance', 'input-subject-attendance', 'finalize-student-attendance', 'view-attendance-analytics', 'view-attendance-audit'],
            'GTK' => ['view-student-attendance', 'input-subject-attendance', 'finalize-student-attendance', 'view-attendance-analytics'],
        ];

        foreach ($grants as $roleName => $permissionNames) {
            Role::where('name', $roleName)->first()?->givePermissionTo($permissionNames);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_analysis_runs');
        Schema::dropIfExists('attendance_alerts');
        Schema::dropIfExists('absensi_siswa_audits');

        Schema::table('absensi_siswa_records', function (Blueprint $table) {
            $table->dropIndex('attendance_student_status_index');
            $table->dropColumn(['late_minutes', 'left_early_minutes', 'source_reference']);
        });

        Schema::table('absensi_siswa_sessions', function (Blueprint $table) {
            $table->dropUnique('attendance_session_key_unique');
            $table->dropIndex('attendance_history_scope_index');
            $table->dropIndex('attendance_workflow_index');
            $table->dropConstrainedForeignId('finalized_by');
            $table->dropColumn([
                'session_key', 'semester', 'tingkat', 'kelas_snapshot', 'mapel_snapshot',
                'guru_snapshot', 'scheduled_start', 'scheduled_end', 'finalized_at',
                'locked_at', 'version', 'revision_reason',
            ]);
        });

        Permission::whereIn('name', $this->permissions)->delete();
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
