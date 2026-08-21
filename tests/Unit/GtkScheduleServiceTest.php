<?php

namespace Tests\Unit;

use App\Models\AppSetting;
use App\Models\Gtk;
use App\Models\JadwalPelajaran;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Services\GtkScheduleService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Tests\TestCase;

class GtkScheduleServiceTest extends TestCase
{
    public function test_it_uses_configured_salutation_by_gender_and_birth_year(): void
    {
        $service = new GtkScheduleService();
        $settings = new AppSetting([
            'gtk_schedule_salutation_enabled' => true,
            'gtk_salutation_male_senior' => 'Pak',
            'gtk_salutation_female_senior' => 'Bu',
            'gtk_salutation_male_young' => 'Mas',
            'gtk_salutation_female_young' => 'Mbak',
        ]);

        $senior = new Gtk(['nama_lengkap' => 'Budi', 'jenis_kelamin' => 'L', 'tanggal_lahir' => '1984-12-31']);
        $young = new Gtk(['nama_lengkap' => 'Sari', 'jenis_kelamin' => 'P', 'tanggal_lahir' => '1985-01-01']);

        $this->assertSame('Pak Budi', $service->greeting($senior, $settings));
        $this->assertSame('Mbak Sari', $service->greeting($young, $settings));
    }

    public function test_it_reminds_when_a_schedule_starts_within_the_configured_window(): void
    {
        $service = new GtkScheduleService();
        $settings = new AppSetting([
            'gtk_schedule_reminder_enabled' => true,
            'gtk_schedule_reminder_minutes' => 5,
            'gtk_schedule_salutation_enabled' => true,
            'gtk_salutation_male_senior' => 'Pak',
            'gtk_salutation_female_senior' => 'Bu',
            'gtk_salutation_male_young' => 'Mas',
            'gtk_salutation_female_young' => 'Mbak',
        ]);
        $gtk = new Gtk(['nama_lengkap' => 'Budi', 'jenis_kelamin' => 'L', 'tanggal_lahir' => '1984-01-01']);
        $schedule = new JadwalPelajaran(['jam_mulai' => '08:00:00']);
        $schedule->setRelation('mataPelajaran', new MataPelajaran(['nama_mapel' => 'Matematika']));
        $schedule->setRelation('kelas', new Kelas(['nama_kelas' => 'X-A']));

        $reminder = $service->reminder(new Collection([$schedule]), $gtk, $settings, Carbon::parse('2026-08-21 07:55:00'));

        $this->assertNotNull($reminder);
        $this->assertStringContainsString('5 menit', $reminder['message']);
        $this->assertStringContainsString('Pak Budi', $reminder['message']);
    }

    public function test_it_removes_duplicate_room_and_marks_live_schedule_status(): void
    {
        $service = new GtkScheduleService();
        $current = new JadwalPelajaran(['jam_mulai' => '08:00:00', 'jam_selesai' => '09:00:00', 'ruangan' => 'XI-A2']);
        $current->setRelation('kelas', new Kelas(['nama_kelas' => 'XI-A2']));
        $next = new JadwalPelajaran(['jam_mulai' => '10:00:00', 'jam_selesai' => '11:00:00', 'ruangan' => 'Lab 1']);
        $next->setRelation('kelas', new Kelas(['nama_kelas' => 'XI-A2']));

        $schedules = $service->decorateSchedules(new Collection([$current, $next]), Carbon::parse('2026-08-21 08:30:00'));

        $this->assertSame('XI-A2', $schedules->first()->location_label);
        $this->assertSame('ongoing', $schedules->first()->dashboard_status);
        $this->assertSame('XI-A2 · Lab 1', $schedules->last()->location_label);
        $this->assertSame('next', $schedules->last()->dashboard_status);
    }
}
