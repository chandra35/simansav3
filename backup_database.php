<?php
/**
 * Database Backup Script
 * Simple backup script to export database to SQL file
 */

$host = '127.0.0.1';
$username = 'root';
$password = '';
$database = 'simansav3';
$backupDir = __DIR__ . '/database/backups';
$backupFile = $backupDir . '/simansav3_backup_' . date('Ymd_His') . '.sql';

// Create backup directory if not exists
if (!is_dir($backupDir)) {
    mkdir($backupDir, 0755, true);
}

echo "Starting database backup...\n";
echo "Database: $database\n";
echo "Backup file: $backupFile\n\n";

try {
    // Connect to database
    $pdo = new PDO("mysql:host=$host;dbname=$database", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get all tables
    $tables = [];
    $result = $pdo->query('SHOW TABLES');
    while ($row = $result->fetch(PDO::FETCH_NUM)) {
        $tables[] = $row[0];
    }
    
    // Start backup
    $backup = "-- Database Backup: $database\n";
    $backup .= "-- Created: " . date('Y-m-d H:i:s') . "\n";
    $backup .= "-- Host: $host\n\n";
    $backup .= "SET FOREIGN_KEY_CHECKS=0;\n\n";
    
    foreach ($tables as $table) {
        echo "Backing up table: $table\n";
        
        // Drop table statement
        $backup .= "-- Table: $table\n";
        $backup .= "DROP TABLE IF EXISTS `$table`;\n\n";
        
        // Create table statement
        $createTable = $pdo->query("SHOW CREATE TABLE `$table`")->fetch(PDO::FETCH_ASSOC);
        $backup .= $createTable['Create Table'] . ";\n\n";
        
        // Insert data
        $rows = $pdo->query("SELECT * FROM `$table`");
        $rowCount = 0;
        
        while ($row = $rows->fetch(PDO::FETCH_ASSOC)) {
            if ($rowCount == 0) {
                $columns = array_keys($row);
                $backup .= "INSERT INTO `$table` (`" . implode('`, `', $columns) . "`) VALUES\n";
            }
            
            $values = array_map(function($value) use ($pdo) {
                if ($value === null) {
                    return 'NULL';
                }
                return $pdo->quote($value);
            }, array_values($row));
            
            $backup .= "(" . implode(', ', $values) . ")";
            $rowCount++;
            
            // Check if there are more rows
            if ($rows->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_NEXT)) {
                $backup .= ",\n";
                // Reset cursor to continue
                $rows = $pdo->query("SELECT * FROM `$table` LIMIT $rowCount, 1000");
                $rows->fetch(); // Skip already processed rows
            } else {
                $backup .= ";\n\n";
            }
        }
        
        // If table is empty
        if ($rowCount == 0) {
            $backup .= "-- No data in table $table\n\n";
        } else {
            echo "  - Backed up $rowCount rows\n";
        }
    }
    
    $backup .= "SET FOREIGN_KEY_CHECKS=1;\n";
    
    // Save to file
    file_put_contents($backupFile, $backup);
    
    $fileSize = filesize($backupFile);
    $fileSizeMB = round($fileSize / 1024 / 1024, 2);
    
    echo "\n✓ Backup completed successfully!\n";
    echo "File: $backupFile\n";
    echo "Size: $fileSizeMB MB\n";
    echo "Tables backed up: " . count($tables) . "\n";
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}
