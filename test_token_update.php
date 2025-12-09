<?php
/**
 * Test Script untuk Update API Token
 * Jalankan: php test_token_update.php
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== TEST UPDATE API TOKEN ===\n\n";

// Test 1: Cek table exists
echo "1. Cek table api_tokens...\n";
$tables = DB::select("SHOW TABLES LIKE 'api_tokens'");
if (!empty($tables)) {
    echo "   ✓ Table api_tokens EXISTS\n\n";
} else {
    echo "   ✗ Table api_tokens NOT FOUND\n\n";
    exit(1);
}

// Test 2: Cek struktur table
echo "2. Struktur table api_tokens:\n";
$columns = DB::select("DESCRIBE api_tokens");
foreach ($columns as $col) {
    echo "   - {$col->Field} ({$col->Type})\n";
}
echo "\n";

// Test 3: Insert test token
echo "3. Test insert token...\n";
try {
    $tokenType = 'test_token_' . time();
    $result = DB::table('api_tokens')->updateOrInsert(
        ['name' => $tokenType],
        [
            'token' => 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.test123456789',
            'description' => 'Test Token',
            'expires_at' => now()->addHours(4),
            'created_at' => DB::raw('COALESCE(created_at, NOW())'),
            'updated_at' => now()
        ]
    );
    
    if ($result) {
        echo "   ✓ Insert SUCCESS\n";
        
        // Verify
        $token = DB::table('api_tokens')->where('name', $tokenType)->first();
        echo "   Token ID: {$token->id}\n";
        echo "   Created: {$token->created_at}\n";
        echo "   Updated: {$token->updated_at}\n";
        
        // Cleanup
        DB::table('api_tokens')->where('name', $tokenType)->delete();
        echo "   ✓ Test token cleaned up\n";
    } else {
        echo "   ✗ Insert FAILED\n";
    }
} catch (\Exception $e) {
    echo "   ✗ ERROR: " . $e->getMessage() . "\n";
}

echo "\n4. Current tokens in database:\n";
$tokens = DB::table('api_tokens')->get();
foreach ($tokens as $token) {
    echo "   - {$token->name} (Updated: {$token->updated_at})\n";
}

echo "\n=== TEST SELESAI ===\n";
