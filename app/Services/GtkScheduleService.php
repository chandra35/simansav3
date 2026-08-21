<?php

namespace App\Services;

use App\Models\AppSetting;
use App\Models\Gtk;
use App\Models\JadwalPelajaran;
use App\Models\TahunPelajaran;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class GtkScheduleService
{
    public function schedulesForDay(Gtk $gtk, ?TahunPelajaran $year, CarbonInterface $date): Collection
    {
        if (! $year) {
            return collect();
        }

        return JadwalPelajaran::query()
            ->with(['mataPelajaran:id,nama_mapel', 'kelas:id,nama_kelas,ruang_kelas'])
            ->where('gtk_id', $gtk->id)
            ->where('tahun_pelajaran_id', $year->id)
            ->where('hari', $this->dayKey($date))
            ->where('is_active', true)
            ->orderBy('jam_mulai')
            ->orderBy('jam_ke')
            ->get();
    }

    public function schedulesForWeek(Gtk $gtk, ?TahunPelajaran $year): Collection
    {
        if (! $year) {
            return collect();
        }

        return JadwalPelajaran::query()
            ->with(['mataPelajaran:id,nama_mapel', 'kelas:id,nama_kelas,ruang_kelas'])
            ->where('gtk_id', $gtk->id)
            ->where('tahun_pelajaran_id', $year->id)
            ->where('is_active', true)
            ->orderByRaw("FIELD(hari, 'senin','selasa','rabu','kamis','jumat','sabtu')")
            ->orderBy('jam_mulai')
            ->orderBy('jam_ke')
            ->get();
    }

    /**
     * Add presentation data shared by the dashboard and weekly schedule.
     * A room that is identical to the class name is legacy data, not a second class.
     */
    public function decorateSchedules(Collection $schedules, ?CarbonInterface $now = null): Collection
    {
        $nextSchedule = null;

        if ($now) {
            $nextSchedule = $schedules->first(function (JadwalPelajaran $schedule) use ($now) {
                return $schedule->jam_mulai
                    && $this->startsAt($schedule, $now)->greaterThan($now);
            });
        }

        return $schedules->each(function (JadwalPelajaran $schedule) use ($now, $nextSchedule) {
            $schedule->setAttribute('location_label', $this->locationLabel($schedule));

            if (! $now) {
                return;
            }

            $status = 'upcoming';
            if ($schedule->jam_mulai) {
                $startsAt = $this->startsAt($schedule, $now);
                $endsAt = $schedule->jam_selesai
                    ? $now->copy()->setTimeFromTimeString(substr((string) $schedule->jam_selesai, 0, 5))
                    : null;

                if ($endsAt && $now->greaterThanOrEqualTo($endsAt)) {
                    $status = 'completed';
                } elseif ($now->greaterThanOrEqualTo($startsAt)) {
                    $status = 'ongoing';
                } elseif ($nextSchedule && $schedule->is($nextSchedule)) {
                    $status = 'next';
                }
            }

            $schedule->setAttribute('dashboard_status', $status);
        });
    }

    public function locationLabel(JadwalPelajaran $schedule): string
    {
        $class = trim((string) ($schedule->kelas?->nama_kelas ?? ''));
        $room = trim((string) ($schedule->ruangan ?: $schedule->kelas?->ruang_kelas ?: ''));

        if ($room && mb_strtolower($room) !== mb_strtolower($class)) {
            return trim($class.' · '.$room, ' ·');
        }

        return $class ?: ($room ?: '-');
    }

    public function reminder(Collection $schedules, Gtk $gtk, AppSetting $settings, CarbonInterface $now): ?array
    {
        if (! $settings->gtk_schedule_reminder_enabled) {
            return null;
        }

        $minutes = max(1, (int) $settings->gtk_schedule_reminder_minutes);
        $upcoming = $schedules->first(function (JadwalPelajaran $schedule) use ($now, $minutes) {
            if (! $schedule->jam_mulai) {
                return false;
            }

            $startsAt = $now->copy()->setTimeFromTimeString(substr((string) $schedule->jam_mulai, 0, 5));
            return $startsAt->greaterThanOrEqualTo($now)
                && $startsAt->lessThanOrEqualTo($now->copy()->addMinutes($minutes));
        });

        if (! $upcoming) {
            return null;
        }

        $startsAt = $now->copy()->setTimeFromTimeString(substr((string) $upcoming->jam_mulai, 0, 5));
        $remaining = max(0, (int) ceil($now->diffInSeconds($startsAt, false) / 60));
        $greeting = $this->greeting($gtk, $settings);
        $subject = $upcoming->mataPelajaran?->nama_mapel ?? 'jadwal mengajar';
        $class = $upcoming->kelas?->nama_kelas ?? 'kelas Anda';
        $lead = $remaining === 0
            ? ['Jadwal dimulai sekarang', 'Saatnya menuju kelas', 'Jangan lupa, jadwal sudah dimulai']
            : ["{$remaining} menit lagi", "Segera dimulai dalam {$remaining} menit", "Jangan lupa, {$remaining} menit lagi"];
        $prefix = $greeting ? $greeting.', ' : '';

        return [
            'schedule' => $upcoming,
            'message' => $prefix.$lead[array_rand($lead)].": {$subject} di {$class}.",
        ];
    }

    public function greeting(Gtk $gtk, AppSetting $settings): ?string
    {
        if (! $settings->gtk_schedule_salutation_enabled) {
            return null;
        }

        $birthYear = $gtk->tanggal_lahir ? (int) substr((string) $gtk->tanggal_lahir, 0, 4) : null;
        $senior = $birthYear !== null && $birthYear <= 1984;
        $male = $gtk->jenis_kelamin === 'L';
        $salutation = match (true) {
            $male && $senior => $settings->gtk_salutation_male_senior,
            ! $male && $senior => $settings->gtk_salutation_female_senior,
            $male => $settings->gtk_salutation_male_young,
            default => $settings->gtk_salutation_female_young,
        };

        return trim(($salutation ?: '').' '.($gtk->nama_lengkap ?: '')) ?: null;
    }

    private function dayKey(CarbonInterface $date): string
    {
        return [1 => 'senin', 2 => 'selasa', 3 => 'rabu', 4 => 'kamis', 5 => 'jumat', 6 => 'sabtu'][$date->dayOfWeekIso] ?? '';
    }

    private function startsAt(JadwalPelajaran $schedule, CarbonInterface $now): CarbonInterface
    {
        return $now->copy()->setTimeFromTimeString(substr((string) $schedule->jam_mulai, 0, 5));
    }
}
