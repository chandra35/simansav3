<?php

require __DIR__ . '/vendor/autoload.php';

$file = __DIR__ . '/LEGGER NILAI KELAS XII.2.xlsx';

if (!file_exists($file)) {
    echo "File tidak ditemukan: $file\n";
    exit;
}

$spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file);
$worksheet = $spreadsheet->getActiveSheet();
$rows = $worksheet->toArray();

echo "=== Debug Header Excel RDM ===\n\n";

for ($i = 0; $i < min(10, count($rows)); $i++) {
    $rowData = array_map('trim', array_map('strval', $rows[$i]));
    echo "Baris " . ($i+1) . ": ";
    
    // Show first 26 columns (A-Z)
    $cols = [];
    for ($j = 0; $j < min(26, count($rowData)); $j++) {
        $val = $rowData[$j];
        if (!empty($val)) {
            $cols[] = chr(65 + $j) . "=" . $val;
        }
    }
    echo implode(' | ', $cols) . "\n";
}

echo "\n=== Cari baris dengan NISN ===\n";
$headerRowIndex = null;
for ($i = 0; $i < min(10, count($rows)); $i++) {
    $row = array_map('strtoupper', array_map('trim', array_map('strval', $rows[$i])));
    if (in_array('NISN', $row)) {
        $headerRowIndex = $i;
        echo "NISN ditemukan di baris " . ($i+1) . " (index $i)\n";
        break;
    }
}

if ($headerRowIndex !== null) {
    echo "\n=== Header kolom (baris " . ($headerRowIndex+1) . ") ===\n";
    $header = array_map('strtoupper', array_map('trim', array_map('strval', $rows[$headerRowIndex])));
    foreach ($header as $idx => $val) {
        if (!empty($val)) {
            echo "  [$idx] " . chr(65 + $idx) . " = $val\n";
        }
    }
}
