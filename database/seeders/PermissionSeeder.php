<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Definisi modul-modul yang ada di sistem
        $modules = [
            'user' => 'User Management',
            'role' => 'Role Management',
            'permission' => 'Permission Management',
            'siswa' => 'Data Siswa',
            'gtk' => 'Data GTK',
            'kelas' => 'Data Kelas',
            'kurikulum' => 'Data Kurikulum',
            'tahun-pelajaran' => 'Data Tahun Pelajaran',
            'mata-pelajaran' => 'Data Mata Pelajaran',
            'nilai' => 'Data Nilai',
            'absensi' => 'Data Absensi',
            'dashboard' => 'Dashboard',
            'laporan' => 'Laporan',
        ];

        // Definisi actions untuk setiap modul
        $actions = ['view', 'create', 'edit', 'delete'];

        DB::beginTransaction();
        try {
            // Create permissions untuk setiap modul
            foreach ($modules as $moduleKey => $moduleName) {
                foreach ($actions as $action) {
                    Permission::firstOrCreate(
                        ['name' => "{$action}-{$moduleKey}"],
                        ['guard_name' => 'web']
                    );
                }
            }

            // Create special permissions
            $specialPermissions = [
                'assign-role' => 'Assign Role ke User',
                'assign-permission' => 'Assign Permission ke User/Role',
                'view-activity-log' => 'Lihat Activity Log',
                'export-data' => 'Export Data',
                'import-data' => 'Import Data',
                'manage-settings' => 'Kelola Pengaturan Sistem',
                'view-all-data' => 'Lihat Semua Data (Bypass Ownership)',
            ];

            foreach ($specialPermissions as $permKey => $permName) {
                Permission::firstOrCreate(
                    ['name' => $permKey],
                    ['guard_name' => 'web']
                );
            }

            // Create Roles
            $superAdmin = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
            $admin = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
            $operator = Role::firstOrCreate(['name' => 'Operator', 'guard_name' => 'web']);
            $gtk = Role::firstOrCreate(['name' => 'GTK', 'guard_name' => 'web']);
            $siswa = Role::firstOrCreate(['name' => 'Siswa', 'guard_name' => 'web']);

            // Assign ALL permissions to Super Admin
            $superAdmin->syncPermissions(Permission::all());

            // Admin - semua kecuali manage user/role/permission
            $adminPermissions = Permission::where('name', 'not like', '%-user')
                ->where('name', 'not like', '%-role')
                ->where('name', 'not like', '%-permission')
                ->where('name', '!=', 'assign-role')
                ->where('name', '!=', 'assign-permission')
                ->pluck('name')
                ->toArray();
            $admin->syncPermissions($adminPermissions);

            // Operator - CRUD kecuali user management
            $operatorPermissions = Permission::whereIn('name', [
                'view-siswa', 'create-siswa', 'edit-siswa', 'delete-siswa',
                'view-gtk', 'create-gtk', 'edit-gtk', 'delete-gtk',
                'view-kelas', 'create-kelas', 'edit-kelas', 'delete-kelas',
                'view-kurikulum', 'create-kurikulum', 'edit-kurikulum', 'delete-kurikulum',
                'view-tahun-pelajaran', 'create-tahun-pelajaran', 'edit-tahun-pelajaran', 'delete-tahun-pelajaran',
                'view-mata-pelajaran', 'create-mata-pelajaran', 'edit-mata-pelajaran', 'delete-mata-pelajaran',
                'view-dashboard',
                'import-data',
                'export-data',
            ])->pluck('name')->toArray();
            $operator->syncPermissions($operatorPermissions);

            // GTK - base role with minimal permissions (replaced old Guru role)
            // Additional permissions can be assigned via Tugas Tambahan feature
            $gtkPermissions = Permission::whereIn('name', [
                'view-siswa',
                'view-kelas',
                'view-mata-pelajaran',
                'view-gtk-dashboard',
                'edit-gtk-profile',
                'change-password-gtk',
            ])->pluck('name')->toArray();
            $gtk->syncPermissions($gtkPermissions);

            // Siswa - view only (data sendiri)
            $siswaPermissions = Permission::whereIn('name', [
                'view-dashboard',
                'view-nilai',
                'view-absensi',
                'view-mata-pelajaran',
            ])->pluck('name')->toArray();
            $siswa->syncPermissions($siswaPermissions);

            DB::commit();

            $this->command->info('✓ Permissions and Roles seeded successfully!');
            $this->command->info('✓ Total Permissions: ' . Permission::count());
            $this->command->info('✓ Total Roles: ' . Role::count());
            $this->command->newLine();
            $this->command->table(
                ['Role', 'Permissions Count'],
                [
                    ['Super Admin', $superAdmin->permissions->count()],
                    ['Admin', $admin->permissions->count()],
                    ['Operator', $operator->permissions->count()],
                    ['GTK', $gtk->permissions->count()],
                    ['Siswa', $siswa->permissions->count()],
                ]
            );
        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('✗ Error seeding permissions: ' . $e->getMessage());
        }
    }
}
