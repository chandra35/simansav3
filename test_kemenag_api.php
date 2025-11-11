<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\KemenagNipService;

echo "===========================================\n";
echo "TEST KEMENAG NIP API SERVICE\n";
echo "===========================================\n\n";

$service = new KemenagNipService();

// Test dengan NIP dari contoh sebelumnya (harus 18 digit)
$testNips = [
    '198909092025211087',  // NIP 18 digit dari dummy data
    '199001012015011001',  // NIP test lainnya
];

foreach ($testNips as $nip) {
    echo "Testing NIP: $nip\n";
    echo "-------------------------------------------\n";
    
    // Validate format
    if (!$service->validateNipFormat($nip)) {
        echo "❌ Format NIP tidak valid!\n\n";
        continue;
    }
    
    echo "✓ Format NIP valid\n";
    
    // Call API
    echo "Calling API...\n";
    $result = $service->cekNip($nip);
    
    if ($result['success']) {
        echo "✅ SUCCESS!\n";
        echo "Message: {$result['message']}\n\n";
        
        if ($result['data']) {
            echo "Data yang ditemukan:\n";
            echo "- Nama: " . ($result['data']['NAMA'] ?? '-') . "\n";
            echo "- NIP: " . ($result['data']['NIP'] ?? '-') . "\n";
            echo "- Status: " . ($result['data']['STATUS_PEGAWAI'] ?? '-') . "\n";
            echo "- Jabatan: " . ($result['data']['TAMPIL_JABATAN'] ?? '-') . "\n";
            echo "- Unit Kerja: " . ($result['data']['SATKER_1'] ?? '-') . "\n";
            echo "- Provinsi: " . ($result['data']['PROVINSI'] ?? '-') . "\n";
        }
    } else {
        echo "❌ FAILED!\n";
        echo "Message: {$result['message']}\n";
    }
    
    echo "\n===========================================\n\n";
}

echo "Test selesai!\n";
echo "\nCek log di storage/logs/laravel.log untuk detail\n";
