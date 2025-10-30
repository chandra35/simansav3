<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\CustomMenu;
use App\Services\CustomMenuImportService;

$menu = CustomMenu::first();

if (!$menu) {
    die("No menu found\n");
}

echo "Menu: {$menu->judul}\n";
echo "Content Type: {$menu->content_type}\n";
echo "Custom Fields (raw): " . json_encode($menu->custom_fields) . "\n";
echo "Custom Fields Array: " . json_encode($menu->getCustomFieldsArray()) . "\n\n";

try {
    $service = new CustomMenuImportService($menu);
    echo "Attempting to generate template...\n";
    $response = $service->downloadTemplate();
    echo "Template generated successfully!\n";
    echo "Response type: " . get_class($response) . "\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n";
}
