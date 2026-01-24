<?php
// Script untuk verifikasi urutan header template Excel
require __DIR__ . '/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$file = __DIR__ . '/storage/app/templates/Template_Import_Siswa.xlsx';

if (!file_exists($file)) {
    die("❌ File tidak ditemukan: $file\n");
}

echo "📄 Membaca file: $file\n\n";

$spreadsheet = IOFactory::load($file);
$sheet = $spreadsheet->getActiveSheet();

// Baca header (baris 1)
echo "📋 HEADER (Baris 1):\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
for ($col = 'A'; $col <= 'D'; $col++) {
    $value = $sheet->getCell($col . '1')->getValue();
    echo "   Kolom $col: $value\n";
}

echo "\n✅ URUTAN HEADER:\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
$headers = [];
for ($col = 'A'; $col <= 'D'; $col++) {
    $headers[] = $sheet->getCell($col . '1')->getValue();
}
echo "   " . implode(' → ', $headers) . "\n";

// Baca sample data (baris 2)
echo "\n📊 SAMPLE DATA (Baris 2):\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
for ($col = 'A'; $col <= 'D'; $col++) {
    $value = $sheet->getCell($col . '2')->getValue();
    $header = $sheet->getCell($col . '1')->getValue();
    echo "   $header: $value\n";
}

// Verifikasi urutan
echo "\n🔍 VERIFIKASI:\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
$expected = ['Nama Lengkap', 'NISN', 'NIK', 'Jenis Kelamin'];
$match = true;

for ($i = 0; $i < count($expected); $i++) {
    $actual = $headers[$i];
    $exp = $expected[$i];
    $status = ($actual === $exp) ? '✅' : '❌';
    echo "   $status Kolom " . chr(65 + $i) . ": ";
    if ($actual === $exp) {
        echo "OK ($actual)\n";
    } else {
        echo "ERROR (Expected: '$exp', Got: '$actual')\n";
        $match = false;
    }
}

echo "\n" . str_repeat("━", 40) . "\n";
if ($match) {
    echo "✅ SUKSES! Urutan header sudah sesuai EMIS\n";
    echo "   Nama Lengkap → NISN → NIK → Jenis Kelamin\n";
} else {
    echo "❌ ERROR! Urutan header belum sesuai\n";
}
echo str_repeat("━", 40) . "\n";
