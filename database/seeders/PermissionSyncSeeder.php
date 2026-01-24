<?php

namespace Database\Seeders;

use App\Services\PermissionSyncService;
use Illuminate\Database\Seeder;

class PermissionSyncSeeder extends Seeder
{
    /**
     * Sync all permissions from module definitions.
     * 
     * Run: php artisan db:seed --class=PermissionSyncSeeder
     */
    public function run(): void
    {
        $service = new PermissionSyncService();
        $result = $service->syncPermissionsFromModules();
        
        $this->command->info("Permissions synced: {$result['created']} created, {$result['existing']} existing");
        
        // Give all permissions to Super Admin role
        $superAdmin = \Spatie\Permission\Models\Role::where('name', 'Super Admin')->first();
        if ($superAdmin) {
            $permissions = \Spatie\Permission\Models\Permission::all();
            $superAdmin->syncPermissions($permissions);
            $this->command->info("All permissions assigned to Super Admin role");
        }
    }
}
