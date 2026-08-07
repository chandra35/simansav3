<?php

namespace Tests\Unit;

use App\Services\JadwalWakakurImportService;
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
}
