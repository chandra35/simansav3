<?php
// Quick diagnostic: test cipher endpoint concurrency + check cache state
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Load Laravel environment
require '/www/wwwroot/simansa.man1metro.sch.id/vendor/autoload.php';
$app = require '/www/wwwroot/simansa.man1metro.sch.id/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// 1. Cache status
$testKey = 'rdm_dec_' . md5('test');
$cached = \Illuminate\Support\Facades\Cache::get($testKey);
echo "Cache driver: " . config('cache.default') . "\n";
echo "Cache test key exists: " . ($cached ? 'YES' : 'NO') . "\n";

// Count cached decrypt keys (file cache only)
$cacheDriver = config('cache.default');
if ($cacheDriver === 'file') {
    $cacheDir = storage_path('framework/cache/data');
    $files = glob($cacheDir . '/**/**', GLOB_NOSORT);
    $rdmFiles = array_filter($files ?? [], fn($f) => is_file($f));
    echo "Total cache files: " . count($rdmFiles) . "\n";
}

// 2. Test 4 concurrent requests to cipher endpoint
echo "\n=== Cipher endpoint concurrency test (4 parallel) ===\n";
$url = rtrim(env('RDM_CIPHER_URL', 'https://rapor.man1metro.sch.id/periksasiswa/dec.php'), '/');
$token = env('RDM_CIPHER_TOKEN', 'mascan_code');
$endpoint = $url . '?token=' . $token;
$testData = json_encode(array_fill(0, 10, 'test_enc_value'));

$start = microtime(true);
$mh = curl_multi_init();
$handles = [];
for ($i = 0; $i < 4; $i++) {
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $endpoint,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $testData,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_TIMEOUT => 15,
    ]);
    curl_multi_add_handle($mh, $ch);
    $handles[] = $ch;
}
do {
    $status = curl_multi_exec($mh, $running);
    if ($running) curl_multi_select($mh, 1.0);
} while ($running > 0 && $status === CURLM_OK);

foreach ($handles as $i => $ch) {
    $resp = curl_multi_getcontent($ch);
    $err  = curl_error($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    echo "Handle $i: HTTP $code " . ($err ? "ERROR=$err" : "len=" . strlen($resp)) . "\n";
    curl_multi_remove_handle($mh, $ch);
    curl_close($ch);
}
curl_multi_close($mh);
echo "Total time: " . round(microtime(true) - $start, 2) . "s\n";

// 3. Check RDM student count for kelas XII (tahun 2025)
echo "\n=== RDM Kelas XII count ===\n";
try {
    $count = \Illuminate\Support\Facades\DB::connection('mysql_rdm')
        ->table('e_siswa as s')
        ->join('e_kelas as k', 'k.kelas_id', '=', 's.kelas_id')
        ->where('k.tahunajaran_id', 2025)
        ->where('s.tingkat_id', '14')
        ->count();
    echo "Kelas XII students in RDM: $count\n";
    
    // Check how many have NIS that match SIMANSA username
    $nisList = \Illuminate\Support\Facades\DB::connection('mysql_rdm')
        ->table('e_siswa as s')
        ->join('e_kelas as k', 'k.kelas_id', '=', 's.kelas_id')
        ->where('k.tahunajaran_id', 2025)
        ->where('s.tingkat_id', '14')
        ->pluck('s.siswa_nis')
        ->filter()
        ->values()
        ->toArray();
    
    $simansaUsers = \Illuminate\Support\Facades\DB::table('users')
        ->whereIn('username', $nisList)
        ->count();
    
    echo "RDM NIS values found in SIMANSA username: $simansaUsers / " . count($nisList) . "\n";
} catch (\Exception $e) {
    echo "DB Error: " . $e->getMessage() . "\n";
}
