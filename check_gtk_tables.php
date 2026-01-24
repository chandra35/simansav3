<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Checking GTK Tables in SIMANSA Database ===\n\n";

try {
    // Check tables that start with 'gtk'
    $tables = DB::select("SHOW TABLES LIKE 'gtk%'");
    
    if (empty($tables)) {
        echo "❌ No tables found starting with 'gtk'\n";
    } else {
        echo "✅ Found tables:\n";
        foreach ($tables as $table) {
            $tableName = array_values((array)$table)[0];
            echo "   - {$tableName}\n";
        }
    }
    
    echo "\n--- Checking 'gtk' table (singular) ---\n";
    $gtkExists = DB::select("SHOW TABLES LIKE 'gtk'");
    if (!empty($gtkExists)) {
        echo "✅ Table 'gtk' EXISTS\n";
        
        // Get structure
        $structure = DB::select("DESCRIBE gtk");
        echo "\nStructure of 'gtk' table:\n";
        foreach ($structure as $column) {
            echo "   - {$column->Field} ({$column->Type})\n";
        }
        
        // Count records
        $count = DB::table('gtk')->count();
        echo "\nTotal records: {$count}\n";
        
        // Sample data
        if ($count > 0) {
            $sample = DB::table('gtk')->limit(3)->get();
            echo "\nSample data (first 3):\n";
            foreach ($sample as $row) {
                $nama = $row->nama ?? $row->nama_lengkap ?? 'N/A';
                $nip = $row->nip ?? 'N/A';
                echo "   ID: {$row->id}, Nama: {$nama}, NIP: {$nip}\n";
            }
        }
    } else {
        echo "❌ Table 'gtk' DOES NOT EXIST\n";
    }
    
    echo "\n--- Checking 'gtks' table (plural) ---\n";
    $gtksExists = DB::select("SHOW TABLES LIKE 'gtks'");
    if (!empty($gtksExists)) {
        echo "✅ Table 'gtks' EXISTS\n";
        
        // Get structure
        $structure = DB::select("DESCRIBE gtks");
        echo "\nStructure of 'gtks' table:\n";
        foreach ($structure as $column) {
            echo "   - {$column->Field} ({$column->Type})\n";
        }
        
        // Count records
        $count = DB::table('gtks')->count();
        echo "\nTotal records: {$count}\n";
        
        // Sample data
        if ($count > 0) {
            $sample = DB::table('gtks')->limit(3)->get();
            echo "\nSample data (first 3):\n";
            foreach ($sample as $row) {
                $nama = $row->nama_lengkap ?? $row->nama ?? 'N/A';
                $nip = $row->nip ?? 'N/A';
                echo "   ID: {$row->id}, Nama: {$nama}, NIP: {$nip}\n";
            }
        }
    } else {
        echo "❌ Table 'gtks' DOES NOT EXIST\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n=== Check Complete ===\n";
