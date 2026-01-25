<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\CustomMenu;
use App\Services\CustomMenuImportService;

$menu = CustomMenu::first();
if (!$menu) {
    echo "No menu found!\n";
    exit(1);
}

echo "Menu: " . $menu->judul . "\n";
echo "Content type: " . $menu->content_type . "\n";

try {
    $service = new CustomMenuImportService($menu);
    echo "Service created\n";
    
    // Test downloadTemplate method
    $response = $service->downloadTemplate();
    echo "Response type: " . get_class($response) . "\n";
    echo "Download OK!\n";
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "\nTrace:\n" . $e->getTraceAsString() . "\n";
}
