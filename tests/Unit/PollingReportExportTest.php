<?php

namespace Tests\Unit;

use App\Exports\PollingReportExport;
use App\Models\Polling;
use App\Models\PollingQuestion;
use Maatwebsite\Excel\Excel as ExcelFormat;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use Tests\TestCase;

class PollingReportExportTest extends TestCase
{
    public function test_student_contact_and_confidentiality_signature_are_exported(): void
    {
        $polling = new Polling(['title' => 'Polling TKA', 'slug' => 'polling-tka']);
        $polling->id = 'polling-test';
        $question = new PollingQuestion(['prompt' => 'Mata Pelajaran Pilihan', 'type' => 'single']);
        $question->id = 'question-test';
        $polling->setRelation('questions', collect([$question]));

        $rows = collect([[
            'type' => 'siswa',
            'name' => 'Budi Santoso',
            'username' => '0012345678',
            'student_phone' => '081234567890',
            'student_email' => 'budi@example.test',
            'grade' => 12,
            'class_name' => 'XII-A1',
            'answered' => true,
            'submitted_at' => now(),
            'answers' => ['question-test' => 'Matematika Tingkat Lanjut'],
        ]]);

        $binary = Excel::raw(new PollingReportExport($polling, $rows, [
            'exported_by' => 'Administrator',
            'exported_at' => '12/08/2026 13:00:00 WIB',
            'signature' => 'SIMANSA-ABCDE-FGHIJ',
        ]), ExcelFormat::XLSX);

        $temporaryFile = tempnam(sys_get_temp_dir(), 'polling-report-');
        file_put_contents($temporaryFile, $binary);

        try {
            $sheet = IOFactory::load($temporaryFile)->getActiveSheet();

            $this->assertSame('No. HP Siswa', $sheet->getCell('E1')->getValue());
            $this->assertSame('Email Siswa', $sheet->getCell('F1')->getValue());
            $this->assertSame('081234567890', $sheet->getCell('E2')->getValue());
            $this->assertSame(DataType::TYPE_STRING, $sheet->getCell('E2')->getDataType());
            $this->assertSame('budi@example.test', $sheet->getCell('F2')->getValue());
            $this->assertSame('PERNYATAAN KERAHASIAAN DAN TANGGUNG JAWAB DATA', $sheet->getCell('A5')->getValue());
            $this->assertStringContainsString('kebocoran data menjadi tanggung jawab', $sheet->getCell('A6')->getValue());
            $this->assertStringContainsString('izin dari pihak sekolah', $sheet->getCell('A7')->getValue());
            $this->assertSame('Signature: SIMANSA-ABCDE-FGHIJ', $sheet->getCell('A8')->getValue());
            $this->assertStringContainsString('Administrator', $sheet->getCell('A9')->getValue());
        } finally {
            @unlink($temporaryFile);
        }
    }
}
