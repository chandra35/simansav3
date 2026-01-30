<?php
/**
 * Debug script untuk melihat proses upload nilai
 * Jalankan: php debug_upload_nilai.php path/to/excel.xlsx
 */

require __DIR__ . '/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

if ($argc < 2) {
    echo "Usage: php debug_upload_nilai.php <excel_file>\n";
    exit(1);
}

$file = $argv[1];
if (!file_exists($file)) {
    echo "File tidak ditemukan: $file\n";
    exit(1);
}

$spreadsheet = IOFactory::load($file);
$worksheet = $spreadsheet->getActiveSheet();
$rows = $worksheet->toArray();

echo "=== DEBUG UPLOAD NILAI ===\n\n";
echo "Total rows: " . count($rows) . "\n\n";

// Cari baris header (baris yang mengandung "Nisn")
$headerRowIndex = null;
for ($i = 0; $i < min(10, count($rows)); $i++) {
    $row = array_map('strtoupper', array_map('trim', array_map('strval', $rows[$i])));
    echo "Row $i: " . implode(' | ', array_slice($row, 0, 10)) . "\n";
    if (in_array('NISN', $row)) {
        $headerRowIndex = $i;
        echo "  >> HEADER FOUND at row $i\n";
    }
}

if ($headerRowIndex === null) {
    echo "\nERROR: Kolom NISN tidak ditemukan!\n";
    exit(1);
}

echo "\n=== HEADER ANALYSIS ===\n";
$header1 = array_map('strtoupper', array_map('trim', array_map('strval', $rows[$headerRowIndex])));
$header2 = [];
if (isset($rows[$headerRowIndex + 1])) {
    $header2 = array_map('strtoupper', array_map('trim', array_map('strval', $rows[$headerRowIndex + 1])));
}

echo "\nHeader Row 1 (index $headerRowIndex):\n";
foreach ($header1 as $idx => $val) {
    if (!empty($val)) {
        echo "  [$idx] = '$val'\n";
    }
}

echo "\nHeader Row 2 (index " . ($headerRowIndex + 1) . "):\n";
foreach ($header2 as $idx => $val) {
    if (!empty($val)) {
        echo "  [$idx] = '$val'\n";
    }
}

// Mapping logic
$skipColumns = ['NO', 'NIS', 'NISN', 'NAMA', 'JK', 'JUMLAH', 'PAI', 'KMPM', 'KMPS', ''];
$mapelMapping = [];

for ($index = 0; $index < max(count($header1), count($header2)); $index++) {
    $kode2 = isset($header2[$index]) ? strtoupper(trim($header2[$index])) : '';
    $kode1 = isset($header1[$index]) ? strtoupper(trim($header1[$index])) : '';
    
    $kode = !empty($kode2) ? $kode2 : $kode1;
    
    if ($kode1 === 'MULOK' && $kode2 === 'PRKW') {
        $kode = 'MULOK PRKW';
    }
    
    if (in_array($kode, $skipColumns)) continue;
    
    $mapelMapping[$index] = $kode;
}

echo "\n=== MAPEL MAPPING ===\n";
foreach ($mapelMapping as $idx => $kode) {
    echo "  Column [$idx] => '$kode'\n";
}

// NISN Index
$nisnIndex = array_search('NISN', $header1);
echo "\nNISN column index: $nisnIndex\n";

// Data start row
$dataStartRow = $headerRowIndex + 1;
if (isset($rows[$headerRowIndex + 1])) {
    $potentialSubHeader = array_map('strtoupper', array_map('trim', array_map('strval', $rows[$headerRowIndex + 1])));
    $subHeaderMapels = ['QH', 'AA', 'FIK', 'SKI', 'BIO', 'KIM', 'FIS', 'EKO', 'PRKW'];
    foreach ($potentialSubHeader as $val) {
        if (in_array($val, $subHeaderMapels)) {
            $dataStartRow = $headerRowIndex + 2;
            break;
        }
    }
}

echo "Data starts at row: $dataStartRow\n\n";

echo "=== FIRST 5 DATA ROWS ===\n";
for ($i = $dataStartRow; $i < min($dataStartRow + 5, count($rows)); $i++) {
    $row = $rows[$i];
    $nisn = trim(strval($row[$nisnIndex] ?? ''));
    echo "\nRow $i - NISN: '$nisn'\n";
    
    foreach ($mapelMapping as $colIdx => $kode) {
        $nilai = $row[$colIdx] ?? null;
        echo "  $kode (col $colIdx) = '$nilai'\n";
    }
}
