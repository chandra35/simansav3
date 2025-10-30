<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\CustomMenu;
use App\Models\User;

echo "Testing delete menu functionality...\n\n";

// Get first user for created_by
$user = User::first();
if (!$user) {
    die("No user found in database\n");
}

// Create a test menu
$menu = CustomMenu::create([
    'judul' => 'Test Menu Delete ' . time(),
    'slug' => 'test-menu-delete-' . time(),
    'icon' => 'fas fa-test',
    'menu_group' => 'akademik',
    'content_type' => 'general',
    'konten' => 'Test content',
    'custom_fields' => null,
    'urutan' => 99,
    'is_active' => true,
    'created_by' => $user->id,
]);

echo "✅ Created test menu: {$menu->judul} (ID: {$menu->id})\n";

// Try to delete
try {
    $menuId = $menu->id;
    $judul = $menu->judul;
    
    $menu->delete();
    
    echo "✅ Menu deleted successfully!\n";
    
    // Verify deletion
    $check = CustomMenu::find($menuId);
    if ($check === null) {
        echo "✅ Verified: Menu '{$judul}' no longer exists in database\n";
    } else {
        echo "❌ Error: Menu still exists in database\n";
    }
} catch (\Exception $e) {
    echo "❌ Error deleting menu: " . $e->getMessage() . "\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n";
}
