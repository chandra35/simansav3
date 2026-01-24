<?php

echo "=== TEST KEMENDIKBUD API (NEW URL) ===" . PHP_EOL;
echo PHP_EOL;

// Test NPSN 1: SMP Negeri 1 Pekalongan
$npsn1 = '10805965';
echo "Testing NPSN: " . $npsn1 . PHP_EOL;
$url1 = "https://referensi.data.kemendikdasmen.go.id/pendidikan/npsn/" . $npsn1;
echo "URL: " . $url1 . PHP_EOL;
echo PHP_EOL;

$ch1 = curl_init();
curl_setopt($ch1, CURLOPT_URL, $url1);
curl_setopt($ch1, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch1, CURLOPT_TIMEOUT, 15);
curl_setopt($ch1, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch1, CURLOPT_SSL_VERIFYPEER, false); // For testing only

$html1 = curl_exec($ch1);
$httpCode1 = curl_getinfo($ch1, CURLINFO_HTTP_CODE);
$error1 = curl_error($ch1);

if ($error1) {
    echo "ERROR: " . $error1 . PHP_EOL;
} else {
    echo "HTTP Status: " . $httpCode1 . PHP_EOL;
    echo "Response Length: " . strlen($html1) . " bytes" . PHP_EOL;
    echo PHP_EOL;
    
    if ($httpCode1 == 200 && strlen($html1) > 0) {
        echo "--- Content Check ---" . PHP_EOL;
        echo "Contains NPSN '10805965': " . (strpos($html1, '10805965') !== false ? 'YES ✓' : 'NO ✗') . PHP_EOL;
        echo "Contains 'UPTD SMP': " . (stripos($html1, 'UPTD SMP') !== false ? 'YES ✓' : 'NO ✗') . PHP_EOL;
        echo "Contains 'PEKALONGAN': " . (stripos($html1, 'PEKALONGAN') !== false ? 'YES ✓' : 'NO ✗') . PHP_EOL;
        echo "Contains 'NEGERI': " . (stripos($html1, 'NEGERI') !== false ? 'YES ✓' : 'NO ✗') . PHP_EOL;
        echo "Contains 'Bentuk Pendidikan': " . (stripos($html1, 'Bentuk Pendidikan') !== false ? 'YES ✓' : 'NO ✗') . PHP_EOL;
        echo PHP_EOL;
        
        // Try to extract some data
        echo "--- Attempting HTML Parsing ---" . PHP_EOL;
        
        // Method 1: Find table rows
        preg_match_all('/<tr>(.*?)<\/tr>/is', $html1, $rows);
        if (count($rows[1]) > 0) {
            echo "Found " . count($rows[1]) . " table rows" . PHP_EOL;
            
            // Look for specific data
            foreach ($rows[1] as $row) {
                if (stripos($row, 'Nama') !== false && stripos($row, ':') !== false) {
                    // Remove HTML tags
                    $cleanRow = strip_tags($row);
                    $cleanRow = preg_replace('/\s+/', ' ', $cleanRow);
                    echo "Found Nama row: " . trim($cleanRow) . PHP_EOL;
                }
                
                if (stripos($row, 'Status Sekolah') !== false) {
                    $cleanRow = strip_tags($row);
                    $cleanRow = preg_replace('/\s+/', ' ', $cleanRow);
                    echo "Found Status row: " . trim($cleanRow) . PHP_EOL;
                }
                
                if (stripos($row, 'Bentuk Pendidikan') !== false) {
                    $cleanRow = strip_tags($row);
                    $cleanRow = preg_replace('/\s+/', ' ', $cleanRow);
                    echo "Found Bentuk row: " . trim($cleanRow) . PHP_EOL;
                }
            }
        }
        
        echo PHP_EOL;
        echo "--- First 800 characters of HTML ---" . PHP_EOL;
        echo substr($html1, 0, 800) . PHP_EOL;
        echo "..." . PHP_EOL;
    }
}

curl_close($ch1);

echo PHP_EOL;
echo "============================================" . PHP_EOL;
echo PHP_EOL;

// Test NPSN 2: MAN 1 Metro
$npsn2 = '10648374';
echo "Testing NPSN: " . $npsn2 . PHP_EOL;
$url2 = "https://referensi.data.kemendikdasmen.go.id/pendidikan/npsn/" . $npsn2;
echo "URL: " . $url2 . PHP_EOL;
echo PHP_EOL;

$ch2 = curl_init();
curl_setopt($ch2, CURLOPT_URL, $url2);
curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch2, CURLOPT_TIMEOUT, 15);
curl_setopt($ch2, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch2, CURLOPT_SSL_VERIFYPEER, false);

$html2 = curl_exec($ch2);
$httpCode2 = curl_getinfo($ch2, CURLINFO_HTTP_CODE);
$error2 = curl_error($ch2);

if ($error2) {
    echo "ERROR: " . $error2 . PHP_EOL;
} else {
    echo "HTTP Status: " . $httpCode2 . PHP_EOL;
    echo "Response Length: " . strlen($html2) . " bytes" . PHP_EOL;
    echo PHP_EOL;
    
    if ($httpCode2 == 200 && strlen($html2) > 0) {
        echo "--- Content Check ---" . PHP_EOL;
        echo "Contains NPSN '10648374': " . (strpos($html2, '10648374') !== false ? 'YES ✓' : 'NO ✗') . PHP_EOL;
        echo "Contains 'MAN': " . (strpos($html2, 'MAN') !== false ? 'YES ✓' : 'NO ✗') . PHP_EOL;
        echo "Contains 'METRO': " . (stripos($html2, 'METRO') !== false ? 'YES ✓' : 'NO ✗') . PHP_EOL;
        echo "Contains 'NEGERI': " . (stripos($html2, 'NEGERI') !== false ? 'YES ✓' : 'NO ✗') . PHP_EOL;
        echo PHP_EOL;
        
        // Try parsing
        preg_match_all('/<tr>(.*?)<\/tr>/is', $html2, $rows2);
        if (count($rows2[1]) > 0) {
            echo "Found " . count($rows2[1]) . " table rows" . PHP_EOL;
            
            foreach ($rows2[1] as $row) {
                if (stripos($row, 'Nama') !== false && stripos($row, ':') !== false) {
                    $cleanRow = strip_tags($row);
                    $cleanRow = preg_replace('/\s+/', ' ', $cleanRow);
                    echo "Found Nama row: " . trim($cleanRow) . PHP_EOL;
                }
            }
        }
    }
}

curl_close($ch2);

echo PHP_EOL;
echo "=== TEST COMPLETED ===" . PHP_EOL;
