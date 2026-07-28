<?php

namespace Tests\Unit;

use App\Exports\SiswaExport;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PHPUnit\Framework\TestCase;

class SiswaExportIdentifierTest extends TestCase
{
    public function test_long_identifiers_remain_exact_text_after_xlsx_round_trip(): void
    {
        $export = new SiswaExport(collect());
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $identifiers = [
            'C2' => '0012345678',
            'D2' => '131118720001240078',
            'H2' => '1872014406900007',
            'S2' => '1872010202700009',
            'X2' => '1872015503800013',
            'AB2' => '1872014406900008',
        ];

        foreach ($identifiers as $coordinate => $value) {
            $export->bindValue($sheet->getCell($coordinate), $value);
        }

        $path = tempnam(sys_get_temp_dir(), 'simansa-siswa-export-').'.xlsx';

        try {
            (new Xlsx($spreadsheet))->save($path);
            $reloaded = IOFactory::load($path)->getActiveSheet();

            foreach ($identifiers as $coordinate => $value) {
                $this->assertSame($value, $reloaded->getCell($coordinate)->getValue());
                $this->assertSame(DataType::TYPE_STRING, $reloaded->getCell($coordinate)->getDataType());
            }
        } finally {
            @unlink($path);
        }
    }

    public function test_column_metadata_matches_all_export_headings(): void
    {
        $export = new SiswaExport(collect());

        $this->assertCount(34, $export->headings());
        $this->assertArrayHasKey('AH', $export->columnWidths());
        $this->assertSame('@', $export->columnFormats()['H']);
        $this->assertSame('@', $export->columnFormats()['AB']);
    }
}
