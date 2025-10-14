<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        // Create or find super admin user
        $user = User::firstOrCreate(
            ['username' => 'superadmin'],
            [
                'name' => 'Super Administrator',
                'email' => 'superadmin@simansa.man1metro.sch.id',
                'password' => Hash::make('password'),
                'role' => 'super_admin',
                'is_first_login' => false,
                'is_active' => true,
            ]
        );
        
        // Assign Super Admin role from Spatie
        $role = Role::where('name', 'Super Admin')->first();
        
        if ($role) {
            $user->assignRole($role);
            $this->command->info('✓ Super Admin role assigned to: ' . $user->name);
        } else {
            $this->command->error('✗ Super Admin role not found. Please run PermissionSeeder first.');
        }
    }
}
