<?php
// Test script — jalankan di VM: php test_emis.php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

// 1. Ambil token dari DB
$tokenRow = DB::table('api_tokens')->where('name', 'emis_api_token')->first();
$institusiRow = DB::table('api_tokens')->where('name', 'emis_institusi_token')->first();

echo "=== emis_api_token ===\n";
echo "Token prefix : " . substr($tokenRow->token ?? 'NULL', 0, 60) . "...\n";
echo "Expires at   : " . ($tokenRow->expires_at ?? 'NULL') . "\n\n";

echo "=== emis_institusi_token ===\n";
echo "Token prefix : " . substr($institusiRow->token ?? 'NULL', 0, 60) . "...\n";
echo "Expires at   : " . ($institusiRow->expires_at ?? 'NULL') . "\n\n";

// Test pakai token yang lebih baru
$activeToken = $tokenRow;
if ($institusiRow && $institusiRow->expires_at > ($tokenRow->expires_at ?? '')) {
    echo ">>> Pakai emis_institusi_token (lebih baru)\n\n";
    $activeToken = $institusiRow;
}

// 2. Test hit Kemdikbud endpoint
$nisn = '0073888908';
echo "Test NISN: $nisn\n";
echo "---\n";

echo "1. Kemdikbud endpoint...\n";
try {
    $res = Http::timeout(10)
        ->withHeaders(['Authorization' => 'Bearer ' . $activeToken->token, 'Accept' => 'application/json'])
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
        ->withHeaders(['Authorization' => 'Bearer ' . $activeToken->token, 'Accept' => 'application/json'])
        ->withOptions(['verify' => false])
        ->get("https://api-emis.kemenag.go.id/v1/students/student-ppdb-search?fnisn={$nisn}");

    echo "   Status: " . $res->status() . "\n";
    echo "   Body  : " . substr($res->body(), 0, 300) . "\n\n";
} catch (\Exception $e) {
    echo "   ERROR: " . $e->getMessage() . "\n\n";
}
