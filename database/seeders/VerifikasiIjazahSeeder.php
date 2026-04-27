<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

/**
 * Seeder untuk fitur Verifikasi Ijazah SMP/MTs
 * Jalankan dengan: php artisan db:seed --class=VerifikasiIjazahSeeder
 */
class VerifikasiIjazahSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Buat permission
        $permission = Permission::firstOrCreate(['name' => 'verifikasi-ijazah', 'guard_name' => 'web']);
        $this->command->info('✅ Permission "verifikasi-ijazah" created/exists');

        // Buat role baru
        $role = Role::firstOrCreate(['name' => 'Verifikator Ijazah', 'guard_name' => 'web']);
        $role->givePermissionTo([
            'view-dashboard',
            'view-siswa',
            'view-dokumen-siswa',
            'verifikasi-ijazah',
            'view-profile',
            'edit-profile',
            // GTK personal access
            'view-gtk-dashboard',
            'edit-gtk-profile',
            'change-password-gtk',
        ]);
        $this->command->info('✅ Role "Verifikator Ijazah" created/updated');

        // Pastikan Super Admin juga punya permission ini
        $superAdmin = Role::where('name', 'Super Admin')->first();
        if ($superAdmin && !$superAdmin->hasPermissionTo('verifikasi-ijazah')) {
            $superAdmin->givePermissionTo('verifikasi-ijazah');
            $this->command->info('✅ Super Admin diberi permission "verifikasi-ijazah"');
        }

        // Pastikan Admin juga punya (bisa monitor dashboard verifikasi)
        $admin = Role::where('name', 'Admin')->first();
        if ($admin && !$admin->hasPermissionTo('verifikasi-ijazah')) {
            $admin->givePermissionTo('verifikasi-ijazah');
            $this->command->info('✅ Role Admin diberi permission "verifikasi-ijazah"');
        }

        $this->command->info('');
        $this->command->info('=== Selesai ===');
        $this->command->info('Untuk memberi role Verifikator Ijazah ke akun GTK:');
        $this->command->info('  Admin → User & Role → Data User → Edit User → Assign Role');
    }
}
