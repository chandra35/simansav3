<?php

namespace App\Services;

use App\Models\Gtk;
use App\Models\Kelas;
use App\Models\MoodleIntegration;
use App\Models\MoodleSyncItem;
use App\Models\MoodleSyncRun;
use App\Models\Siswa;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use RuntimeException;

class MoodleSyncService
{
    public function test(MoodleIntegration $integration): array
    {
        $result = $this->call($integration, 'core_webservice_get_site_info');
        $integration->update(['last_tested_at' => now(), 'last_test_status' => 'success', 'last_test_message' => 'Terhubung ke '.($result['sitename'] ?? 'Moodle')]);
        return $result;
    }

    public function preview(): array
    {
        $students = Siswa::query()->whereNotNull('nisn')->where('nisn', '!=', '')->count();
        $gtk = Gtk::query()->whereNotNull('nik')->where('nik', '!=', '')->count();
        $cohorts = Kelas::query()->where('is_active', true)->whereNotNull('nama_kelas')->count();
        $members = Kelas::query()->where('is_active', true)->whereNotNull('nama_kelas')->withCount('siswaAktif')->get()->sum('siswa_aktif_count');
        $years = Kelas::query()->where('is_active', true)->distinct('tahun_pelajaran_id')->count('tahun_pelajaran_id');

        return [
            'students' => $students, 'gtk' => $gtk, 'cohorts' => $cohorts, 'members' => $members,
            'categories' => $years, 'category_items' => $years + $cohorts,
            'total_users' => $students + $gtk, 'total_cohorts' => $cohorts + $members,
            'total_categories' => $years + $cohorts,
        ];
    }

    /**
     * Read-only comparison. This method must never call a Moodle write function.
     */
    public function comparePreview(MoodleIntegration $integration, string $type): array
    {
        $local = $this->preview();
        $plan = [
            'users' => ['create' => 0, 'update' => 0, 'unchanged' => 0, 'missing' => 0],
            'cohorts' => ['create' => 0, 'update' => 0, 'unchanged' => 0, 'membership_add' => 0, 'membership_extra' => 0, 'details' => []],
            'categories' => ['create' => 0, 'update' => 0, 'unchanged' => 0],
            'membership_comparable' => true,
        ];

        if ($type === 'users' || $type === 'all') {
            $desired = collect();
            Siswa::with(['user', 'kelasSaatIni'])->whereNotNull('nisn')->where('nisn', '!=', '')->get()->each(function ($student) use (&$desired, $integration) {
                $desired->push(['username' => trim($student->nisn), 'firstname' => $student->nama_lengkap, 'lastname' => $student->kelasSaatIni?->nama_kelas ?: ($student->status_siswa === 'alumni' ? 'Alumni' : 'Siswa'), 'email' => $student->user?->email ?: trim($student->nisn).trim($integration->student_email_domain)]);
            });
            Gtk::whereNotNull('nik')->where('nik', '!=', '')->get()->each(function ($gtk) use (&$desired) {
                $desired->push(['username' => trim($gtk->nik), 'firstname' => $gtk->nama_lengkap, 'lastname' => $gtk->jenis_ptk ?: 'GTK', 'email' => $gtk->email ?: trim($gtk->nik).'@man1metro.sch.id']);
            });
            $existing = collect($this->call($integration, 'core_user_get_users_by_field', ['field' => 'username', 'values' => $desired->pluck('username')->values()->all()]))->keyBy(fn ($row) => (string) ($row['username'] ?? ''));
            foreach ($desired as $item) {
                $found = $existing->get($item['username']);
                if (!$found) { $plan['users']['create']++; continue; }
                $same = ($found['firstname'] ?? '') === ($item['firstname'] ?? '') && ($found['lastname'] ?? '') === ($item['lastname'] ?? '') && ($found['email'] ?? '') === ($item['email'] ?? '');
                $same ? $plan['users']['unchanged']++ : $plan['users']['update']++;
            }
            $plan['users']['missing'] = $plan['users']['create'];
        }

        $classes = Kelas::with(['tahunPelajaran', 'siswaAktif'])->where('is_active', true)->whereNotNull('nama_kelas')->get();
        $existingCohorts = collect();
        if ($type === 'cohorts' || $type === 'all') {
            $existingCohorts = collect($this->call($integration, 'core_cohort_get_cohorts', ['cohortids' => []]))->keyBy(fn ($row) => (string) ($row['idnumber'] ?? ''));
            foreach ($classes as $class) {
                $idnumber = 'simansa-kelas-'.$class->id;
                $description = 'Rombel SIMANSA '.($class->tahunPelajaran?->nama ?: '');
                $found = $existingCohorts->get($idnumber);
                $metadataAction = !$found ? 'create' : ((($found['name'] ?? '') !== $class->nama_kelas || trim(strip_tags($found['description'] ?? '')) !== trim($description)) ? 'update' : 'unchanged');
                $plan['cohorts'][$metadataAction]++;
                $plan['cohorts']['details'][$idnumber] = ['idnumber' => $idnumber, 'local_name' => $class->nama_kelas, 'moodle_name' => $found['name'] ?? null, 'metadata_action' => $metadataAction, 'local_members' => $class->siswaAktif->whereNotNull('nisn')->count(), 'moodle_members' => 0, 'membership_add' => 0, 'membership_extra' => 0];
            }
            try {
                $memberRows = collect($this->call($integration, 'core_cohort_get_cohort_members', ['cohortids' => $existingCohorts->pluck('id')->filter()->map(fn ($id) => (int) $id)->values()->all()]))->keyBy(fn ($row) => (string) ($row['cohortid'] ?? ''));
                $usernames = $classes->flatMap(fn ($class) => $class->siswaAktif->pluck('nisn'))->filter()->map(fn ($value) => trim($value))->unique()->values()->all();
                $users = collect($this->call($integration, 'core_user_get_users_by_field', ['field' => 'username', 'values' => $usernames]))->keyBy(fn ($row) => (string) ($row['username'] ?? ''));
                foreach ($classes as $class) {
                    $found = $existingCohorts->get('simansa-kelas-'.$class->id);
                    if (!$found) { $plan['cohorts']['membership_add'] += $class->siswaAktif->whereNotNull('nisn')->count(); $plan['cohorts']['details'][$idnumber]['membership_add'] = $class->siswaAktif->whereNotNull('nisn')->count(); continue; }
                    $wanted = $class->siswaAktif->pluck('nisn')->filter()->map(fn ($value) => (int) data_get($users->get(trim($value)), 'id', 0))->filter()->values()->all();
                    $actual = collect(data_get($memberRows->get((string) $found['id']), 'userids', []))->map(fn ($id) => (int) $id);
                    $toAdd = collect($wanted)->diff($actual)->count();
                    $extra = $actual->diff($wanted)->count();
                    $plan['cohorts']['membership_add'] += $toAdd;
                    $managed = collect($users->pluck('id')->filter()->map(fn ($id) => (int) $id));
                    $extra = $actual->diff($wanted)->intersect($managed)->count();
                    $plan['cohorts']['membership_extra'] += $extra;
                    $plan['cohorts']['details'][$idnumber]['moodle_members'] = $actual->count();
                    $plan['cohorts']['details'][$idnumber]['membership_add'] = $toAdd;
                    $plan['cohorts']['details'][$idnumber]['membership_extra'] = $extra;
                }
            } catch (\Throwable $e) {
                $plan['membership_comparable'] = false;
                $plan['membership_error'] = $e->getMessage();
            }
        }

        if ($type === 'categories' || $type === 'all') {
            $existingCategories = collect($this->call($integration, 'core_course_get_categories', ['addsubcategories' => 1]))->keyBy(fn ($row) => (string) ($row['idnumber'] ?? ''));
            $years = $classes->groupBy('tahun_pelajaran_id');
            foreach ($years as $yearId => $yearClasses) {
                $year = $yearClasses->first()->tahunPelajaran;
                $parent = $existingCategories->get('simansa-tp-'.$yearId);
                if (!$parent) $plan['categories']['create']++; elseif (($parent['name'] ?? '') !== ($year?->nama ?: 'Tahun Pelajaran')) $plan['categories']['update']++; else $plan['categories']['unchanged']++;
                foreach ($yearClasses as $class) {
                    $found = $existingCategories->get('simansa-kelas-'.$class->id);
                    if (!$found) $plan['categories']['create']++; elseif (($found['name'] ?? '') !== $class->nama_kelas) $plan['categories']['update']++; else $plan['categories']['unchanged']++;
                }
            }
        }

        $actions = [
            'create' => ($type === 'users' || $type === 'all' ? $plan['users']['create'] : 0) + ($type === 'cohorts' || $type === 'all' ? $plan['cohorts']['create'] : 0) + ($type === 'categories' || $type === 'all' ? $plan['categories']['create'] : 0),
            'update' => ($type === 'users' || $type === 'all' ? $plan['users']['update'] : 0) + ($type === 'cohorts' || $type === 'all' ? $plan['cohorts']['update'] : 0) + ($type === 'categories' || $type === 'all' ? $plan['categories']['update'] : 0),
            'membership_add' => ($type === 'cohorts' || $type === 'all') ? $plan['cohorts']['membership_add'] : 0,
            'membership_remove' => ($type === 'cohorts' || $type === 'all') ? $plan['cohorts']['membership_extra'] : 0,
            'unchanged' => 0,
        ];
        $actions['total_writes'] = $actions['create'] + $actions['update'] + $actions['membership_add'] + $actions['membership_remove'];
        $actions['unchanged'] = ($type === 'users' || $type === 'all' ? $plan['users']['unchanged'] : 0) + ($type === 'cohorts' || $type === 'all' ? $plan['cohorts']['unchanged'] : 0) + ($type === 'categories' || $type === 'all' ? $plan['categories']['unchanged'] : 0);
        $token = Str::random(64);
        Cache::put('moodle-sync-preview:'.$token, ['type' => $type, 'integration_id' => $integration->id, 'plan' => $plan, 'actions' => $actions], now()->addMinutes(15));
        return ['local' => $local, 'plan' => $plan, 'actions' => $actions, 'preview_token' => $token, 'comparison_complete' => $plan['membership_comparable'], 'message' => 'Read-only: SIMANSA dibandingkan dengan data Moodle terbaru. Belum ada data yang diubah. Anggota ekstra di Moodle tidak dihapus.'];
    }

    public function consumePreview(MoodleIntegration $integration, string $token, string $type): array
    {
        $preview = Cache::pull('moodle-sync-preview:'.$token);
        if (!$preview || $preview['integration_id'] !== $integration->id || $preview['type'] !== $type) throw new RuntimeException('Preview sudah kedaluwarsa atau tidak cocok. Jalankan preview lagi.');
        if (!($preview['plan']['membership_comparable'] ?? true)) throw new RuntimeException('Perbandingan membership Moodle belum tersedia. Sinkronisasi diblokir demi keamanan.');
        return $preview;
    }

    public function createRun(MoodleIntegration $integration, string $type, ?string $userId = null, ?array $preview = null): MoodleSyncRun
    {
        if (!$integration->enabled || blank($integration->base_url) || blank($integration->webservice_token)) {
            throw new RuntimeException('Integrasi Moodle belum aktif atau token belum diisi.');
        }
        $local = $this->preview();
        $total = $preview['actions']['total_writes'] ?? match ($type) {
            'users' => $local['total_users'], 'cohorts' => $local['total_cohorts'],
            'categories' => $local['total_categories'], default => $local['total_users'] + $local['total_cohorts'] + $local['total_categories'],
        };
        return MoodleSyncRun::create([
            'moodle_integration_id' => $integration->id, 'started_by' => $userId, 'type' => $type,
            'status' => 'queued', 'summary' => ['created' => 0, 'updated' => 0, 'skipped' => $preview['actions']['unchanged'] ?? 0, 'failed' => 0, 'total' => $total, 'preview' => $preview['actions'] ?? []],
            'total_items' => $total, 'processed_items' => 0, 'current_stage' => 'Menunggu proses dimulai',
        ]);
    }

    public function runExisting(MoodleSyncRun $run): void
    {
        $integration = $run->integration;
        $summary = $run->summary ?: ['created' => 0, 'updated' => 0, 'skipped' => 0, 'failed' => 0, 'total' => $run->total_items];
        $run->update(['status' => 'running', 'started_at' => $run->started_at ?: now(), 'current_stage' => 'Menyiapkan data']);
        try {
            if (($run->type === 'users' || $run->type === 'all') && $integration->sync_users) $this->syncUsers($integration, $run, $summary);
            if (($run->type === 'cohorts' || $run->type === 'all') && $integration->sync_cohorts) $this->syncCohorts($integration, $run, $summary);
            if (($run->type === 'categories' || $run->type === 'all') && $integration->sync_categories) $this->syncCategories($integration, $run, $summary);
            $run->update(['status' => 'success', 'summary' => $summary, 'processed_items' => $run->total_items, 'current_stage' => 'Selesai', 'finished_at' => now()]);
        } catch (\Throwable $e) {
            Log::error('Moodle sync failed', ['run_id' => $run->id, 'error' => $e->getMessage()]);
            $run->update(['status' => 'failed', 'summary' => $summary, 'error' => $e->getMessage(), 'current_stage' => 'Gagal', 'finished_at' => now()]);
        }
    }

    private function advance(MoodleSyncRun $run, array $summary, string $stage): void
    {
        $run->increment('processed_items');
        $run->update(['summary' => $summary, 'current_stage' => $stage]);
    }

    public function run(MoodleIntegration $integration, string $type, ?int $userId = null): MoodleSyncRun
    {
        if (!$integration->enabled || blank($integration->base_url) || blank($integration->webservice_token)) {
            throw new RuntimeException('Integrasi Moodle belum aktif atau token belum diisi.');
        }

        $run = MoodleSyncRun::create(['moodle_integration_id' => $integration->id, 'started_by' => $userId, 'type' => $type, 'status' => 'running', 'started_at' => now()]);
        $summary = ['created' => 0, 'updated' => 0, 'skipped' => 0, 'failed' => 0, 'total' => 0];

        try {
            if (($type === 'users' || $type === 'all') && $integration->sync_users) {
                $this->syncUsers($integration, $run, $summary);
            }
            if (($type === 'cohorts' || $type === 'all') && $integration->sync_cohorts) {
                $this->syncCohorts($integration, $run, $summary);
            }
            if (($type === 'categories' || $type === 'all') && $integration->sync_categories) {
                $this->syncCategories($integration, $run, $summary);
            }
            $run->update(['status' => 'success', 'summary' => $summary, 'finished_at' => now()]);
        } catch (\Throwable $e) {
            Log::error('Moodle sync failed', ['run_id' => $run->id, 'error' => $e->getMessage()]);
            $run->update(['status' => 'failed', 'summary' => $summary, 'error' => $e->getMessage(), 'finished_at' => now()]);
            throw $e;
        }
        return $run;
    }

    private function syncUsers(MoodleIntegration $integration, MoodleSyncRun $run, array &$summary): void
    {
        $students = Siswa::with(['user', 'kelasSaatIni'])->whereNotNull('nisn')->where('nisn', '!=', '')->get();
        foreach ($students as $student) {
            $payload = ['username' => trim($student->nisn), 'firstname' => $student->nama_lengkap, 'lastname' => $student->kelasSaatIni?->nama_kelas ?: ($student->status_siswa === 'alumni' ? 'Alumni' : 'Siswa'), 'email' => $student->user?->email ?: trim($student->nisn).trim($integration->student_email_domain), 'auth' => 'manual'];
            $this->upsertUser($integration, $run, $summary, 'siswa', (string) $student->id, $payload, $student->nisn);
            $this->advance($run, $summary, 'Sinkronisasi user siswa');
        }

        $gtks = Gtk::query()->whereNotNull('nik')->where('nik', '!=', '')->get();
        foreach ($gtks as $gtk) {
            $payload = ['username' => trim($gtk->nik), 'firstname' => $gtk->nama_lengkap, 'lastname' => $gtk->jenis_ptk ?: 'GTK', 'email' => $gtk->email ?: trim($gtk->nik).'@man1metro.sch.id', 'auth' => 'manual'];
            $this->upsertUser($integration, $run, $summary, 'gtk', (string) $gtk->id, $payload, $gtk->nik);
            $this->advance($run, $summary, 'Sinkronisasi user GTK');
        }
    }

    private function upsertUser(MoodleIntegration $integration, MoodleSyncRun $run, array &$summary, string $entity, string $localId, array $payload, string $identifier): void
    {
        $summary['total']++;
        try {
            $existing = $this->call($integration, 'core_user_get_users_by_field', ['field' => 'username', 'values' => [$payload['username']]]);
            $user = is_array($existing) && isset($existing[0]) ? $existing[0] : null;
            if ($user) {
                $payload['id'] = (int) $user['id'];
                $this->call($integration, 'core_user_update_users', ['users' => [$payload]]);
                $action = 'updated';
                $summary['updated']++;
            } else {
                $payload['password'] = $payload['username'];
                $created = $this->call($integration, 'core_user_create_users', ['users' => [$payload]]);
                $user = $created[0] ?? [];
                $action = 'created';
                $summary['created']++;
            }
            $auditPayload = $payload;
            unset($auditPayload['password']);
            MoodleSyncItem::create(['moodle_sync_run_id' => $run->id, 'entity_type' => $entity, 'local_id' => $localId, 'identifier' => $identifier, 'action' => $action, 'status' => 'success', 'moodle_id' => $user['id'] ?? null, 'payload' => $auditPayload]);
        } catch (\Throwable $e) {
            $summary['failed']++;
            $auditPayload = $payload;
            unset($auditPayload['password']);
            MoodleSyncItem::create(['moodle_sync_run_id' => $run->id, 'entity_type' => $entity, 'local_id' => $localId, 'identifier' => $identifier, 'action' => 'upsert', 'status' => 'failed', 'message' => $e->getMessage(), 'payload' => $auditPayload]);
        }
    }

    private function syncCohorts(MoodleIntegration $integration, MoodleSyncRun $run, array &$summary): void
    {
        $classes = Kelas::with(['tahunPelajaran', 'siswaAktif'])->where('is_active', true)->whereNotNull('nama_kelas')->get();
        $managedUsernames = $classes->flatMap(fn ($class) => $class->siswaAktif->pluck('nisn'))->filter()->map(fn ($value) => trim($value))->unique()->values()->all();
        $managedUsers = collect($this->call($integration, 'core_user_get_users_by_field', ['field' => 'username', 'values' => $managedUsernames]))->keyBy(fn ($row) => (string) ($row['username'] ?? ''));
        $managedUserIds = collect($managedUsers->pluck('id')->filter())->map(fn ($id) => (int) $id);
        $existing = collect($this->call($integration, 'core_cohort_get_cohorts', ['cohortids' => []]));
        $byNumber = $existing->keyBy(fn ($row) => (string) ($row['idnumber'] ?? ''));
        foreach ($classes as $class) {
            $idnumber = 'simansa-kelas-'.$class->id;
            try {
                if ($byNumber->has($idnumber)) {
                    $cohort = $byNumber->get($idnumber);
                    $description = 'Rombel SIMANSA '.($class->tahunPelajaran?->nama ?: '');
                    if (($cohort['name'] ?? '') !== $class->nama_kelas || trim(strip_tags($cohort['description'] ?? '')) !== trim($description)) {
                        $this->call($integration, 'core_cohort_update_cohorts', ['cohorts' => [['id' => (int) $cohort['id'], 'categorytype' => ['type' => 'system', 'value' => 0], 'name' => $class->nama_kelas, 'idnumber' => $idnumber, 'description' => $description, 'descriptionformat' => 1, 'visible' => 1]]]);
                        $summary['updated']++;
                        $action = 'updated';
                    } else {
                        $summary['skipped']++;
                        $action = 'unchanged';
                    }
                    $moodleId = $cohort['id'];
                } else {
                    $result = $this->call($integration, 'core_cohort_create_cohorts', ['cohorts' => [['categorytype' => ['type' => 'system', 'value' => 0], 'name' => $class->nama_kelas, 'idnumber' => $idnumber, 'description' => 'Rombel SIMANSA '.($class->tahunPelajaran?->nama ?: ''), 'descriptionformat' => 1, 'visible' => 1]]]);
                    $summary['created']++;
                    $action = 'created';
                    $moodleId = $result[0]['id'] ?? null;
                }
                MoodleSyncItem::create(['moodle_sync_run_id' => $run->id, 'entity_type' => 'cohort', 'local_id' => (string) $class->id, 'identifier' => $idnumber, 'action' => $action, 'status' => 'success', 'moodle_id' => $moodleId]);
                $this->syncCohortMembers($integration, $run, $class, (int) $moodleId, $summary, $managedUserIds);
                $this->advance($run, $summary, 'Sinkronisasi kohor '.($class->nama_kelas ?: ''));
            } catch (\Throwable $e) { $summary['failed']++; MoodleSyncItem::create(['moodle_sync_run_id' => $run->id, 'entity_type' => 'cohort', 'local_id' => (string) $class->id, 'identifier' => $idnumber, 'action' => 'upsert', 'status' => 'failed', 'message' => $e->getMessage()]); }
        }
    }

    private function syncCohortMembers(MoodleIntegration $integration, MoodleSyncRun $run, Kelas $class, int $cohortId, array &$summary, $managedUserIds): void
    {
        if (!$cohortId) return;
        $existingMembers = collect($this->call($integration, 'core_cohort_get_cohort_members', ['cohortids' => [$cohortId]]));
        $existingUserIds = collect(data_get($existingMembers->first(), 'userids', []))->map(fn ($id) => (int) $id);
        $desiredUserIds = collect();
        foreach ($class->siswaAktif as $student) {
            if (filled($student->nisn)) {
                $users = $this->call($integration, 'core_user_get_users_by_field', ['field' => 'username', 'values' => [trim($student->nisn)]]);
                $userId = (int) data_get($users, '0.id', 0);
                if ($userId) $desiredUserIds->push($userId);
            }
        }
        $staleUserIds = $existingUserIds->diff($desiredUserIds)->intersect($managedUserIds);
        if ($staleUserIds->isNotEmpty()) {
            $this->call($integration, 'core_cohort_delete_cohort_members', ['members' => $staleUserIds->map(fn ($userId) => ['cohortid' => $cohortId, 'userid' => $userId])->values()->all()]);
            $summary['removed'] = ($summary['removed'] ?? 0) + $staleUserIds->count();
            $run->increment('processed_items', $staleUserIds->count());
        }
        foreach ($class->siswaAktif as $student) {
            if (blank($student->nisn)) continue;
            try {
                $users = $this->call($integration, 'core_user_get_users_by_field', ['field' => 'username', 'values' => [trim($student->nisn)]]);
                $moodleUserId = (int) data_get($users, '0.id', 0);
                if (!$moodleUserId) continue;
                if ($existingUserIds->contains($moodleUserId)) { $summary['skipped']++; continue; }
                $this->call($integration, 'core_cohort_add_cohort_members', ['members' => [['cohorttype' => ['type' => 'id', 'value' => $cohortId], 'usertype' => ['type' => 'id', 'value' => $moodleUserId]]]]);
                $existingUserIds->push($moodleUserId);
                $summary['total']++;
                $this->advance($run, $summary, 'Mengisi anggota kohor');
            } catch (\Throwable $e) {
                $summary['failed']++;
                MoodleSyncItem::create(['moodle_sync_run_id' => $run->id, 'entity_type' => 'membership', 'local_id' => (string) $student->id, 'identifier' => $class->nama_kelas, 'action' => 'add', 'status' => 'failed', 'message' => $e->getMessage()]);
            }
        }
    }

    private function syncCategories(MoodleIntegration $integration, MoodleSyncRun $run, array &$summary): void
    {
        $years = Kelas::with('tahunPelajaran')->where('is_active', true)->get()->groupBy('tahun_pelajaran_id');
        $existing = collect($this->call($integration, 'core_course_get_categories', ['addsubcategories' => 1]));
        $byNumber = $existing->keyBy(fn ($row) => (string) ($row['idnumber'] ?? ''));
        foreach ($years as $yearId => $classes) {
            $year = $classes->first()->tahunPelajaran;
            $idnumber = 'simansa-tp-'.$yearId;
            $parent = $byNumber->get($idnumber);
            if (!$parent) {
                try { $created = $this->call($integration, 'core_course_create_categories', ['categories' => [['name' => $year?->nama ?: 'Tahun Pelajaran', 'parent' => 0, 'idnumber' => $idnumber, 'description' => 'Kategori akademik SIMANSA', 'descriptionformat' => 1]]]); $parent = $created[0] ?? []; $summary['created']++; } catch (\Throwable $e) { $summary['failed']++; continue; }
            } else {
                try { $this->call($integration, 'core_course_update_categories', ['categories' => [['id' => (int) $parent['id'], 'name' => $year?->nama ?: 'Tahun Pelajaran', 'idnumber' => $idnumber, 'parent' => 0]]]); $summary['updated']++; } catch (\Throwable $e) { $summary['failed']++; continue; }
            }
            foreach ($classes as $class) {
                $childNumber = 'simansa-kelas-'.$class->id;
                try {
                    if ($byNumber->has($childNumber)) {
                        $this->call($integration, 'core_course_update_categories', ['categories' => [['id' => (int) $byNumber->get($childNumber)['id'], 'name' => $class->nama_kelas, 'idnumber' => $childNumber, 'parent' => (int) ($parent['id'] ?? 0)]]]);
                        $summary['updated']++;
                    } else {
                        $this->call($integration, 'core_course_create_categories', ['categories' => [['name' => $class->nama_kelas, 'parent' => (int) ($parent['id'] ?? 0), 'idnumber' => $childNumber, 'description' => 'Rombel SIMANSA', 'descriptionformat' => 1]]]);
                        $summary['created']++;
                    }
                } catch (\Throwable $e) { $summary['failed']++; }
                $this->advance($run, $summary, 'Sinkronisasi kategori');
            }
        }
    }

    private function call(MoodleIntegration $integration, string $function, array $params = []): array
    {
        $response = Http::asForm()->timeout(30)->post(rtrim($integration->base_url, '/').'/webservice/rest/server.php', array_merge(['wstoken' => $integration->webservice_token, 'wsfunction' => $function, 'moodlewsrestformat' => 'json'], $params));
        if (!$response->successful()) throw new RuntimeException('Moodle HTTP '.$response->status());
        $data = $response->json();
        if (!is_array($data)) throw new RuntimeException('Respons Moodle tidak valid.');
        if (isset($data['exception'])) throw new RuntimeException($data['message'] ?? $data['errorcode'] ?? 'Moodle API error.');
        return $data;
    }
}
