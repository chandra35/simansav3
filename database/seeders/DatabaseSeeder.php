<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info('🌱 Memulai seeding database...');
        $this->command->newLine();

        // 1. Super Admin
        $this->command->info('👤 Seeding Super Admin...');
        $this->call(SuperAdminSeeder::class);
        $this->command->newLine();

        // 2. Roles & Permissions (RBAC)
        $this->command->info('🔐 Seeding Roles & Permissions (RBAC)...');
        $this->call(RolePermissionSeeder::class);
        $this->command->newLine();

        // 3. Data Wilayah Indonesia (dari package Laravolt)
        $this->command->info('🗺️  Seeding Data Wilayah Indonesia...');
        $this->command->warn('   Proses ini membutuhkan waktu beberapa menit...');
        Artisan::call('laravolt:indonesia:seed');
        $this->command->info('   ✅ Data wilayah berhasil di-seed!');
        $this->command->newLine();

        // 4. Kurikulum & Jurusan
        $this->command->info('📚 Seeding Kurikulum & Jurusan...');
        $this->call(KurikulumSeeder::class);
        $this->command->newLine();

        // 5. Tahun Pelajaran
        $this->command->info('📅 Seeding Tahun Pelajaran...');
        $this->call(TahunPelajaranSeeder::class);
        $this->command->newLine();

        $this->command->info('🎉 Seeding database selesai!');
        $this->command->newLine();
        $this->command->info('🔐 Login Credentials:');
        $this->command->info('   Username: superadmin');
        $this->command->info('   Password: password');
    }
}
