<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class SampleUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Creating sample users...');

        // Admin
        $admin = User::firstOrCreate(
            ['username' => 'admin'],
            [
                'name' => 'Admin Sekolah',
                'email' => 'admin@simansa.sch.id',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'is_first_login' => false,
                'is_active' => true,
            ]
        );
        $admin->assignRole('Admin');

        // Operator
        $operator = User::firstOrCreate(
            ['username' => 'operator'],
            [
                'name' => 'Operator TU',
                'email' => 'operator@simansa.sch.id',
                'password' => Hash::make('password'),
                'role' => 'operator',
                'is_first_login' => false,
                'is_active' => true,
            ]
        );
        $operator->assignRole('Operator');

        // GTK - Candra (View Only)
        $candra = User::firstOrCreate(
            ['username' => 'candra'],
            [
                'name' => 'Candra - Guru Matematika',
                'email' => 'candra@simansa.sch.id',
                'password' => Hash::make('password'),
                'role' => 'gtk',
                'phone' => '08123456701',
                'is_first_login' => false,
                'is_active' => true,
            ]
        );
        $candra->assignRole('GTK');

        // GTK - Nanang (Full CRUD)
        $nanang = User::firstOrCreate(
            ['username' => 'nanang'],
            [
                'name' => 'Nanang - Guru Bahasa Indonesia',
                'email' => 'nanang@simansa.sch.id',
                'password' => Hash::make('password'),
                'role' => 'gtk',
                'phone' => '08123456702',
                'is_first_login' => false,
                'is_active' => true,
            ]
        );
        $nanang->syncRoles(['GTK', 'Operator']); // Multi-role

        // Waka Kurikulum - Suhardi
        $suhardi = User::firstOrCreate(
            ['username' => 'suhardi'],
            [
                'name' => 'Suhardi - Waka Kurikulum',
                'email' => 'suhardi@simansa.sch.id',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'phone' => '08123456703',
                'is_first_login' => false,
                'is_active' => true,
            ]
        );
        $suhardi->assignRole('Admin');

        // Sample Siswa
        $siswa = User::firstOrCreate(
            ['username' => 'siswa001'],
            [
                'name' => 'Ahmad Rizki',
                'email' => 'ahmad.rizki@student.simansa.sch.id',
                'password' => Hash::make('password'),
                'role' => 'siswa',
                'is_first_login' => true,
                'is_active' => true,
            ]
        );
        $siswa->assignRole('Siswa');

        $this->command->info('✓ Sample users created successfully!');
        $this->command->newLine();
        $this->command->table(
            ['Username', 'Name', 'Role(s)', 'Password'],
            [
                ['superadmin', 'Super Administrator', 'Super Admin', 'password'],
                ['admin', 'Admin Sekolah', 'Admin', 'password'],
                ['operator', 'Operator TU', 'Operator', 'password'],
                ['candra', 'Candra - Guru Matematika', 'GTK', 'password'],
                ['nanang', 'Nanang - Guru B. Indonesia', 'GTK + Operator', 'password'],
                ['suhardi', 'Suhardi - Waka Kurikulum', 'Admin', 'password'],
                ['siswa001', 'Ahmad Rizki', 'Siswa', 'password'],
            ]
        );
    }
}
