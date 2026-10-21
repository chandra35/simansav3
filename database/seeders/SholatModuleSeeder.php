<?php

namespace Database\Seeders;

use App\Models\SholatSetting;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class SholatModuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 2. Buat Permission Modul Sholat
        $permissions = [
            'sholat-manage-setting',
            'sholat-input',
            'sholat-view',
            'sholat-monitoring',
            'sholat-lapor-udzur',
            'sholat-export',
        ];

        foreach ($permissions as $permName) {
            Permission::firstOrCreate(['name' => $permName]);
        }

        // 3. Assign Permission ke Role
        $superAdmin = Role::firstOrCreate(['name' => 'Super Admin']);
        $superAdmin->givePermissionTo(Permission::all());

        $admin = Role::firstOrCreate(['name' => 'Admin']);
        $admin->givePermissionTo($permissions);

        $kepala = Role::firstOrCreate(['name' => 'Kepala Madrasah']);
        $kepala->givePermissionTo(['sholat-view', 'sholat-monitoring', 'sholat-export']);

        $waka = Role::firstOrCreate(['name' => 'WAKA']);
        $waka->givePermissionTo(['sholat-view', 'sholat-monitoring', 'sholat-export']);

        $waliKelas = Role::firstOrCreate(['name' => 'Wali Kelas']);
        $waliKelas->givePermissionTo(['sholat-input', 'sholat-view', 'sholat-monitoring', 'sholat-export']);

        $gtk = Role::firstOrCreate(['name' => 'GTK']);
        $gtk->givePermissionTo(['sholat-input', 'sholat-view']);

        $bk = Role::firstOrCreate(['name' => 'BK']);
        $bk->givePermissionTo(['sholat-view', 'sholat-monitoring', 'sholat-export']);

        $siswa = Role::firstOrCreate(['name' => 'Siswa']);
        $siswa->givePermissionTo(['sholat-view', 'sholat-lapor-udzur']);

        // 4. Seeding Default Sholat Settings
        $defaultSettings = [
            [
                'kode' => 'dzuhur',
                'nama' => 'Sholat Dzuhur',
                'is_active' => true,
                'jam_mulai' => '11:45',
                'jam_selesai' => '13:00',
                'hari_aktif' => ['senin', 'selasa', 'rabu', 'kamis', 'sabtu'],
                'target_gender' => 'all',
                'urutan' => 1,
                'keterangan' => 'Sholat Dzuhur berjamaah di Masjid Al-Ikhlas MAN 1 Metro',
            ],
            [
                'kode' => 'ashar',
                'nama' => 'Sholat Ashar',
                'is_active' => true,
                'jam_mulai' => '15:15',
                'jam_selesai' => '16:30',
                'hari_aktif' => ['senin', 'selasa', 'rabu', 'kamis', 'sabtu'],
                'target_gender' => 'all',
                'urutan' => 2,
                'keterangan' => 'Sholat Ashar berjamaah di Masjid Al-Ikhlas MAN 1 Metro',
            ],
            [
                'kode' => 'dhuha',
                'nama' => 'Sholat Dhuha',
                'is_active' => false,
                'jam_mulai' => '06:45',
                'jam_selesai' => '07:30',
                'hari_aktif' => ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu'],
                'target_gender' => 'all',
                'urutan' => 3,
                'keterangan' => 'Pembiasaan sholat Dhuha pagi',
            ],
            [
                'kode' => 'jumat',
                'nama' => 'Sholat Jumat',
                'is_active' => false,
                'jam_mulai' => '11:30',
                'jam_selesai' => '13:00',
                'hari_aktif' => ['jumat'],
                'target_gender' => 'L',
                'urutan' => 4,
                'keterangan' => 'Sholat Jumat berjamaah di masjid untuk siswa putra',
            ],
            [
                'kode' => 'keputrian',
                'nama' => 'Kajian Keputrian',
                'is_active' => false,
                'jam_mulai' => '11:30',
                'jam_selesai' => '13:00',
                'hari_aktif' => ['jumat'],
                'target_gender' => 'P',
                'urutan' => 5,
                'keterangan' => 'Kajian keputrian bagi siswi saat pelaksanaan Sholat Jumat',
            ],
        ];

        foreach ($defaultSettings as $setting) {
            SholatSetting::firstOrCreate(
                ['kode' => $setting['kode']],
                $setting
            );
        }

        $this->command->info('✅ Modul Sholat permissions & default settings successfully seeded!');
    }
}
