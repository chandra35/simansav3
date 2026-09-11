<?php

namespace App\Services;

use App\Models\Gtk;
use App\Models\Kelas;
use App\Models\MoodleIntegration;
use App\Models\MoodleCheckRun;
use App\Models\MoodleCheckResult;
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
    /**
     * Smart Check tahap pertama: baca siswa aktif yang sudah memiliki rombel,
     * lalu cocokkan NISN dengan username Moodle. Tidak ada operasi tulis.
     */
    public function smartCheckUsers(MoodleIntegration $integration): array
    {
        if (blank($integration->base_url) || blank($integration->webservice_token)) {
            throw new RuntimeException('URL atau token Web Service Moodle belum diisi.');
        }

        $students = Siswa::query()
            ->with('kelasSaatIni:id,nama_kelas')
            ->where('status_siswa', 'aktif')
            ->whereHas('kelasSaatIni', fn ($query) => $query->where('is_active', true))
            ->orderBy('nama_lengkap')
            ->get(['id', 'nisn', 'nama_lengkap', 'kelas_saat_ini_id']);

        $nisns = $students->pluck('nisn')->filter(fn ($nisn) => filled($nisn))->map(fn ($nisn) => trim($nisn))->unique()->values();
        $moodleUsers = collect();
        foreach ($nisns->chunk(200) as $chunk) {
            $moodleUsers = $moodleUsers->merge($this->call($integration, 'core_user_get_users_by_field', [
                'field' => 'username',
                'values' => $chunk->all(),
            ]));
        }
        $byUsername = $moodleUsers->keyBy(fn ($user) => (string) ($user['username'] ?? ''));

        $summary = ['total' => 0, 'missing' => 0, 'matched' => 0, 'conflict_nisn' => 0, 'without_nisn' => 0];
        $records = [];
        foreach ($students as $student) {
            $summary['total']++;
            $nisn = trim((string) $student->nisn);
            $base = ['id' => (string) $student->id, 'nisn' => $nisn, 'nama_lengkap' => $student->nama_lengkap, 'rombel' => $student->kelasSaatIni?->nama_kelas ?: '-', 'moodle_name' => null, 'moodle_id' => null, 'moodle_username' => null, 'status' => null];
            if ($nisn === '') {
                $summary['without_nisn']++;
                $records[] = array_replace($base, ['status' => 'without_nisn']);
                continue;
            }
            $moodle = $byUsername->get($nisn);
            if (!$moodle) {
                $summary['missing']++;
                $records[] = array_replace($base, ['status' => 'missing']);
                continue;
            }
            $moodleName = trim((string) ($moodle['firstname'] ?? ''));
            $nameMatch = $this->compareNames($student->nama_lengkap, $moodleName);
            $summary[$nameMatch === 'conflict' ? 'conflict_nisn' : 'matched']++;
            $records[] = array_replace($base, ['moodle_name' => $moodleName, 'moodle_id' => $moodle['id'] ?? null, 'moodle_username' => $moodle['username'] ?? $nisn, 'status' => $nameMatch === 'exact' ? 'matched' : ($nameMatch === 'variant' ? 'matched_variant' : 'conflict_nisn')]);
        }

        $result = ['summary' => $summary, 'records' => $records, 'checked_at' => now()->format('d M Y H:i:s'), 'message' => 'Smart Check selesai. Tidak ada data Moodle yang diubah.'];
        $this->storeCheckSnapshot($integration, 'students', $summary, $records);
        return $result;
    }

    /**
     * Smart Check GTK aktif. NIK menjadi username Moodle dan pemeriksaan tetap read-only.
     */
    public function smartCheckGtk(MoodleIntegration $integration): array
    {
        if (blank($integration->base_url) || blank($integration->webservice_token)) {
            throw new RuntimeException('URL atau token Web Service Moodle belum diisi.');
        }

        $gtks = Gtk::query()
            ->active()
            ->whereHas('user', fn ($query) => $query->where('is_active', true))
            ->orderBy('nama_lengkap')
            ->get(['id', 'nik', 'nama_lengkap', 'email', 'jenis_ptk']);
        $identifiers = $gtks->pluck('nik')->filter(fn ($value) => filled($value))->map(fn ($value) => trim($value))->unique()->values();
        $moodleUsers = collect();
        foreach ($identifiers->chunk(200) as $chunk) {
            $moodleUsers = $moodleUsers->merge($this->call($integration, 'core_user_get_users_by_field', ['field' => 'username', 'values' => $chunk->all()]));
        }
        $byUsername = $moodleUsers->keyBy(fn ($user) => (string) ($user['username'] ?? ''));
        $summary = ['total' => 0, 'missing' => 0, 'matched' => 0, 'conflict_nisn' => 0, 'without_nik' => 0];
        $records = [];

        foreach ($gtks as $gtk) {
            $summary['total']++;
            $nik = trim((string) $gtk->nik);
            $moodle = $nik !== '' ? $byUsername->get($nik) : null;
            $moodleName = $moodle ? trim(($moodle['firstname'] ?? '').' '.($moodle['lastname'] ?? '')) : null;
            if ($nik === '') {
                $summary['without_nik']++;
                $status = 'without_nik';
            } elseif (!$moodle) {
                $summary['missing']++;
                $status = 'missing';
            } elseif (($nameMatch = $this->compareNames($gtk->nama_lengkap, $moodleName)) !== 'conflict') {
                $summary['matched']++;
                $status = $nameMatch === 'exact' ? 'matched' : 'matched_variant';
            } else {
                $summary['conflict_nisn']++;
                $status = 'conflict_nisn';
            }
            $records[] = ['id' => (string) $gtk->id, 'nik' => $nik, 'nama_lengkap' => $gtk->nama_lengkap, 'jenis_ptk' => $gtk->jenis_ptk ?: 'GTK', 'moodle_name' => $moodleName, 'moodle_email' => $moodle['email'] ?? null, 'moodle_id' => $moodle['id'] ?? null, 'moodle_username' => $moodle['username'] ?? $nik, 'status' => $status];
        }

        $result = ['summary' => $summary, 'records' => $records, 'checked_at' => now()->format('d M Y H:i:s'), 'message' => 'Smart Check GTK selesai. Tidak ada data Moodle yang diubah.'];
        $this->storeCheckSnapshot($integration, 'gtk', $summary, $records);
        return $result;
    }

    public function latestCheckSnapshot(MoodleIntegration $integration, string $subject): ?array
    {
        $run = MoodleCheckRun::with('results')->where('moodle_integration_id', $integration->id)->where('subject', $subject)->where('status', 'success')->latest('checked_at')->first();
        if (!$run) return null;
        return ['summary' => $run->summary ?: [], 'records' => $run->results->map(fn ($item) => array_merge(['id' => $item->local_id, 'result_id' => $item->id, 'status' => $item->resolution ? 'resolved' : $item->status, 'resolution' => $item->resolution], $item->payload ?: []))->values()->all(), 'checked_at' => $run->checked_at?->format('d M Y H:i:s'), 'message' => 'Menampilkan hasil pemeriksaan terakhir.'];
    }

    private function storeCheckSnapshot(MoodleIntegration $integration, string $subject, array $summary, array $records): void
    {
        $run = MoodleCheckRun::create(['moodle_integration_id' => $integration->id, 'checked_by' => auth()->id(), 'subject' => $subject, 'status' => 'success', 'summary' => $summary, 'checked_at' => now()]);
        foreach ($records as $record) {
            $identifier = $subject === 'gtk' ? ($record['nik'] ?? '') : ($record['nisn'] ?? '');
            MoodleCheckResult::create(['moodle_check_run_id' => $run->id, 'local_id' => $record['id'] ?? null, 'identifier' => $identifier, 'local_name' => $record['nama_lengkap'] ?? null, 'local_group' => $record['rombel'] ?? ($record['jenis_ptk'] ?? null), 'moodle_name' => $record['moodle_name'] ?? null, 'moodle_email' => $record['moodle_email'] ?? null, 'status' => $record['status'] ?? 'unknown', 'payload' => $record]);
        }
    }

    public function resolveConflict(MoodleIntegration $integration, string $subject, string $localId, string $resolution, ?string $newUsername = null, ?string $note = null): array
    {
        $result = MoodleCheckResult::whereHas('run', fn ($query) => $query->where('moodle_integration_id', $integration->id)->where('subject', $subject))
            ->where('local_id', $localId)->latest('id')->firstOrFail();
        if ($result->status !== 'conflict_nisn' && !$result->resolution) throw new RuntimeException('Data ini bukan konflik identitas yang dapat diselesaikan.');

        if ($resolution === 'correct_username') {
            $newUsername = trim((string) $newUsername);
            if ($newUsername === '') throw new RuntimeException('NISN baru wajib diisi.');
            $duplicate = $this->call($integration, 'core_user_get_users_by_field', ['field' => 'username', 'values' => [$newUsername]]);
            if (filled($duplicate)) throw new RuntimeException('NISN baru sudah digunakan akun Moodle lain.');
            $moodleId = data_get($result->payload, 'moodle_id');
            if (!$moodleId) throw new RuntimeException('ID akun Moodle tidak tersedia. Jalankan Smart Check terbaru.');
            $this->call($integration, 'core_user_update_users', ['users' => [['id' => (int) $moodleId, 'username' => $newUsername]]]);
        }

        if ($resolution === 'update_name') {
            $moodleId = data_get($result->payload, 'moodle_id');
            if (!$moodleId) throw new RuntimeException('ID akun Moodle tidak tersedia. Jalankan Smart Check terbaru.');
            $this->call($integration, 'core_user_update_users', ['users' => [['id' => (int) $moodleId, 'firstname' => $result->local_name]]]);
        }

        if (!in_array($resolution, ['correct_username', 'update_name', 'verified', 'ignored'], true)) throw new RuntimeException('Resolusi konflik tidak valid.');
        $result->update(['resolution' => $resolution, 'resolution_note' => $note, 'verified_by' => auth()->id(), 'verified_at' => now()]);
        return ['message' => $resolution === 'correct_username' ? 'Username/NISN akun Moodle diperbarui. userid dan data nilai tetap dipertahankan.' : ($resolution === 'update_name' ? 'Nama lengkap Moodle disamakan dengan SIMANSA. userid dan data nilai tetap dipertahankan.' : 'Konflik ditandai sebagai sudah diverifikasi.'), 'resolution' => $resolution];
    }

    public function createMissingUser(MoodleIntegration $integration, string $subject, string $localId): array
    {
        if (!$integration->enabled || blank($integration->base_url) || blank($integration->webservice_token)) throw new RuntimeException('Integrasi Moodle belum aktif atau token belum diisi.');
        if ($subject === 'gtk') {
            $local = Gtk::active()->whereHas('user', fn ($query) => $query->where('is_active', true))->where('id', $localId)->firstOrFail();
            $identifier = trim((string) $local->nik);
            $payload = ['username' => $identifier, 'firstname' => $local->nama_lengkap, 'lastname' => $local->jenis_ptk ?: 'GTK', 'email' => $local->email ?: $identifier.'@man1metro.sch.id'];
        } else {
            $local = Siswa::with(['user', 'kelasSaatIni'])->where('status_siswa', 'aktif')->whereHas('kelasSaatIni', fn ($query) => $query->where('is_active', true))->where('id', $localId)->firstOrFail();
            $identifier = trim((string) $local->nisn);
            $payload = ['username' => $identifier, 'firstname' => $local->nama_lengkap, 'lastname' => $local->kelasSaatIni?->nama_kelas ?: 'Siswa', 'email' => $local->user?->email ?: $identifier.trim($integration->student_email_domain)];
        }
        if ($identifier === '') throw new RuntimeException($subject === 'gtk' ? 'GTK belum memiliki NIK.' : 'Siswa belum memiliki NISN.');
        if (filled($this->call($integration, 'core_user_get_users_by_field', ['field' => 'username', 'values' => [$identifier]]))) throw new RuntimeException('Username/NISN sudah terdaftar di Moodle. Jalankan pemeriksaan ulang.');
        $created = $this->call($integration, 'core_user_create_users', ['users' => [array_merge($payload, ['password' => $identifier, 'auth' => 'manual', 'forcepasswordchange' => 1])]]);
        $result = MoodleCheckResult::whereHas('run', fn ($query) => $query->where('moodle_integration_id', $integration->id)->where('subject', $subject))->where('local_id', $localId)->latest('id')->first();
        if ($result) $result->update(['resolution' => 'created', 'resolution_note' => 'Akun Moodle dibuat dari Smart Check.', 'verified_by' => auth()->id(), 'verified_at' => now()]);
        return ['message' => 'Akun Moodle berhasil dibuat untuk '.$local->nama_lengkap.'. Username: '.$identifier.'. Password awal sama dengan username dan wajib diganti saat login pertama.', 'moodle_id' => data_get($created, '0.id')];
    }

    private function normalizeName(?string $name): string
    {
        return mb_strtoupper(trim((string) preg_replace('/\s+/', ' ', (string) $name)));
    }

    /**
     * Membandingkan nama secara konservatif. Varian format/penulisan ringan
     * diterima, tetapi nama dengan identitas yang berbeda tetap menjadi konflik.
     */
    private function compareNames(?string $local, ?string $moodle): string
    {
        $local = $this->normalizeName($local);
        $moodle = $this->normalizeName($moodle);
        if ($local === '' || $moodle === '') return 'conflict';
        if ($local === $moodle || preg_replace('/\s+/', '', $local) === preg_replace('/\s+/', '', $moodle)) return 'exact';

        $localTokens = preg_split('/\s+/', preg_replace('/[^A-Z0-9 ]/', '', $local), -1, PREG_SPLIT_NO_EMPTY);
        $moodleTokens = preg_split('/\s+/', preg_replace('/[^A-Z0-9 ]/', '', $moodle), -1, PREG_SPLIT_NO_EMPTY);
        $shorter = count($localTokens) <= count($moodleTokens) ? $localTokens : $moodleTokens;
        $longer = count($localTokens) <= count($moodleTokens) ? $moodleTokens : $localTokens;
        if (count($longer) - count($shorter) <= 1) {
            $matched = 0;
            foreach ($shorter as $token) {
                foreach ($longer as $candidate) {
                    if ($token === $candidate || (strlen($token) === 1 && str_starts_with($candidate, $token)) || (strlen($candidate) === 1 && str_starts_with($token, $candidate))) {
                        $matched++;
                        break;
                    }
                }
            }
            if ($matched === count($shorter) && count($shorter) >= 2) return 'variant';
        }

        similar_text(str_replace(' ', '', $local), str_replace(' ', '', $moodle), $percent);
        return $percent >= 92 ? 'variant' : 'conflict';
    }

    public function test(MoodleIntegration $integration): array
    {
        $result = $this->call($integration, 'core_webservice_get_site_info');
        $integration->update(['last_tested_at' => now(), 'last_test_status' => 'success', 'last_test_message' => 'Terhubung ke '.($result['sitename'] ?? 'Moodle')]);
        return $result;
    }

    public function preview(): array
    {
        $studentScope = Siswa::query()->where('status_siswa', 'aktif')->whereHas('kelasSaatIni', fn ($query) => $query->where('is_active', true));
        $students = (clone $studentScope)->count();
        $studentsWithNisn = (clone $studentScope)->whereNotNull('nisn')->where('nisn', '!=', '')->count();
        $gtk = Gtk::query()->active()->whereHas('user', fn ($query) => $query->where('is_active', true))->whereNotNull('nik')->where('nik', '!=', '')->count();
        $cohorts = Kelas::query()->where('is_active', true)->whereNotNull('nama_kelas')->count();
        $members = Kelas::query()->where('is_active', true)->whereNotNull('nama_kelas')->with(['siswaAktif' => fn ($query) => $query->where('siswa.status_siswa', 'aktif')])->get()->sum(fn ($class) => $class->siswaAktif->count());
        $years = Kelas::query()->where('is_active', true)->distinct('tahun_pelajaran_id')->count('tahun_pelajaran_id');

        return [
            'students' => $students, 'students_with_nisn' => $studentsWithNisn, 'gtk' => $gtk, 'cohorts' => $cohorts, 'members' => $members,
            'categories' => $years, 'category_items' => $years + $cohorts,
            'total_users' => $studentsWithNisn + $gtk, 'total_cohorts' => $cohorts + $members,
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
            'users' => ['create' => 0, 'update' => 0, 'conflict' => 0, 'unchanged' => 0, 'missing' => 0, 'details' => []],
            'cohorts' => ['create' => 0, 'update' => 0, 'unchanged' => 0, 'membership_add' => 0, 'membership_extra' => 0, 'details' => []],
            'categories' => ['create' => 0, 'update' => 0, 'unchanged' => 0],
            'membership_comparable' => true,
        ];

        if ($type === 'users' || $type === 'all') {
            $desired = collect();
            Siswa::with(['user', 'kelasSaatIni'])->where('status_siswa', 'aktif')->whereHas('kelasSaatIni', fn ($query) => $query->where('is_active', true))->whereNotNull('nisn')->where('nisn', '!=', '')->get()->each(function ($student) use (&$desired, $integration) {
                $desired->push(['type' => 'Siswa', 'username' => trim($student->nisn), 'firstname' => $student->nama_lengkap, 'lastname' => $student->kelasSaatIni?->nama_kelas ?: 'Siswa', 'email' => $student->user?->email ?: trim($student->nisn).trim($integration->student_email_domain)]);
            });
            Gtk::active()->whereHas('user', fn ($query) => $query->where('is_active', true))->whereNotNull('nik')->where('nik', '!=', '')->get()->each(function ($gtk) use (&$desired) {
                $desired->push(['type' => 'GTK', 'username' => trim($gtk->nik), 'firstname' => $gtk->nama_lengkap, 'lastname' => $gtk->jenis_ptk ?: 'GTK', 'email' => $gtk->email ?: trim($gtk->nik).'@man1metro.sch.id']);
            });
            $existing = collect($this->call($integration, 'core_user_get_users_by_field', ['field' => 'username', 'values' => $desired->pluck('username')->values()->all()]))->keyBy(fn ($row) => (string) ($row['username'] ?? ''));
            foreach ($desired as $item) {
                $found = $existing->get($item['username']);
                if (!$found) { $plan['users']['create']++; $plan['users']['details'][] = ['type' => $item['type'], 'identifier' => $item['username'], 'local_name' => $item['firstname'], 'moodle_name' => null, 'local_email' => $item['email'], 'moodle_email' => null, 'status' => 'missing']; continue; }
                $sameName = $this->normalizeName($found['firstname'] ?? '') === $this->normalizeName($item['firstname'] ?? '');
                $same = $sameName && ($found['lastname'] ?? '') === ($item['lastname'] ?? '') && ($found['email'] ?? '') === ($item['email'] ?? '');
                if (!$sameName) $plan['users']['conflict']++; elseif (!$same) $plan['users']['update']++; else $plan['users']['unchanged']++;
                $plan['users']['details'][] = ['type' => $item['type'], 'identifier' => $item['username'], 'local_name' => $item['firstname'], 'moodle_name' => trim(($found['firstname'] ?? '').' '.($found['lastname'] ?? '')), 'local_email' => $item['email'], 'moodle_email' => $found['email'] ?? null, 'status' => $same ? 'same' : ($sameName ? 'different' : 'conflict_nisn')];
            }
            $plan['users']['missing'] = $plan['users']['create'];
        }

        $classes = Kelas::with(['tahunPelajaran', 'siswaAktif' => fn ($query) => $query->where('siswa.status_siswa', 'aktif')])->where('is_active', true)->whereNotNull('nama_kelas')->get();
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
            'conflict' => ($type === 'users' || $type === 'all') ? $plan['users']['conflict'] : 0,
            'membership_add' => ($type === 'cohorts' || $type === 'all') ? $plan['cohorts']['membership_add'] : 0,
            'membership_remove' => ($type === 'cohorts' || $type === 'all') ? $plan['cohorts']['membership_extra'] : 0,
            'unchanged' => 0,
        ];
        $actions['total_writes'] = $actions['create'] + $actions['update'] + $actions['membership_add'] + $actions['membership_remove'];
        $actions['unchanged'] = ($type === 'users' || $type === 'all' ? $plan['users']['unchanged'] : 0) + ($type === 'cohorts' || $type === 'all' ? $plan['cohorts']['unchanged'] : 0) + ($type === 'categories' || $type === 'all' ? $plan['categories']['unchanged'] : 0);
        $token = Str::random(64);
        Cache::put('moodle-sync-preview:'.$token, ['type' => $type, 'integration_id' => $integration->id, 'plan' => $plan, 'actions' => $actions], now()->addMinutes(15));
        return ['local' => $local, 'plan' => $plan, 'actions' => $actions, 'preview_token' => $token, 'comparison_complete' => $plan['membership_comparable'], 'message' => 'Read-only: SIMANSA dibandingkan dengan data Moodle terbaru. Konflik identitas diblokir dan tidak akan diubah otomatis.'];
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
        $summary = $run->summary ?: ['created' => 0, 'updated' => 0, 'conflict' => 0, 'skipped' => 0, 'failed' => 0, 'total' => $run->total_items];
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
        $summary = ['created' => 0, 'updated' => 0, 'conflict' => 0, 'skipped' => 0, 'failed' => 0, 'total' => 0];

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
        $students = Siswa::with(['user', 'kelasSaatIni'])->where('status_siswa', 'aktif')->whereHas('kelasSaatIni', fn ($query) => $query->where('is_active', true))->whereNotNull('nisn')->where('nisn', '!=', '')->get();
        foreach ($students as $student) {
            $payload = ['username' => trim($student->nisn), 'firstname' => $student->nama_lengkap, 'lastname' => $student->kelasSaatIni?->nama_kelas ?: ($student->status_siswa === 'alumni' ? 'Alumni' : 'Siswa'), 'email' => $student->user?->email ?: trim($student->nisn).trim($integration->student_email_domain), 'auth' => 'manual'];
            $this->upsertUser($integration, $run, $summary, 'siswa', (string) $student->id, $payload, $student->nisn);
            $this->advance($run, $summary, 'Sinkronisasi user siswa');
        }

        $gtks = Gtk::query()->active()->whereHas('user', fn ($query) => $query->where('is_active', true))->whereNotNull('nik')->where('nik', '!=', '')->get();
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
                if ($this->normalizeName($user['firstname'] ?? '') !== $this->normalizeName($payload['firstname'] ?? '')) {
                    $summary['conflict']++;
                    MoodleSyncItem::create(['moodle_sync_run_id' => $run->id, 'entity_type' => $entity, 'local_id' => $localId, 'identifier' => $identifier, 'action' => 'conflict', 'status' => 'skipped', 'moodle_id' => $user['id'] ?? null, 'message' => 'Username cocok tetapi nama berbeda. Perlu verifikasi kepemilikan akun.', 'payload' => ['local' => $payload, 'moodle' => $user]]);
                    return;
                }
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
        $classes = Kelas::with(['tahunPelajaran', 'siswaAktif' => fn ($query) => $query->where('siswa.status_siswa', 'aktif')])->where('is_active', true)->whereNotNull('nama_kelas')->get();
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
