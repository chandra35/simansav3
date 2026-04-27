<?php
// Test script — jalankan di VM: php test_emis.php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

// 1. Ambil token dari DB
$tokenRow = DB::table('api_tokens')->where('name', 'emis_api_token')->first();
if (!$tokenRow) {
    echo "ERROR: token emis_api_token tidak ada di DB\n";
    exit(1);
}

echo "Token prefix : " . substr($tokenRow->token, 0, 60) . "...\n";
echo "Expires at   : " . $tokenRow->expires_at . "\n\n";

// 2. Test hit Kemdikbud endpoint
$nisn = '0073888908';
echo "Test NISN: $nisn\n";
echo "---\n";

echo "1. Kemdikbud endpoint...\n";
try {
    $res = Http::timeout(10)
        ->withHeaders(['Authorization' => 'Bearer ' . $tokenRow->token, 'Accept' => 'application/json'])
        ->withOptions(['verify' => false])
        ->get("https://api-emis.kemenag.go.id/v1/students/pusdatin/{$nisn}/0");

    echo "   Status: " . $res->status() . "\n";
    echo "   Body  : " . substr($res->body(), 0, 300) . "\n\n";
} catch (\Exception $e) {
    echo "   ERROR: " . $e->getMessage() . "\n\n";
}

echo "2. Kemenag PPDB endpoint...\n";
try {
    $res = Http::timeout(10)
        ->withHeaders(['Authorization' => 'Bearer ' . $tokenRow->token, 'Accept' => 'application/json'])
        ->withOptions(['verify' => false])
        ->get("https://api-emis.kemenag.go.id/v1/students/student-ppdb-search?fnisn={$nisn}");

    echo "   Status: " . $res->status() . "\n";
    echo "   Body  : " . substr($res->body(), 0, 300) . "\n\n";
} catch (\Exception $e) {
    echo "   ERROR: " . $e->getMessage() . "\n\n";
}
