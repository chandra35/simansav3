<?php

namespace App\Services;

use App\Models\AbsensiSiswaRecord;
use App\Models\AbsensiSiswaSession;
use App\Models\AbsensiSetting;
use App\Models\AttendanceQrToken;
use App\Models\SholatRecord;
use App\Models\SholatSession;
use App\Models\SholatSetting;
use App\Models\Siswa;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class StudentQrAttendanceService
{
    private const DEFAULT_TTL_MINUTES = 10;
    private const DAILY_SOURCE_REFERENCE = 'student_qr_scan';

    public function issueDailyToken(AbsensiSiswaSession $session, User $issuer, ?int $ttlMinutes = null): array
    {
        if (! AbsensiSetting::getValue('qr_daily_enabled', true)) {
            throw ValidationException::withMessages(['session' => 'QR absensi harian sedang dinonaktifkan oleh administrator.']);
        }

        if ($session->mode !== 'harian') {
            throw ValidationException::withMessages([
                'session' => 'QR attendance hanya didukung untuk sesi absensi harian.',
            ]);
        }

        if ($session->status === 'final') {
            throw ValidationException::withMessages([
                'session' => 'Sesi absensi yang sudah final tidak bisa dibuatkan token QR baru.',
            ]);
        }

        return $this->issueToken(
            AttendanceQrToken::CONTEXT_DAILY,
            ['absensi_siswa_session_id' => $session->id],
            $issuer,
            $ttlMinutes
        );
    }

    public function issueSholatToken(SholatSession $session, User $issuer, ?int $ttlMinutes = null): array
    {
        if (! AbsensiSetting::getValue('qr_sholat_enabled', true)) {
            throw ValidationException::withMessages(['session' => 'QR presensi sholat sedang dinonaktifkan oleh administrator.']);
        }

        $this->ensureSholatQrAvailability($session);

        return $this->issueToken(
            AttendanceQrToken::CONTEXT_SHOLAT,
            ['sholat_session_id' => $session->id],
            $issuer,
            $ttlMinutes
        );
    }

    public function checkInWithToken(string $plainToken, Siswa $siswa, User $actor): array
    {
        $token = $this->resolveToken($plainToken);

        if ($token->context === AttendanceQrToken::CONTEXT_DAILY) {
            return $this->checkInDaily($token, $siswa, $actor);
        }

        if ($token->context === AttendanceQrToken::CONTEXT_SHOLAT) {
            return $this->checkInSholat($token, $siswa);
        }

        throw ValidationException::withMessages([
            'token' => 'Konteks token QR tidak dikenali.',
        ]);
    }

    private function issueToken(string $context, array $scope, User $issuer, ?int $ttlMinutes = null): array
    {
        $configuredTtl = AbsensiSetting::getValue('qr_token_ttl_minutes', self::DEFAULT_TTL_MINUTES);
        $ttl = max(1, min(180, $ttlMinutes ?? $configuredTtl));
        $plainToken = Str::random(64);
        $expiresAt = now()->addMinutes($ttl);

        $token = DB::transaction(function () use ($context, $scope, $issuer, $plainToken, $expiresAt) {
            AttendanceQrToken::query()
                ->where('context', $context)
                ->when(isset($scope['absensi_siswa_session_id']), fn ($query) => $query->where('absensi_siswa_session_id', $scope['absensi_siswa_session_id']))
                ->when(isset($scope['sholat_session_id']), fn ($query) => $query->where('sholat_session_id', $scope['sholat_session_id']))
                ->whereNull('revoked_at')
                ->update(['revoked_at' => now()]);

            return AttendanceQrToken::query()->create([
                'context' => $context,
                'absensi_siswa_session_id' => $scope['absensi_siswa_session_id'] ?? null,
                'sholat_session_id' => $scope['sholat_session_id'] ?? null,
                'token_hash' => hash('sha256', $plainToken),
                'expires_at' => $expiresAt,
                'issued_by' => $issuer->id,
            ]);
        });

        return [
            'id' => $token->id,
            'context' => $token->context,
            'token' => $plainToken,
            'expires_at' => $token->expires_at,
            'absensi_siswa_session_id' => $token->absensi_siswa_session_id,
            'sholat_session_id' => $token->sholat_session_id,
        ];
    }

    private function resolveToken(string $plainToken): AttendanceQrToken
    {
        $plainToken = trim($plainToken);
        if ($plainToken === '') {
            throw ValidationException::withMessages([
                'token' => 'Token QR wajib diisi.',
            ]);
        }

        $token = AttendanceQrToken::query()
            ->with([
                'dailySession:id,kelas_id,tanggal,status,mode,tahun_pelajaran_id',
                'sholatSession:id,kelas_id,tanggal,status,sholat_setting_id,tahun_pelajaran_id',
            ])
            ->where('token_hash', hash('sha256', $plainToken))
            ->first();

        if (! $token) {
            throw ValidationException::withMessages([
                'token' => 'Token QR tidak valid.',
            ]);
        }

        if ($token->revoked_at !== null) {
            throw ValidationException::withMessages([
                'token' => 'Token QR sudah tidak aktif.',
            ]);
        }

        if ($token->isExpired()) {
            throw ValidationException::withMessages([
                'token' => 'Token QR sudah kedaluwarsa.',
            ]);
        }

        return $token;
    }

    private function checkInDaily(AttendanceQrToken $token, Siswa $siswa, User $actor): array
    {
        if (! AbsensiSetting::getValue('qr_daily_enabled', true)) {
            throw ValidationException::withMessages(['token' => 'QR absensi harian sedang dinonaktifkan oleh administrator.']);
        }

        $session = $token->dailySession;
        if (! $session) {
            throw ValidationException::withMessages([
                'token' => 'Sesi absensi harian pada token tidak ditemukan.',
            ]);
        }

        if ($session->status === 'final') {
            throw ValidationException::withMessages([
                'token' => 'Sesi absensi sudah final dan tidak menerima scan QR.',
            ]);
        }

        if (! $this->isStudentInClass($siswa, $session->kelas_id, $session->tanggal, $session->tahun_pelajaran_id)) {
            throw ValidationException::withMessages([
                'token' => 'Anda tidak terdaftar pada kelas untuk sesi absensi ini.',
            ]);
        }

        $result = DB::transaction(function () use ($token, $session, $siswa, $actor) {
            $lockedToken = AttendanceQrToken::query()->lockForUpdate()->findOrFail($token->id);
            if ($lockedToken->revoked_at !== null || $lockedToken->isExpired()) {
                throw ValidationException::withMessages([
                    'token' => 'Token QR sudah tidak aktif atau kedaluwarsa.',
                ]);
            }

            $record = AbsensiSiswaRecord::withTrashed()
                ->where('session_id', $session->id)
                ->where('siswa_id', $siswa->id)
                ->first();

            if ($record && ! $record->trashed()) {
                return ['record' => $record, 'already_marked' => true];
            }

            if (! $record) {
                $record = new AbsensiSiswaRecord([
                    'session_id' => $session->id,
                    'siswa_id' => $siswa->id,
                ]);
            } elseif ($record->trashed()) {
                $record->restore();
            }

            $record->fill([
                'status' => 'hadir',
                'notes' => $record->notes ?: 'Check-in QR siswa',
                'attendance_method' => 'qrcode',
                'source_reference' => self::DAILY_SOURCE_REFERENCE,
                'checked_at' => now(),
                'checked_by' => $actor->id,
            ]);
            $record->save();

            $lockedSession = AbsensiSiswaSession::query()->lockForUpdate()->findOrFail($session->id);
            if (in_array($lockedSession->attendance_method, ['manual', 'face'], true)) {
                $lockedSession->attendance_method = 'hybrid';
                $lockedSession->updated_by = $lockedSession->updated_by ?: $actor->id;
                $lockedSession->save();
            }

            return ['record' => $record, 'already_marked' => false];
        });

        return [
            'context' => AttendanceQrToken::CONTEXT_DAILY,
            'session_id' => $session->id,
            'record_id' => $result['record']->id,
            'status' => $result['record']->status,
            'already_marked' => $result['already_marked'],
        ];
    }

    private function checkInSholat(AttendanceQrToken $token, Siswa $siswa): array
    {
        if (! AbsensiSetting::getValue('qr_sholat_enabled', true)) {
            throw ValidationException::withMessages(['token' => 'QR presensi sholat sedang dinonaktifkan oleh administrator.']);
        }

        $session = $token->sholatSession;
        if (! $session) {
            throw ValidationException::withMessages([
                'token' => 'Sesi sholat pada token tidak ditemukan.',
            ]);
        }

        $setting = $this->ensureSholatQrAvailability($session);
        if (! $setting->isAvailableForGender($siswa->jenis_kelamin)) {
            throw ValidationException::withMessages([
                'token' => 'Presensi QR ini tidak ditujukan untuk jenis kelamin Anda.',
            ]);
        }

        if (! $this->isStudentInClass($siswa, $session->kelas_id, $session->tanggal, $session->tahun_pelajaran_id)) {
            throw ValidationException::withMessages([
                'token' => 'Anda tidak terdaftar pada kelas untuk sesi sholat ini.',
            ]);
        }

        $record = DB::transaction(function () use ($token, $session, $siswa) {
            $lockedToken = AttendanceQrToken::query()->lockForUpdate()->findOrFail($token->id);
            if ($lockedToken->revoked_at !== null || $lockedToken->isExpired()) {
                throw ValidationException::withMessages([
                    'token' => 'Token QR sudah tidak aktif atau kedaluwarsa.',
                ]);
            }

            $existing = SholatRecord::query()
                ->where('sholat_session_id', $session->id)
                ->where('siswa_id', $siswa->id)
                ->first();

            if ($existing) {
                return [$existing, true];
            }

            return [SholatRecord::query()->create([
                'sholat_session_id' => $session->id,
                'siswa_id' => $siswa->id,
                'status' => SholatRecord::STATUS_BERJAMAAH,
                'catatan' => 'Check-in QR siswa',
            ]), false];
        });

        return [
            'context' => AttendanceQrToken::CONTEXT_SHOLAT,
            'session_id' => $session->id,
            'record_id' => $record[0]->id,
            'status' => $record[0]->status,
            'already_marked' => $record[1],
        ];
    }

    public function checkInWithStudentCard(Siswa $siswa, User $actor): array
    {
        $today = now()->toDateString();
        $classIds = $this->activeClassIdsForDate($siswa, now());
        $dailySessions = collect();
        $sholatSessions = collect();

        if (AbsensiSetting::getValue('qr_daily_enabled', true) && $classIds->isNotEmpty()) {
            $dailySessions = AbsensiSiswaSession::query()
                ->whereDate('tanggal', $today)
                ->where('mode', 'harian')
                ->where('status', '!=', 'final')
                ->whereIn('kelas_id', $classIds)
                ->get();
        }

        if (AbsensiSetting::getValue('qr_sholat_enabled', true) && $classIds->isNotEmpty()) {
            $sholatSessions = SholatSession::query()
                ->with('sholatSetting')
                ->whereDate('tanggal', $today)
                ->whereIn('kelas_id', $classIds)
                ->get()
                ->filter(function (SholatSession $session) use ($siswa) {
                    try {
                        $setting = $this->ensureSholatQrAvailability($session);

                        return $setting->isAvailableForGender($siswa->jenis_kelamin);
                    } catch (ValidationException) {
                        return false;
                    }
                })
                ->values();
        }

        if ($dailySessions->isEmpty() && $sholatSessions->isEmpty()) {
            throw ValidationException::withMessages([
                'card_qr' => 'Tidak ada sesi absensi harian atau sholat yang sedang terbuka untuk siswa ini.',
            ]);
        }

        if ($sholatSessions->count() > 1) {
            throw ValidationException::withMessages([
                'card_qr' => 'Ditemukan lebih dari satu sesi sholat yang berlaku. Pastikan jadwal sholat tidak saling tumpang tindih.',
            ]);
        }

        $dailyResult = DB::transaction(function () use ($dailySessions, $siswa, $actor) {
            return $dailySessions->map(fn (AbsensiSiswaSession $session) => $this->recordDailyCardScan($session, $siswa, $actor));
        });
        $sholatResult = DB::transaction(function () use ($sholatSessions, $siswa) {
            return $sholatSessions->map(fn (SholatSession $session) => $this->recordSholatCardScan($session, $siswa));
        });

        $newDaily = $dailyResult->where('already_marked', false)->count();
        $newSholat = $sholatResult->where('already_marked', false)->count();
        $newTotal = $newDaily + $newSholat;

        return [
            'student' => ['id' => $siswa->id, 'name' => $siswa->nama_lengkap],
            'daily_sessions' => $dailyResult->count(),
            'sholat_sessions' => $sholatResult->count(),
            'already_marked' => $newTotal === 0,
            'message' => $newTotal > 0
                ? sprintf('%s: %d absensi harian dan %d presensi sholat berhasil dicatat.', $siswa->nama_lengkap, $newDaily, $newSholat)
                : sprintf('%s sudah tercatat pada seluruh sesi yang sedang berlaku.', $siswa->nama_lengkap),
        ];
    }

    private function recordDailyCardScan(AbsensiSiswaSession $session, Siswa $siswa, User $actor): array
    {
        $lockedSession = AbsensiSiswaSession::query()->lockForUpdate()->findOrFail($session->id);
        if ($lockedSession->status === 'final') {
            return ['already_marked' => true];
        }

        $record = AbsensiSiswaRecord::withTrashed()
            ->where('session_id', $lockedSession->id)
            ->where('siswa_id', $siswa->id)
            ->first();
        if ($record && ! $record->trashed()) {
            return ['already_marked' => true];
        }
        if (! $record) {
            $record = new AbsensiSiswaRecord(['session_id' => $lockedSession->id, 'siswa_id' => $siswa->id]);
        } else {
            $record->restore();
        }

        $record->fill([
            'status' => 'hadir',
            'notes' => $record->notes ?: 'Scan QR ID Card siswa',
            'attendance_method' => 'qrcode',
            'source_reference' => 'student_card_qr_scan',
            'checked_at' => now(),
            'checked_by' => $actor->id,
        ])->save();
        if (in_array($lockedSession->attendance_method, ['manual', 'face'], true)) {
            $lockedSession->attendance_method = 'hybrid';
            $lockedSession->updated_by = $lockedSession->updated_by ?: $actor->id;
            $lockedSession->save();
        }

        return ['already_marked' => false];
    }

    private function recordSholatCardScan(SholatSession $session, Siswa $siswa): array
    {
        $existing = SholatRecord::query()
            ->where('sholat_session_id', $session->id)
            ->where('siswa_id', $siswa->id)
            ->first();
        if ($existing) {
            return ['already_marked' => true];
        }

        SholatRecord::query()->create([
            'sholat_session_id' => $session->id,
            'siswa_id' => $siswa->id,
            'status' => SholatRecord::STATUS_BERJAMAAH,
            'catatan' => 'Scan QR ID Card siswa',
        ]);

        return ['already_marked' => false];
    }

    private function activeClassIdsForDate(Siswa $siswa, CarbonInterface $tanggal)
    {
        return DB::table('siswa_kelas')
            ->where('siswa_id', $siswa->id)
            ->whereNull('deleted_at')
            ->where('status', 'aktif')
            ->whereDate('tanggal_masuk', '<=', $tanggal->toDateString())
            ->where(function ($query) use ($tanggal) {
                $query->whereNull('tanggal_keluar')
                    ->orWhereDate('tanggal_keluar', '>=', $tanggal->toDateString());
            })
            ->pluck('kelas_id');
    }
    private function ensureSholatQrAvailability(SholatSession $session): SholatSetting
    {
        if (! $session->tanggal->isToday()) {
            throw ValidationException::withMessages(['session' => 'QR presensi sholat hanya dapat digunakan pada tanggal sesi berlangsung.']);
        }

        $setting = $session->sholatSetting ?: SholatSetting::query()->find($session->sholat_setting_id);
        if (! $setting || ! $setting->is_active) {
            throw ValidationException::withMessages(['session' => 'Pengaturan sholat untuk sesi ini tidak aktif.']);
        }

        $now = now();
        $dayAliases = [
            strtolower($now->englishDayOfWeek),
            strtolower($now->locale('id')->isoFormat('dddd')),
            (string) $now->dayOfWeekIso,
        ];
        if (! empty($setting->hari_aktif) && ! array_intersect($dayAliases, array_map('strtolower', (array) $setting->hari_aktif))) {
            throw ValidationException::withMessages(['session' => 'QR presensi sholat tidak tersedia pada hari ini.']);
        }

        $currentTime = $now->format('H:i');
        if (($setting->jam_mulai && $currentTime < $setting->jam_mulai)
            || ($setting->jam_selesai && $currentTime > $setting->jam_selesai)) {
            throw ValidationException::withMessages(['session' => 'QR presensi sholat hanya tersedia pada waktu yang telah ditentukan.']);
        }

        return $setting;
    }

    private function isStudentInClass(Siswa $siswa, string $kelasId, CarbonInterface $tanggal, ?string $tahunPelajaranId = null): bool
    {
        return DB::table('siswa_kelas')
            ->where('siswa_id', $siswa->id)
            ->where('kelas_id', $kelasId)
            ->whereNull('deleted_at')
            ->where('status', 'aktif')
            ->when($tahunPelajaranId, fn ($query) => $query->where('tahun_pelajaran_id', $tahunPelajaranId))
            ->whereDate('tanggal_masuk', '<=', $tanggal->toDateString())
            ->where(function ($query) use ($tanggal) {
                $query->whereNull('tanggal_keluar')
                    ->orWhereDate('tanggal_keluar', '>=', $tanggal->toDateString());
            })
            ->exists();
    }
}
