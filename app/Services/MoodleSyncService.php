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
        return [
            'students' => Siswa::query()->whereNotNull('nisn')->where('nisn', '!=', '')->count(),
            'gtk' => Gtk::query()->whereNotNull('nik')->where('nik', '!=', '')->count(),
            'cohorts' => Kelas::query()->where('is_active', true)->whereNotNull('nama_kelas')->count(),
            'categories' => Kelas::query()->where('is_active', true)->distinct('tahun_pelajaran_id')->count('tahun_pelajaran_id'),
        ];
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
        }

        $gtks = Gtk::query()->whereNotNull('nik')->where('nik', '!=', '')->get();
        foreach ($gtks as $gtk) {
            $payload = ['username' => trim($gtk->nik), 'firstname' => $gtk->nama_lengkap, 'lastname' => $gtk->jenis_ptk ?: 'GTK', 'email' => $gtk->email ?: trim($gtk->nik).'@man1metro.sch.id', 'auth' => 'manual'];
            $this->upsertUser($integration, $run, $summary, 'gtk', (string) $gtk->id, $payload, $gtk->nik);
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
        $existing = collect($this->call($integration, 'core_cohort_get_cohorts', ['cohortids' => []]));
        $byNumber = $existing->keyBy(fn ($row) => (string) ($row['idnumber'] ?? ''));
        foreach ($classes as $class) {
            $idnumber = 'simansa-kelas-'.$class->id;
            try {
                if ($byNumber->has($idnumber)) {
                    $cohort = $byNumber->get($idnumber);
                    $this->call($integration, 'core_cohort_update_cohorts', ['cohorts' => [['id' => (int) $cohort['id'], 'name' => $class->nama_kelas, 'idnumber' => $idnumber, 'description' => 'Rombel SIMANSA '.($class->tahunPelajaran?->nama ?: ''), 'descriptionformat' => 1, 'visible' => 1]]]);
                    $summary['updated']++;
                    $action = 'updated';
                    $moodleId = $cohort['id'];
                } else {
                    $result = $this->call($integration, 'core_cohort_create_cohorts', ['cohorts' => [['name' => $class->nama_kelas, 'idnumber' => $idnumber, 'description' => 'Rombel SIMANSA '.($class->tahunPelajaran?->nama ?: ''), 'descriptionformat' => 1, 'visible' => 1, 'contextid' => 1]]]);
                    $summary['created']++;
                    $action = 'created';
                    $moodleId = $result[0]['id'] ?? null;
                }
                MoodleSyncItem::create(['moodle_sync_run_id' => $run->id, 'entity_type' => 'cohort', 'local_id' => (string) $class->id, 'identifier' => $idnumber, 'action' => $action, 'status' => 'success', 'moodle_id' => $moodleId]);
                $this->syncCohortMembers($integration, $run, $class, (int) $moodleId, $summary);
            } catch (\Throwable $e) { $summary['failed']++; MoodleSyncItem::create(['moodle_sync_run_id' => $run->id, 'entity_type' => 'cohort', 'local_id' => (string) $class->id, 'identifier' => $idnumber, 'action' => 'upsert', 'status' => 'failed', 'message' => $e->getMessage()]); }
        }
    }

    private function syncCohortMembers(MoodleIntegration $integration, MoodleSyncRun $run, Kelas $class, int $cohortId, array &$summary): void
    {
        if (!$cohortId) return;
        foreach ($class->siswaAktif as $student) {
            if (blank($student->nisn)) continue;
            try {
                $users = $this->call($integration, 'core_user_get_users_by_field', ['field' => 'username', 'values' => [trim($student->nisn)]]);
                $moodleUserId = (int) data_get($users, '0.id', 0);
                if (!$moodleUserId) continue;
                $this->call($integration, 'core_cohort_add_cohort_members', ['members' => [['cohorttype' => ['type' => 'id', 'value' => $cohortId], 'usertype' => ['type' => 'id', 'value' => $moodleUserId]]]]);
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
