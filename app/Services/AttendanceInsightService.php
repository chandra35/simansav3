<?php

namespace App\Services;

use App\Models\AttendanceAlert;
use App\Models\TahunPelajaran;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AttendanceInsightService
{
    public function generate(TahunPelajaran $year, ?Collection $classIds = null): array
    {
        $end = now()->endOfDay();
        if ($year->tanggal_selesai && $year->tanggal_selesai->endOfDay()->lt($end)) {
            $end = $year->tanggal_selesai->endOfDay();
        }
        $start = $end->copy()->subDays(29);
        if ($year->tanggal_mulai && $year->tanggal_mulai->startOfDay()->gt($start)) {
            $start = $year->tanggal_mulai->startOfDay();
        }

        $query = DB::table('absensi_siswa_records as records')
            ->join('absensi_siswa_sessions as sessions', 'sessions.id', '=', 'records.session_id')
            ->whereNull('records.deleted_at')
            ->whereNull('sessions.deleted_at')
            ->where('sessions.tahun_pelajaran_id', $year->id)
            ->where('sessions.status', 'final')
            ->whereBetween('sessions.tanggal', [$start->toDateString(), $end->toDateString()]);

        if ($classIds !== null) {
            $query->whereIn('sessions.kelas_id', $classIds);
        }

        $rows = $query->select([
            'records.siswa_id', 'records.status', 'sessions.tanggal', 'sessions.mode',
            'sessions.mapel_id', 'sessions.mapel_snapshot', 'sessions.kelas_id', 'sessions.tingkat',
        ])->get();

        $dailyQuery = DB::table('absensis as daily')
            ->join('siswa', 'siswa.user_id', '=', 'daily.user_id')
            ->whereNull('daily.deleted_at')->whereNull('siswa.deleted_at')
            ->where('daily.user_type', 'siswa')->where('daily.tahun_pelajaran_id', $year->id)
            ->whereBetween('daily.tanggal', [$start->toDateString(), $end->toDateString()]);
        if ($classIds !== null) {
            $dailyQuery->whereExists(function ($membership) use ($year, $classIds) {
                $membership->selectRaw('1')->from('siswa_kelas')
                    ->whereColumn('siswa_kelas.siswa_id', 'siswa.id')
                    ->where('siswa_kelas.tahun_pelajaran_id', $year->id)
                    ->whereNull('siswa_kelas.deleted_at')
                    ->whereIn('siswa_kelas.kelas_id', $classIds);
            });
        }
        $dailyRows = $dailyQuery->select([
            'siswa.id as siswa_id', 'daily.status', 'daily.tanggal', 'daily.waktu_masuk',
            'daily.waktu_pulang', 'daily.metode_masuk', 'daily.metode_pulang',
        ])->get();
        $dailyByStudent = $dailyRows->groupBy('siswa_id');

        $studentIds = $rows->pluck('siswa_id')->merge($dailyRows->pluck('siswa_id'))->unique()->values();
        if ($studentIds->isNotEmpty()) {
            AttendanceAlert::query()
                ->where('tahun_pelajaran_id', $year->id)
                ->whereIn('siswa_id', $studentIds)
                ->where('is_active', true)
                ->update(['is_active' => false]);
        }

        $detected = 0;
        $bySeverity = ['low' => 0, 'medium' => 0, 'high' => 0];
        foreach ($rows->groupBy('siswa_id') as $studentId => $studentRows) {
            $subjectRows = $studentRows->where('mode', 'mapel');
            $recent14 = $subjectRows->filter(fn ($row) => Carbon::parse($row->tanggal)->gte($end->copy()->subDays(13)));
            $absent14 = $recent14->where('status', 'alpa')->count();
            if ($absent14 >= 3) {
                $severity = $absent14 >= 5 ? 'high' : 'medium';
                $this->upsertAlert($year, $studentId, 'frequent_subject_absence', 'all', $severity,
                    min(100, 45 + ($absent14 * 8)),
                    'Alpa mapel berulang',
                    "Terdapat {$absent14} alpa pada pembelajaran dalam 14 hari terakhir.",
                    ['alpa_14_hari' => $absent14, 'total_sesi_14_hari' => $recent14->count()], $start, $end);
                $detected++;
                $bySeverity[$severity]++;
            }

            $totalSubject = $subjectRows->count();
            $nonPresent = $subjectRows->whereIn('status', ['alpa'])->count();
            $absenceRate = $totalSubject ? round(($nonPresent / $totalSubject) * 100, 1) : 0;
            if ($totalSubject >= 10 && $absenceRate >= 10) {
                $severity = $absenceRate >= 25 ? 'high' : 'medium';
                $this->upsertAlert($year, $studentId, 'low_subject_attendance', 'all', $severity,
                    min(100, (int) round(40 + $absenceRate * 1.8)),
                    'Persentase kehadiran mapel menurun',
                    "Alpa {$absenceRate}% dari {$totalSubject} sesi pembelajaran final dalam 30 hari.",
                    ['persentase_alpa' => $absenceRate, 'alpa' => $nonPresent, 'total_sesi' => $totalSubject], $start, $end);
                $detected++;
                $bySeverity[$severity]++;
            }

            $lateCount = $subjectRows->where('status', 'terlambat')->count();
            if ($lateCount >= 3) {
                $severity = $lateCount >= 6 ? 'high' : 'medium';
                $this->upsertAlert($year, $studentId, 'repeated_lateness', 'all', $severity,
                    min(100, 40 + ($lateCount * 7)),
                    'Keterlambatan pembelajaran berulang',
                    "Tercatat terlambat pada {$lateCount} sesi pembelajaran dalam 30 hari.",
                    ['terlambat' => $lateCount, 'total_sesi' => $totalSubject], $start, $end);
                $detected++;
                $bySeverity[$severity]++;
            }

            foreach ($subjectRows->groupBy(fn ($row) => $row->mapel_id ?: $row->mapel_snapshot ?: 'unknown') as $subjectKey => $subjectGroup) {
                $subjectAbsence = $subjectGroup->where('status', 'alpa')->count();
                if ($subjectGroup->count() < 4 || $subjectAbsence < 3) {
                    continue;
                }
                $subjectName = $subjectGroup->first()->mapel_snapshot ?: 'mapel tertentu';
                $severity = $subjectAbsence >= 5 ? 'high' : 'medium';
                $this->upsertAlert($year, $studentId, 'subject_specific_absence', (string) $subjectKey, $severity,
                    min(100, 50 + ($subjectAbsence * 7)),
                    'Alpa terkonsentrasi pada satu mapel',
                    "Alpa {$subjectAbsence} dari {$subjectGroup->count()} pertemuan {$subjectName}.",
                    ['mapel' => $subjectName, 'alpa' => $subjectAbsence, 'total_sesi' => $subjectGroup->count()], $start, $end);
                $detected++;
                $bySeverity[$severity]++;
            }

            $dailyForStudent = $dailyByStudent->get($studentId, collect());
            $dailyLateCount = $dailyForStudent->where('status', 'terlambat')->count();
            if ($dailyLateCount >= 3) {
                $severity = $dailyLateCount >= 6 ? 'high' : 'medium';
                $this->upsertAlert($year, $studentId, 'repeated_daily_lateness', 'gate', $severity,
                    min(100, 40 + ($dailyLateCount * 7)),
                    'Keterlambatan datang berulang',
                    "Presensi wajah mencatat {$dailyLateCount} keterlambatan datang dalam 30 hari.",
                    ['terlambat_harian' => $dailyLateCount, 'total_presensi_harian' => $dailyForStudent->count()], $start, $end);
                $detected++;
                $bySeverity[$severity]++;
            }

            $dailyByDate = $dailyForStudent->keyBy('tanggal');
            $conflicts = 0;
            foreach ($subjectRows->groupBy('tanggal') as $date => $daySubjects) {
                $daily = $dailyByDate->get($date);
                if (! $daily) {
                    continue;
                }
                $subjectPresent = $daySubjects->whereIn('status', ['hadir', 'terlambat', 'keluar_awal'])->count();
                $subjectAbsent = $daySubjects->where('status', 'alpa')->count();
                if ((in_array($daily->status, ['hadir', 'terlambat'], true) && $subjectAbsent >= 2)
                    || (in_array($daily->status, ['sakit', 'izin', 'alpa'], true) && $subjectPresent > 0)) {
                    $conflicts++;
                }
            }
            if ($conflicts >= 1) {
                $severity = $conflicts >= 3 ? 'high' : 'medium';
                $this->upsertAlert($year, $studentId, 'daily_subject_conflict', 'all', $severity,
                    min(100, 55 + ($conflicts * 10)),
                    'Konflik absensi harian dan mapel',
                    "Ditemukan {$conflicts} hari dengan status harian dan mapel yang tidak konsisten.",
                    ['jumlah_hari_konflik' => $conflicts], $start, $end);
                $detected++;
                $bySeverity[$severity]++;
            }
        }

        // Siswa yang baru memiliki presensi harian tetap dianalisis keterlambatannya.
        foreach ($dailyByStudent->except($rows->pluck('siswa_id')->unique()->all()) as $studentId => $dailyForStudent) {
            $dailyLateCount = $dailyForStudent->where('status', 'terlambat')->count();
            if ($dailyLateCount < 3) {
                continue;
            }
            $severity = $dailyLateCount >= 6 ? 'high' : 'medium';
            $this->upsertAlert($year, $studentId, 'repeated_daily_lateness', 'gate', $severity,
                min(100, 40 + ($dailyLateCount * 7)), 'Keterlambatan datang berulang',
                "Presensi wajah mencatat {$dailyLateCount} keterlambatan datang dalam 30 hari.",
                ['terlambat_harian' => $dailyLateCount, 'total_presensi_harian' => $dailyForStudent->count()], $start, $end);
            $detected++;
            $bySeverity[$severity]++;
        }

        return [
            'students_scanned' => $studentIds->count(),
            'alerts_detected' => $detected,
            'severity' => $bySeverity,
            'period_start' => $start->toDateString(),
            'period_end' => $end->toDateString(),
        ];
    }

    private function upsertAlert(
        TahunPelajaran $year,
        string $studentId,
        string $rule,
        string $dimension,
        string $severity,
        int $score,
        string $title,
        string $explanation,
        array $evidence,
        Carbon $start,
        Carbon $end
    ): void {
        $fingerprint = hash('sha256', implode('|', [$year->id, $studentId, $rule, $dimension]));
        $existing = AttendanceAlert::query()->where('fingerprint', $fingerprint)->first();
        $reopened = $existing && ! $existing->is_active && in_array($existing->status, ['resolved', 'dismissed'], true);

        AttendanceAlert::updateOrCreate(['fingerprint' => $fingerprint], [
            'siswa_id' => $studentId,
            'tahun_pelajaran_id' => $year->id,
            'rule_code' => $rule,
            'severity' => $severity,
            'score' => $score,
            'title' => $title,
            'explanation' => $explanation,
            'evidence' => $evidence,
            'period_start' => $start->toDateString(),
            'period_end' => $end->toDateString(),
            'status' => $reopened ? 'new' : ($existing?->status ?? 'new'),
            'is_active' => true,
            'first_detected_at' => $existing?->first_detected_at ?? now(),
            'last_detected_at' => now(),
        ]);
    }
}
