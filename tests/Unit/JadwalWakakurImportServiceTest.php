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
            $this->assertSame('56', $result['slots'][0]['kode_gtk']);
            $this->assertSame('S', $result['slots'][0]['kode_mapel']);
        } finally {
            restore_error_handler();
            @unlink($file);
        }
    }
}
