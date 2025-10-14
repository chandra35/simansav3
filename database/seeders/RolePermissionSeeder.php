<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // ============================================
        // PERMISSIONS
        // ============================================
        
        $permissions = [
            // Dashboard
            'view-dashboard',
            
            // Siswa Management
            'view-siswa',
            'create-siswa',
            'edit-siswa',
            'delete-siswa',
            'reset-password-siswa',
            'view-dokumen-siswa',
            
            // Tahun Pelajaran Management
            'view-tahun-pelajaran',
            'create-tahun-pelajaran',
            'edit-tahun-pelajaran',
            'delete-tahun-pelajaran',
            'set-active-tahun-pelajaran',
            'change-semester-tahun-pelajaran',
            
            // Kurikulum Management
            'view-kurikulum',
            'create-kurikulum',
            'edit-kurikulum',
            'delete-kurikulum',
            'manage-jurusan',
            'activate-kurikulum',
            
            // Kelas Management
            'view-kelas',
            'create-kelas',
            'edit-kelas',
            'delete-kelas',
            'assign-siswa-kelas',
            'remove-siswa-kelas',
            'assign-wali-kelas',
            'view-detail-kelas',
            
            // Mutasi Siswa
            'view-mutasi',
            'create-mutasi',
            'edit-mutasi',
            'delete-mutasi',
            'approve-mutasi',
            'reject-mutasi',
            'upload-dokumen-mutasi',
            
            // User Management
            'view-users',
            'create-users',
            'edit-users',
            'delete-users',
            'assign-roles',
            'assign-permissions',
            
            // GTK Management (Future)
            'view-gtk',
            'create-gtk',
            'edit-gtk',
            'delete-gtk',
            
            // Nilai & Rapor (Future)
            'view-nilai',
            'input-nilai',
            'edit-nilai',
            'delete-nilai',
            'cetak-rapor',
            
            // Absensi (Future)
            'view-absensi',
            'input-absensi',
            'edit-absensi',
            'rekap-absensi',
            
            // Laporan
            'view-laporan',
            'export-laporan',
            'view-activity-log',
            
            // Settings
            'manage-settings',
            'view-profile',
            'edit-profile',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $this->command->info('✅ Permissions created successfully!');

        // ============================================
        // ROLES & ASSIGN PERMISSIONS
        // ============================================

        // 1. SUPER ADMIN - All Permissions
        $superAdmin = Role::firstOrCreate(['name' => 'Super Admin']);
        $superAdmin->givePermissionTo(Permission::all());
        $this->command->info('✅ Super Admin role created with ALL permissions');

        // 2. KEPALA MADRASAH - Full Access except technical settings
        $kepalaMadrasah = Role::firstOrCreate(['name' => 'Kepala Madrasah']);
        $kepalaMadrasah->givePermissionTo([
            'view-dashboard',
            
            // Full Siswa Access
            'view-siswa', 'create-siswa', 'edit-siswa', 'delete-siswa', 'reset-password-siswa', 'view-dokumen-siswa',
            
            // Full Tahun Pelajaran Access
            'view-tahun-pelajaran', 'create-tahun-pelajaran', 'edit-tahun-pelajaran', 'delete-tahun-pelajaran', 
            'set-active-tahun-pelajaran', 'change-semester-tahun-pelajaran',
            
            // Full Kurikulum Access
            'view-kurikulum', 'create-kurikulum', 'edit-kurikulum', 'delete-kurikulum', 'manage-jurusan', 'activate-kurikulum',
            
            // Full Kelas Access
            'view-kelas', 'create-kelas', 'edit-kelas', 'delete-kelas', 
            'assign-siswa-kelas', 'remove-siswa-kelas', 'assign-wali-kelas', 'view-detail-kelas',
            
            // Full Mutasi Access
            'view-mutasi', 'create-mutasi', 'edit-mutasi', 'delete-mutasi', 
            'approve-mutasi', 'reject-mutasi', 'upload-dokumen-mutasi',
            
            // GTK Access
            'view-gtk', 'create-gtk', 'edit-gtk', 'delete-gtk',
            
            // Nilai & Rapor
            'view-nilai', 'input-nilai', 'edit-nilai', 'delete-nilai', 'cetak-rapor',
            
            // Absensi
            'view-absensi', 'input-absensi', 'edit-absensi', 'rekap-absensi',
            
            // Laporan
            'view-laporan', 'export-laporan', 'view-activity-log',
            
            // Profile
            'view-profile', 'edit-profile',
        ]);
        $this->command->info('✅ Kepala Madrasah role created');

        // 3. WAKA (Wakil Kepala) - Similar to Kepala but no delete critical data
        $waka = Role::firstOrCreate(['name' => 'WAKA']);
        $waka->givePermissionTo([
            'view-dashboard',
            'view-siswa', 'create-siswa', 'edit-siswa', 'view-dokumen-siswa',
            'view-tahun-pelajaran', 'view-kurikulum',
            'view-kelas', 'create-kelas', 'edit-kelas', 'assign-siswa-kelas', 'remove-siswa-kelas', 'view-detail-kelas',
            'view-mutasi', 'create-mutasi', 'edit-mutasi', 'approve-mutasi', 'reject-mutasi',
            'view-gtk',
            'view-nilai', 'input-nilai', 'edit-nilai', 'cetak-rapor',
            'view-absensi', 'input-absensi', 'edit-absensi', 'rekap-absensi',
            'view-laporan', 'export-laporan',
            'view-profile', 'edit-profile',
        ]);
        $this->command->info('✅ WAKA role created');

        // 4. ADMIN - Data management focus
        $admin = Role::firstOrCreate(['name' => 'Admin']);
        $admin->givePermissionTo([
            'view-dashboard',
            'view-siswa', 'create-siswa', 'edit-siswa', 'reset-password-siswa', 'view-dokumen-siswa',
            'view-tahun-pelajaran', 'create-tahun-pelajaran', 'edit-tahun-pelajaran',
            'view-kurikulum',
            'view-kelas', 'create-kelas', 'edit-kelas', 'assign-siswa-kelas', 'remove-siswa-kelas', 'view-detail-kelas',
            'view-mutasi', 'create-mutasi', 'edit-mutasi', 'upload-dokumen-mutasi',
            'view-gtk', 'create-gtk', 'edit-gtk',
            'view-laporan', 'export-laporan',
            'view-profile', 'edit-profile',
        ]);
        $this->command->info('✅ Admin role created');

        // 5. OPERATOR - Similar to Admin
        $operator = Role::firstOrCreate(['name' => 'Operator']);
        $operator->givePermissionTo([
            'view-dashboard',
            'view-siswa', 'create-siswa', 'edit-siswa', 'view-dokumen-siswa',
            'view-tahun-pelajaran', 'view-kurikulum', 'view-kelas', 'view-detail-kelas',
            'view-mutasi', 'create-mutasi', 'upload-dokumen-mutasi',
            'view-gtk',
            'view-laporan', 'export-laporan',
            'view-profile', 'edit-profile',
        ]);
        $this->command->info('✅ Operator role created');

        // 6. BK (Bimbingan Konseling) - Student data & counseling focus
        $bk = Role::firstOrCreate(['name' => 'BK']);
        $bk->givePermissionTo([
            'view-dashboard',
            'view-siswa', 'edit-siswa', 'view-dokumen-siswa',
            'view-kelas', 'view-detail-kelas',
            'view-mutasi',
            'view-absensi', 'rekap-absensi',
            'view-laporan',
            'view-profile', 'edit-profile',
        ]);
        $this->command->info('✅ BK role created');

        // 7. WALI KELAS - Class & student management
        $waliKelas = Role::firstOrCreate(['name' => 'Wali Kelas']);
        $waliKelas->givePermissionTo([
            'view-dashboard',
            'view-siswa', 'view-dokumen-siswa',
            'view-kelas', 'view-detail-kelas',
            'view-nilai', 'input-nilai',
            'view-absensi', 'input-absensi', 'rekap-absensi',
            'cetak-rapor',
            'view-profile', 'edit-profile',
        ]);
        $this->command->info('✅ Wali Kelas role created');

        // 8. GURU - Teaching focus
        $guru = Role::firstOrCreate(['name' => 'Guru']);
        $guru->givePermissionTo([
            'view-dashboard',
            'view-siswa', 'view-kelas', 'view-detail-kelas',
            'view-nilai', 'input-nilai',
            'view-absensi', 'input-absensi',
            'view-profile', 'edit-profile',
        ]);
        $this->command->info('✅ Guru role created');

        // 9. STAFF TU (Tata Usaha) - Administrative support
        $staffTU = Role::firstOrCreate(['name' => 'Staff TU']);
        $staffTU->givePermissionTo([
            'view-dashboard',
            'view-siswa', 'create-siswa', 'edit-siswa', 'view-dokumen-siswa',
            'view-kelas', 'view-detail-kelas',
            'view-mutasi', 'create-mutasi', 'upload-dokumen-mutasi',
            'view-gtk',
            'view-laporan',
            'view-profile', 'edit-profile',
        ]);
        $this->command->info('✅ Staff TU role created');

        // 10. SISWA - Student access
        $siswa = Role::firstOrCreate(['name' => 'Siswa']);
        $siswa->givePermissionTo([
            'view-profile', 'edit-profile',
        ]);
        $this->command->info('✅ Siswa role created');

        // ============================================
        // ASSIGN ROLES TO EXISTING USERS
        // ============================================

        // Assign Super Admin role to superadmin user
        $superAdminUser = User::where('username', 'superadmin')->first();
        if ($superAdminUser) {
            $superAdminUser->assignRole('Super Admin');
            $this->command->info('✅ Super Admin role assigned to superadmin user');
        }

        // Assign Siswa role to all users with role 'siswa'
        User::where('role', 'siswa')->each(function ($user) {
            $user->assignRole('Siswa');
        });
        $this->command->info('✅ Siswa role assigned to all siswa users');

        $this->command->line('');
        $this->command->info('🎉 RBAC System setup completed!');
        $this->command->line('');
        $this->command->info('📋 Summary:');
        $this->command->info('   - Total Permissions: ' . Permission::count());
        $this->command->info('   - Total Roles: ' . Role::count());
        $this->command->line('');
        $this->command->info('👥 Roles created:');
        $this->command->info('   1. Super Admin (All Permissions)');
        $this->command->info('   2. Kepala Madrasah (Full Management Access)');
        $this->command->info('   3. WAKA (Management Access - No Delete)');
        $this->command->info('   4. Admin (Data Management)');
        $this->command->info('   5. Operator (Data Entry)');
        $this->command->info('   6. BK (Student Counseling)');
        $this->command->info('   7. Wali Kelas (Class Management)');
        $this->command->info('   8. Guru (Teaching)');
        $this->command->info('   9. Staff TU (Administrative Support)');
        $this->command->info('  10. Siswa (Student Access)');
    }
}
