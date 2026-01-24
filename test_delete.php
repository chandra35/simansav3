<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\CustomMenu;

echo "Testing delete functionality...\n\n";

// Create a test menu
$menu = CustomMenu::create([
    'judul' => 'Test Menu Delete',
    'slug' => 'test-menu-delete-' . time(),
    'icon' => 'fas fa-test',
    'menu_group' => 'akademik',
    'content_type' => 'general',
    'konten' => 'Test content',
    'custom_fields' => null,
    'urutan' => 99,
    'is_active' => true,
    'created_by' => '00000000-0000-0000-0000-000000000000', // dummy
]);

echo "Created menu: {$menu->judul} (ID: {$menu->id})\n";

// Try to delete
try {
    $menu->delete();
    echo "✅ Menu deleted successfully!\n";
    
    // Verify deletion
    $check = CustomMenu::find($menu->id);
    if ($check === null) {
        echo "✅ Verified: Menu no longer exists in database\n";
    } else {
        echo "❌ Error: Menu still exists in database\n";
    }
} catch (\Exception $e) {
    echo "❌ Error deleting menu: " . $e->getMessage() . "\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n";
}
