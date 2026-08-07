<?php

namespace Tests\Unit;

use App\Services\JadwalWakakurImportService;
use App\Models\JadwalJamConfig;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PHPUnit\Framework\TestCase;

class JadwalWakakurImportServiceTest extends TestCase
{
    public function test_it_reads_wakakur_codes_and_normalizes_class_names(): void
    {
        $file = sys_get_temp_dir().'/jadwal-wakakur-'.uniqid('', true).'.xlsx';
        set_error_handler(static fn (int $severity): bool => $severity === E_DEPRECATED);
        $spreadsheet = new Spreadsheet();
        $codes = $spreadsheet->getActiveSheet();
        $codes->setTitle('Kode_GTK_mapel');
        $codes->fromArray([
            ['Kode', 'Nama Guru', null, 'Kode', 'Nama Guru', null, 'Kode', 'Mata Pelajaran'],
            ['56', 'Guru S', null, '57', 'Guru A', null, 'A', "Qur'an Hadist"],
            [null, null, null, null, null, null, 'S', 'Penjas ORKES'],
        ], null, 'A10');

        $jadwal = $spreadsheet->createSheet();
        $jadwal->setTitle('jadwal');
        $jadwal->fromArray([
            ['HARI', 'JAM', 'Kelas X', 'Kelas XII A', 'BK'],
            [null, 'KE', 1, 1, 'K.1'],
            ['SENIN', 1, '56S', '57A', '99Z'],
            [null, 0, 'UPACARA'],
        ], null, 'A5');
        (new Xlsx($spreadsheet))->save($file);

        try {
            $result = (new JadwalWakakurImportService())->preview($file);

            $this->assertCount(2, $result['slots']);
            $this->assertSame(1, $result['ignored']);
            $this->assertSame([], $result['warnings']);
            $this->assertSame('X-1', $result['slots'][0]['kelas_nama']);
            $this->assertSame('12-A1', $result['slots'][1]['kelas_nama']);
            $this->assertSame('XIIA1', (new JadwalWakakurImportService())->classKey('12-A1'));
            $this->assertSame('56', $result['slots'][0]['kode_gtk']);
            $this->assertSame('S', $result['slots'][0]['kode_mapel']);
            $this->assertSame(1, $result['dayMaxJam']['senin']);
        } finally {
            restore_error_handler();
            @unlink($file);
        }
    }

    public function test_importer_uses_exact_excel_name_when_a_revised_gtk_code_is_not_mapped(): void
    {
        $controller = file_get_contents(dirname(__DIR__, 2).'/app/Http/Controllers/Admin/JadwalPelajaranController.php');

        $this->assertStringContainsString('$gtkByExactName', $controller);
        $this->assertStringContainsString('normalizePersonName($slot[\'gtk_excel\'])', $controller);
        $this->assertStringContainsString('$gtkDariNama ?? $gtkDariKode', $controller);
        $this->assertStringContainsString('tidak sesuai dengan nama guru pada file Wakakur', $controller);
    }

    public function test_import_requires_configured_time_slots_before_committing_schedule(): void
    {
        $controller = file_get_contents(dirname(__DIR__, 2).'/app/Http/Controllers/Admin/JadwalPelajaranController.php');

        $this->assertStringContainsString("'missing_time_slots' => \$missingTimeSlots", $controller);
        $this->assertStringContainsString('Lengkapi konfigurasi slot jam pelajaran sebelum mengimpor jadwal.', $controller);
    }

    public function test_generate_jam_config_syncs_the_daily_slots_used_by_import(): void
    {
        $controller = file_get_contents(dirname(__DIR__, 2).'/app/Http/Controllers/Admin/JadwalJamConfigController.php');

        $this->assertStringContainsString('$hariSekolah = $tahun->hariKerja()', $controller);
        $this->assertStringContainsString('JadwalHariJam::create', $controller);
        $this->assertStringContainsString('JadwalPelajaran::where', $controller);
        $this->assertStringContainsString('sinkronkanSlotDanJadwal', $controller);
        $this->assertStringContainsString('whereNotExists', $controller);
    }

    public function test_import_uses_each_excel_days_last_period_and_marks_blank_slots(): void
    {
        $controller = file_get_contents(dirname(__DIR__, 2).'/app/Http/Controllers/Admin/JadwalPelajaranController.php');
        $timetable = file_get_contents(dirname(__DIR__, 2).'/resources/views/admin/jadwal-pelajaran/timetable.blade.php');
        $publicMonitor = file_get_contents(dirname(__DIR__, 2).'/resources/views/public/jadwal-monitor.blade.php');

        $this->assertStringContainsString("'day_max_jam' => \$parsed['dayMaxJam']", $controller);
        $this->assertStringContainsString("->where('urutan', '>', \$slotTerakhir->urutan)", $controller);
        $this->assertStringContainsString('Jadwal masih kosong', $timetable);
        $this->assertStringContainsString('Jadwal masih kosong pada jam ini.', $publicMonitor);
    }

    public function test_generator_accepts_a_sixty_minute_second_break_until_half_past_four(): void
    {
        $rows = JadwalJamConfig::generateRows('07:00', 45, [
            ['setelah_jam' => 3, 'durasi' => 15, 'label' => 'Istirahat'],
            ['setelah_jam' => 6, 'durasi' => 60, 'label' => 'Istirahat Sholat'],
        ], '16:30');

        $this->assertSame(11, collect($rows)->where('is_istirahat', false)->count());
        $this->assertSame('11:45', $rows[7]['waktu_mulai']);
        $this->assertSame('12:45', $rows[7]['waktu_selesai']);
        $this->assertSame('16:30', $rows[array_key_last($rows)]['waktu_selesai']);
    }

    public function test_import_confirmation_uses_sweetalert_instead_of_browser_confirm(): void
    {
        $view = file_get_contents(dirname(__DIR__, 2).'/resources/views/admin/jadwal-pelajaran/import.blade.php');

        $this->assertStringContainsString("@section('plugins.Sweetalert2', true)", $view);
        $this->assertStringContainsString("Swal.fire({", $view);
        $this->assertStringNotContainsString("if(!confirm(", $view);
    }

    public function test_schedule_monitor_uses_current_slot_and_fullscreen_view(): void
    {
        $controller = file_get_contents(dirname(__DIR__, 2).'/app/Http/Controllers/Admin/JadwalPelajaranController.php');
        $view = file_get_contents(dirname(__DIR__, 2).'/resources/views/admin/jadwal-pelajaran/monitor.blade.php');

        $this->assertStringContainsString('public function monitor()', $controller);
        $this->assertStringContainsString("now('Asia/Jakarta')", $controller);
        $this->assertStringContainsString('requestFullscreen', $view);
        $this->assertStringContainsString('SEDANG BERLANGSUNG', $view);
        $this->assertStringContainsString('jm-class--tone-', $view);
        $this->assertStringContainsString('nextLesson', $view);
        $this->assertStringContainsString('jm-focus--break', $view);
    }

    public function test_public_schedule_monitor_uses_the_same_live_schedule_data(): void
    {
        $controller = file_get_contents(dirname(__DIR__, 2).'/app/Http/Controllers/Admin/JadwalPelajaranController.php');
        $routes = file_get_contents(dirname(__DIR__, 2).'/routes/web.php');
        $view = file_get_contents(dirname(__DIR__, 2).'/resources/views/public/jadwal-monitor.blade.php');

        $this->assertStringContainsString('public function publicMonitor()', $controller);
        $this->assertStringContainsString('dataMonitor()', $controller);
        $this->assertStringContainsString("Route::get('/monitor-jadwal'", $routes);
        $this->assertStringContainsString("name('public.jadwal-monitor')", $routes);
        $this->assertStringContainsString('MONITOR GURU PIKET', $view);
    }
}
