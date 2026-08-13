<?php

namespace App\Services;

use App\Models\AbsensiOperationalSchedule;
use App\Models\HariLibur;
use Carbon\Carbon;

class AttendanceWindowService
{
    public function state(string $userType, ?Carbon $at = null): array
    {
        $userType = $userType === 'siswa' ? 'siswa' : 'gtk';
        $at = ($at ?: now())->copy()->timezone(config('app.timezone', 'Asia/Jakarta'));
        $schedule = AbsensiOperationalSchedule::query()
            ->where('user_type', $userType)
            ->where('day_of_week', $at->dayOfWeekIso)
            ->first();

        $base = [
            'user_type' => $userType,
            'server_time' => $at->toIso8601String(),
            'mode' => 'closed',
            'status' => null,
            'is_open' => false,
            'schedule' => $schedule ? $this->serializeSchedule($schedule) : null,
            'next_at' => null,
        ];

        if (! $schedule || ! $schedule->is_active || HariLibur::isHoliday($at)) {
            return array_merge($base, [
                'reason' => HariLibur::isHoliday($at) ? 'Presensi ditutup karena hari libur.' : 'Presensi tidak dijadwalkan pada hari ini.',
                'next_at' => $this->nextWorkingCheckIn($userType, $at),
            ]);
        }

        $minute = ((int) $at->format('H')) * 60 + (int) $at->format('i');
        $checkInOpen = $this->minutes($schedule->check_in_open);
        $onTimeUntil = $this->minutes($schedule->on_time_until);
        $checkInClose = $this->minutes($schedule->check_in_close);
        $checkOutOpen = $this->minutes($schedule->check_out_open);
        $checkOutClose = $this->minutes($schedule->check_out_close);

        if ($minute >= $checkInOpen && $minute <= $checkInClose) {
            return array_merge($base, [
                'mode' => 'masuk',
                'status' => $minute <= $onTimeUntil ? 'hadir' : 'terlambat',
                'is_open' => true,
                'reason' => $minute <= $onTimeUntil ? 'Presensi masuk tepat waktu sedang dibuka.' : 'Presensi masuk masih dibuka dengan status terlambat.',
                'next_at' => $this->atTime($at, $schedule->check_in_close)->addMinute()->toIso8601String(),
            ]);
        }

        if ($minute >= $checkOutOpen && $minute <= $checkOutClose) {
            return array_merge($base, [
                'mode' => 'pulang',
                'is_open' => true,
                'reason' => 'Presensi pulang sedang dibuka.',
                'next_at' => $this->atTime($at, $schedule->check_out_close)->addMinute()->toIso8601String(),
            ]);
        }

        if ($minute < $checkInOpen) {
            return array_merge($base, [
                'reason' => 'Presensi masuk belum dibuka.',
                'next_at' => $this->atTime($at, $schedule->check_in_open)->toIso8601String(),
            ]);
        }

        if ($minute < $checkOutOpen) {
            return array_merge($base, [
                'reason' => 'Presensi masuk telah ditutup. Menunggu jadwal pulang.',
                'next_at' => $this->atTime($at, $schedule->check_out_open)->toIso8601String(),
            ]);
        }

        return array_merge($base, [
            'reason' => 'Jadwal presensi hari ini telah selesai.',
            'next_at' => $this->nextWorkingCheckIn($userType, $at),
        ]);
    }

    private function nextWorkingCheckIn(string $userType, Carbon $from): ?string
    {
        for ($offset = 1; $offset <= 14; $offset++) {
            $date = $from->copy()->addDays($offset)->startOfDay();
            $schedule = AbsensiOperationalSchedule::query()
                ->where('user_type', $userType)
                ->where('day_of_week', $date->dayOfWeekIso)
                ->where('is_active', true)
                ->first();
            if ($schedule && ! HariLibur::isHoliday($date)) {
                return $this->atTime($date, $schedule->check_in_open)->toIso8601String();
            }
        }

        return null;
    }

    private function serializeSchedule(AbsensiOperationalSchedule $schedule): array
    {
        return [
            'day' => $schedule->day_label,
            'active' => $schedule->is_active,
            'check_in_open' => $schedule->shortTime('check_in_open'),
            'on_time_until' => $schedule->shortTime('on_time_until'),
            'check_in_close' => $schedule->shortTime('check_in_close'),
            'check_out_open' => $schedule->shortTime('check_out_open'),
            'check_out_close' => $schedule->shortTime('check_out_close'),
        ];
    }

    private function minutes(string $time): int
    {
        [$hour, $minute] = array_map('intval', explode(':', $time));

        return $hour * 60 + $minute;
    }

    private function atTime(Carbon $date, string $time): Carbon
    {
        return Carbon::parse($date->format('Y-m-d').' '.$time, config('app.timezone', 'Asia/Jakarta'));
    }
}
