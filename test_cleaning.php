<?php
// Test script untuk verifikasi cleaning NISN/NIK
require __DIR__ . '/vendor/autoload.php';

use App\Imports\SiswaImport;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

echo "🧪 TEST CLEANING NISN/NIK\n";
echo str_repeat("═", 60) . "\n\n";

// Test cases
$testCases = [
    ['input' => '0123456789', 'expected' => '0123456789', 'desc' => 'Normal NISN'],
    ['input' => "'0123456789", 'expected' => '0123456789', 'desc' => 'NISN dengan petik di awal'],
    ['input' => "'0123456789'", 'expected' => '0123456789', 'desc' => 'NISN dengan petik di awal & akhir'],
    ['input' => '0123-456-789', 'expected' => '0123456789', 'desc' => 'NISN dengan dash'],
    ['input' => '0123 456 789', 'expected' => '0123456789', 'desc' => 'NISN dengan spasi'],
    ['input' => 'NISN0123456789', 'expected' => '0123456789', 'desc' => 'NISN dengan prefix teks'],
    ['input' => '0123.456.789', 'expected' => '0123456789', 'desc' => 'NISN dengan titik'],
    ['input' => '  0123456789  ', 'expected' => '0123456789', 'desc' => 'NISN dengan spasi di awal/akhir'],
    ['input' => '=0123456789', 'expected' => '0123456789', 'desc' => 'NISN dengan formula Excel (=)'],
    ['input' => '"0123456789"', 'expected' => '0123456789', 'desc' => 'NISN dengan double quote'],
];

// Create reflection untuk akses protected method
$import = new SiswaImport();
$reflection = new ReflectionClass($import);
$method = $reflection->getMethod('cleanNumericField');
$method->setAccessible(true);

echo "📋 TEST CASES:\n";
echo str_repeat("─", 60) . "\n";

$passed = 0;
$failed = 0;

foreach ($testCases as $i => $test) {
    $result = $method->invoke($import, $test['input']);
    $status = ($result === $test['expected']) ? '✅' : '❌';
    
    if ($result === $test['expected']) {
        $passed++;
    } else {
        $failed++;
    }
    
    echo sprintf(
        "%s Test #%d: %s\n",
        $status,
        $i + 1,
        $test['desc']
    );
    
    echo sprintf(
        "   Input   : '%s'\n",
        $test['input']
    );
    
    echo sprintf(
        "   Expected: '%s'\n",
        $test['expected']
    );
    
    echo sprintf(
        "   Result  : '%s'\n",
        $result
    );
    
    if ($result !== $test['expected']) {
        echo "   ⚠️  FAILED!\n";
    }
    
    echo "\n";
}

echo str_repeat("═", 60) . "\n";
echo "📊 SUMMARY:\n";
echo str_repeat("─", 60) . "\n";
echo sprintf("✅ Passed: %d / %d\n", $passed, count($testCases));
echo sprintf("❌ Failed: %d / %d\n", $failed, count($testCases));
echo sprintf("📈 Success Rate: %.1f%%\n", ($passed / count($testCases)) * 100);
echo str_repeat("═", 60) . "\n";

if ($failed === 0) {
    echo "🎉 ALL TESTS PASSED!\n";
    echo "✅ Function cleanNumericField() bekerja dengan sempurna\n";
    echo "✅ Semua karakter non-angka berhasil dihapus\n";
    echo "✅ NISN/NIK akan bersih dari petik, spasi, dash, dll\n";
} else {
    echo "⚠️  SOME TESTS FAILED!\n";
    echo "❌ Ada " . $failed . " test yang gagal\n";
}

echo str_repeat("═", 60) . "\n";

// Create sample Excel dengan petik
echo "\n📝 MEMBUAT SAMPLE EXCEL DENGAN PETIK...\n";

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Headers
$headers = ['Nama Lengkap', 'NISN', 'NIK', 'Jenis Kelamin'];
$sheet->fromArray($headers, null, 'A1');

// Sample data dengan petik dan karakter lain
$sampleData = [
    ['Ahmad Fauzi', "'0123456789", "'1234567890123456", 'L'],
    ['Siti Nurhaliza', '0123-456-790', '1234.5678.9012.3457', 'P'],
    ['Budi Santoso', '  0123456791  ', '=1234567890123458', 'L'],
];
$sheet->fromArray($sampleData, null, 'A2');

// Style
$headerStyle = [
    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
    'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '4472C4']],
];
$sheet->getStyle('A1:D1')->applyFromArray($headerStyle);

// Auto size
foreach (range('A', 'D') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

$writer = new Xlsx($spreadsheet);
$testFile = __DIR__ . '/storage/app/templates/Test_Import_With_Quotes.xlsx';

// Pastikan folder ada
if (!file_exists(dirname($testFile))) {
    mkdir(dirname($testFile), 0755, true);
}

$writer->save($testFile);

echo "✅ Sample Excel dibuat: Test_Import_With_Quotes.xlsx\n";
echo "📍 Location: storage/app/templates/\n";
echo "💡 File ini bisa digunakan untuk test import dengan NISN/NIK yang ada petiknya\n";
echo str_repeat("═", 60) . "\n";
