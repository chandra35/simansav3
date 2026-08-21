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
}
