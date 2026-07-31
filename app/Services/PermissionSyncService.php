<?php

namespace App\Services;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Collection;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSyncService
{
    /**
     * Standard CRUD actions for each module
     */
    protected array $standardActions = [
        'view' => 'Lihat',
        'create' => 'Tambah',
        'edit' => 'Edit',
        'delete' => 'Hapus',
    ];

    protected array $customActionLabels = [
        'reset-password' => 'Reset Password',
        'view-dokumen' => 'Lihat Dokumen',
        'manage' => 'Kelola',
        'assign-siswa' => 'Atur Siswa',
        'remove-siswa' => 'Keluarkan Siswa',
        'transfer-siswa' => 'Pindah Rombel Siswa',
        'assign-wali' => 'Tetapkan Wali Kelas',
        'view-detail' => 'Lihat Detail',
        'approve' => 'Setujui',
        'reject' => 'Tolak',
        'upload-dokumen' => 'Upload Dokumen',
        'input' => 'Input',
        'rekap' => 'Lihat Rekap',
        'export' => 'Export',
        'change-semester' => 'Ubah Semester',
        'set-active' => 'Set Aktif',
        'activate' => 'Aktifkan',
        'assign' => 'Assign',
        'create-role' => 'Buat Role',
        'delete-role' => 'Hapus Role',
        'view-permission' => 'Lihat Permission',
        'manage-permission' => 'Kelola Permission',
        'edit-profile' => 'Edit Profil',
        'view-profile' => 'Lihat Profil',
        'change-password' => 'Ubah Password',
        'verifikasi' => 'Verifikasi',
        'cetak' => 'Cetak',
    ];

    /**
     * Module definitions with their permissions
     * Format: 'module-name' => ['display_name', 'icon', 'permissions' => [...]]
     */
    public function getModuleDefinitions(): array
    {
        return [
            'siswa' => [
                'label' => 'Data Siswa',
                'icon' => 'user-graduate',
                'color' => 'primary',
                'description' => 'Manajemen data siswa',
                'permissions' => [
                    'view-siswa',
                    'create-siswa',
                    'edit-siswa',
                    'delete-siswa',
                    'reset-password-siswa',
                    'view-dokumen-siswa',
                    'manage-nis-lokal',
                ],
            ],
            'gtk' => [
                'label' => 'Data GTK',
                'icon' => 'chalkboard-teacher',
                'color' => 'success',
                'description' => 'Manajemen Guru & Tenaga Kependidikan',
                'permissions' => [
                    'view-gtk',
                    'create-gtk',
                    'edit-gtk',
                    'delete-gtk',
                    'reset-password-gtk',
                ],
            ],
            'gtk-personal' => [
                'label' => 'GTK Personal',
                'icon' => 'user-circle',
                'color' => 'info',
                'description' => 'Akses personal untuk GTK',
                'permissions' => [
                    'view-gtk-dashboard',
                    'edit-gtk-profile',
                    'change-password-gtk',
                ],
            ],
            'users' => [
                'label' => 'Manajemen User',
                'icon' => 'users-cog',
                'color' => 'warning',
                'description' => 'Manajemen user dan akses',
                'permissions' => [
                    'view-users',
                    'create-users',
                    'edit-users',
                    'delete-users',
                    'assign-roles',
                    'assign-permissions',
                    'view-permission',
                    'manage-permission',
                    'create-role',
                    'delete-role',
                ],
            ],
            'tahun-pelajaran' => [
                'label' => 'Tahun Pelajaran',
                'icon' => 'calendar-alt',
                'color' => 'secondary',
                'description' => 'Manajemen tahun pelajaran',
                'permissions' => [
                    'view-tahun-pelajaran',
                    'create-tahun-pelajaran',
                    'edit-tahun-pelajaran',
                    'delete-tahun-pelajaran',
                    'set-active-tahun-pelajaran',
                    'change-semester-tahun-pelajaran',
                ],
            ],
            'kurikulum' => [
                'label' => 'Kurikulum',
                'icon' => 'book-open',
                'color' => 'primary',
                'description' => 'Manajemen kurikulum dan jurusan',
                'permissions' => [
                    'view-kurikulum',
                    'create-kurikulum',
                    'edit-kurikulum',
                    'delete-kurikulum',
                    'manage-jurusan',
                    'activate-kurikulum',
                ],
            ],
            'kelas' => [
                'label' => 'Manajemen Kelas',
                'icon' => 'school',
                'color' => 'success',
                'description' => 'Manajemen kelas dan siswa kelas',
                'permissions' => [
                    'view-kelas',
                    'manage-kelas',
                    'create-kelas',
                    'edit-kelas',
                    'delete-kelas',
                    'assign-siswa-kelas',
                    'remove-siswa-kelas',
                    'transfer-siswa-kelas',
                    'assign-wali-kelas',
                    'view-detail-kelas',
                ],
            ],
            'mutasi' => [
                'label' => 'Mutasi Siswa',
                'icon' => 'exchange-alt',
                'color' => 'info',
                'description' => 'Manajemen mutasi siswa',
                'permissions' => [
                    'view-mutasi',
                    'create-mutasi',
                    'edit-mutasi',
                    'delete-mutasi',
                    'approve-mutasi',
                    'reject-mutasi',
                    'upload-dokumen-mutasi',
                ],
            ],
            'nilai' => [
                'label' => 'Nilai & Rapor',
                'icon' => 'clipboard-list',
                'color' => 'warning',
                'description' => 'Manajemen nilai dan rapor siswa',
                'permissions' => [
                    'view-nilai',
                    'input-nilai',
                    'edit-nilai',
                    'delete-nilai',
                    'cetak-rapor',
                ],
            ],
            'asrama' => [
                'label' => 'Asrama',
                'icon' => 'mosque',
                'color' => 'info',
                'description' => 'Unit, santri, asatidz, kelas, mapel, nilai, dan rapor asrama',
                'permissions' => [
                    'view-asrama',
                    'manage-asrama',
                    'manage-asrama-santri',
                    'manage-asrama-asatidz',
                    'manage-asrama-kelas',
                    'manage-asrama-mapel',
                    'manage-asrama-pengampu',
                    'input-nilai-asrama',
                    'manage-rapor-asrama',
                    'publish-rapor-asrama',
                    'print-rapor-asrama',
                    'view-asrama-portal',
                    'asrama-rapor-access',
                ],
            ],
            'absensi' => [
                'label' => 'Absensi',
                'icon' => 'clipboard-check',
                'color' => 'danger',
                'description' => 'Presensi masuk/pulang, absensi harian, mapel, analitik, dan tindak lanjut',
                'permissions' => [
                    'view-absensi',
                    'create-absensi',
                    'input-absensi',
                    'edit-absensi',
                    'rekap-absensi',
                    'view-student-attendance',
                    'monitor-all-student-attendance',
                    'input-daily-attendance',
                    'input-subject-attendance',
                    'finalize-student-attendance',
                    'edit-final-student-attendance',
                    'view-attendance-analytics',
                    'view-attendance-counseling',
                    'manage-attendance-alerts',
                    'view-attendance-audit',
                ],
            ],
            'laporan' => [
                'label' => 'Laporan',
                'icon' => 'chart-bar',
                'color' => 'secondary',
                'description' => 'Akses laporan dan export',
                'permissions' => [
                    'view-laporan',
                    'export-laporan',
                    'view-activity-log',
                ],
            ],
            'settings' => [
                'label' => 'Pengaturan',
                'icon' => 'cogs',
                'color' => 'dark',
                'description' => 'Pengaturan aplikasi',
                'permissions' => [
                    'manage-settings',
                    'view-profile',
                    'edit-profile',
                ],
            ],
            'verifikasi-ijazah' => [
                'label' => 'Verifikasi Ijazah',
                'icon' => 'certificate',
                'color' => 'purple',
                'description' => 'Verifikasi data ijazah SMP/MTs siswa dengan EMIS',
                'permissions' => [
                    'verifikasi-ijazah',
                ],
            ],
            'dashboard' => [
                'label' => 'Dashboard',
                'icon' => 'tachometer-alt',
                'color' => 'primary',
                'description' => 'Akses dashboard',
                'permissions' => [
                    'view-dashboard',
                    'admin-dashboard-access',
                ],
            ],
            'sekolah-asal' => [
                'label' => 'Sekolah Asal',
                'icon' => 'school',
                'color' => 'info',
                'description' => 'Referensi dan pemetaan sekolah asal siswa',
                'permissions' => [
                    'view-sekolah-asal',
                ],
            ],
            'statistik-siswa' => [
                'label' => 'Statistik Siswa',
                'icon' => 'chart-pie',
                'color' => 'cyan',
                'description' => 'Dashboard statistik siswa dan persebaran data',
                'permissions' => [
                    'view-statistik-siswa',
                ],
            ],
            'pembanding-emis' => [
                'label' => 'Pembanding Data EMIS',
                'icon' => 'exchange-alt',
                'color' => 'teal',
                'description' => 'Melihat dan menyinkronkan snapshot siswa EMIS Lembaga',
                'permissions' => [
                    'view-emis-comparison',
                    'sync-emis-comparison',
                ],
            ],
            'mapel' => [
                'label' => 'Mata Pelajaran',
                'icon' => 'book',
                'color' => 'primary',
                'description' => 'Manajemen data mata pelajaran',
                'permissions' => [
                    'view-mapel',
                    'create-mapel',
                    'edit-mapel',
                    'delete-mapel',
                ],
            ],
            'rdm' => [
                'label' => 'Integrasi RDM',
                'icon' => 'sync-alt',
                'color' => 'warning',
                'description' => 'Sinkronisasi nilai dan integrasi RDM',
                'permissions' => [
                    'view-rdm',
                    'manage-rdm',
                    'manage-rdm-mapping',
                ],
            ],
            'jadwal-pelajaran' => [
                'label' => 'Jadwal Pelajaran',
                'icon' => 'clock',
                'color' => 'secondary',
                'description' => 'Manajemen jadwal pembelajaran per kelas',
                'permissions' => [
                    'view-jadwal-pelajaran',
                    'manage-jadwal-pelajaran',
                    'view-jadwal-mapping',
                    'manage-jadwal-mapping',
                ],
            ],
            'kalender-akademik' => [
                'label' => 'Kalender Akademik',
                'icon' => 'calendar',
                'color' => 'secondary',
                'description' => 'Pengelolaan kalender akademik madrasah',
                'permissions' => [
                    'view-kalender-akademik',
                    'manage-kalender-akademik',
                ],
            ],
            'cetak' => [
                'label' => 'Cetak Dokumen',
                'icon' => 'print',
                'color' => 'success',
                'description' => 'Cetak absensi, kartu identitas, dan arsip foto siswa',
                'permissions' => [
                    'print-kelas',
                    'print-siswa',
                    'print-gtk',
                    'download-foto-kelas',
                ],
            ],
            'kesiswaan' => [
                'label' => 'Kesiswaan',
                'icon' => 'users',
                'color' => 'purple',
                'description' => 'Prestasi, ekstrakurikuler, konseling, dan lulusan',
                'permissions' => [
                    'view-prestasi-siswa',
                    'view-ekstrakurikuler',
                    'view-catatan-konseling',
                    'kesiswaan-lulusan-access',
                ],
            ],
            'pemilihan-osis' => [
                'label' => 'Pemilihan OSIS',
                'icon' => 'vote-yea',
                'color' => 'indigo',
                'description' => 'Melihat, mengatur kandidat, jadwal, pemilih, dan hasil pemilihan OSIS',
                'permissions' => [
                    'view-osis-election',
                    'manage-osis-election',
                ],
            ],
            'presensi-wajah' => [
                'label' => 'Presensi Wajah',
                'icon' => 'fingerprint',
                'color' => 'danger',
                'description' => 'Mode kiosk, registrasi, dan verifikasi wajah',
                'permissions' => [
                    'staff-presensi-menu',
                    'face-registration-access',
                    'face-registration-admin',
                ],
            ],
            'akses-sistem' => [
                'label' => 'Akses Sistem',
                'icon' => 'shield-alt',
                'color' => 'dark',
                'description' => 'Gate umum untuk akses menu dan area admin tertentu',
                'permissions' => [
                    'admin-access',
                    'gtk-menu-only',
                ],
            ],
            'hotspot' => [
                'label' => 'Hotspot',
                'icon' => 'wifi',
                'color' => 'info',
                'description' => 'Akses modul hotspot',
                'permissions' => [
                    'view-hotspot',
                ],
            ],
            'keuangan' => [
                'label' => 'Keuangan',
                'icon' => 'wallet',
                'color' => 'warning',
                'description' => 'Jenis pembayaran, tagihan, pembayaran, dan laporan keuangan',
                'permissions' => [
                    'view-keuangan',
                    'manage-keuangan',
                ],
            ],
            'layanan-surat' => [
                'label' => 'Layanan Surat',
                'icon' => 'envelope-open-text',
                'color' => 'secondary',
                'description' => 'Template dan penerbitan surat',
                'permissions' => [
                    'view-layanan-surat',
                    'manage-layanan-surat',
                ],
            ],
            'informasi' => [
                'label' => 'Informasi',
                'icon' => 'bullhorn',
                'color' => 'info',
                'description' => 'Pengumuman dan informasi sekolah',
                'permissions' => [
                    'view-pengumuman',
                ],
            ],
            'monitoring' => [
                'label' => 'Laporan & Monitoring',
                'icon' => 'chart-line',
                'color' => 'dark',
                'description' => 'Monitoring user dan log aktivitas',
                'permissions' => [
                    'view-monitoring-users',
                    'view-activity-log',
                ],
            ],
            'downloads' => [
                'label' => 'Download Center',
                'icon' => 'download',
                'color' => 'info',
                'description' => 'Manajemen file unduhan publik dan storage',
                'permissions' => [
                    'view-downloads',
                    'create-downloads',
                    'edit-downloads',
                    'delete-downloads',
                    'manage-download-settings',
                ],
            ],
            'tools' => [
                'label' => 'Tools',
                'icon' => 'tools',
                'color' => 'secondary',
                'description' => 'Utilitas verifikasi dan maintenance',
                'permissions' => [
                    'manage-tools',
                ],
            ],
            'cbt' => [
                'label' => 'CBT Exam',
                'icon' => 'desktop',
                'color' => 'primary',
                'description' => 'Pengaturan browser dan notifikasi exam',
                'permissions' => [
                    'manage-cbt',
                ],
            ],
            'smartq' => [
                'label' => 'SMART-Q',
                'icon' => 'star',
                'color' => 'warning',
                'description' => 'Akses SMART-Q kelas unggulan',
                'permissions' => [
                    'view-smartq',
                    'create-smartq',
                    'edit-smartq',
                    'manage-peserta-smartq',
                    'input-nilai-smartq',
                    'manage-moodle-smartq',
                    'manage-kelulusan-smartq',
                    'export-smartq',
                ],
            ],
            'siswa-access' => [
                'label' => 'Portal Siswa',
                'icon' => 'user',
                'color' => 'primary',
                'description' => 'Akses menu portal siswa',
                'permissions' => [
                    'siswa-access',
                    'siswa-menu-only',
                    'siswa-smartq-access',
                    'siswa-graduation-announcement-access',
                ],
            ],
        ];
    }

    public function getPermissionCatalog(Collection|array $permissions): array
    {
        $definitions = $this->getModuleDefinitions();
        $permissionItems = collect($permissions)->map(function ($permission) {
            if (is_string($permission)) {
                return ['name' => $permission];
            }

            return [
                'id' => $permission->id ?? null,
                'name' => $permission->name,
                'model' => $permission,
            ];
        });

        $catalog = [];

        foreach ($definitions as $moduleKey => $module) {
            $items = [];
            foreach ($module['permissions'] as $permissionName) {
                $entry = $permissionItems->firstWhere('name', $permissionName);
                if (!$entry) {
                    continue;
                }

                $items[] = array_merge($entry, [
                    'label' => $this->humanizePermissionLabel($permissionName, $moduleKey),
                ]);
            }

            if (!empty($items)) {
                $catalog[$moduleKey] = [
                    'key' => $moduleKey,
                    'label' => $module['label'],
                    'icon' => $module['icon'] ?? 'cube',
                    'color' => $module['color'] ?? 'primary',
                    'description' => $module['description'] ?? '',
                    'items' => $items,
                ];
            }
        }

        $registeredNames = collect($catalog)->flatMap(fn ($module) => collect($module['items'])->pluck('name'))->values()->all();
        $uncategorized = $permissionItems->filter(fn ($entry) => !in_array($entry['name'], $registeredNames, true))->values();

        if ($uncategorized->isNotEmpty()) {
            $catalog['lainnya'] = [
                'key' => 'lainnya',
                'label' => 'Fitur Lainnya',
                'icon' => 'ellipsis-h',
                'color' => 'secondary',
                'description' => 'Permission yang belum masuk katalog modul.',
                'items' => $uncategorized->map(fn ($entry) => array_merge($entry, [
                    'label' => $this->humanizePermissionLabel($entry['name']),
                ]))->all(),
            ];
        }

        return $catalog;
    }

    public function summarizePermissionsByModule(Collection|array $permissions): array
    {
        return collect($this->getPermissionCatalog($permissions))
            ->map(function ($module) {
                return [
                    'key' => $module['key'],
                    'label' => $module['label'],
                    'icon' => $module['icon'],
                    'color' => $module['color'],
                    'count' => count($module['items']),
                    'preview' => collect($module['items'])->take(3)->pluck('label')->values()->all(),
                ];
            })
            ->values()
            ->all();
    }

    public function humanizePermissionLabel(string $permissionName, ?string $moduleKey = null): string
    {
        $parts = explode('-', $permissionName);

        if (count($parts) === 1) {
            return ucwords(str_replace('-', ' ', $permissionName));
        }

        $actionPart = $parts[0];
        $rest = implode('-', array_slice($parts, 1));
        $compoundAction = count($parts) > 2 ? implode('-', array_slice($parts, 0, 2)) : $actionPart;

        if (isset($this->customActionLabels[$permissionName])) {
            return $this->customActionLabels[$permissionName];
        }

        if (isset($this->customActionLabels[$compoundAction])) {
            return $this->customActionLabels[$compoundAction];
        }

        if (isset($this->customActionLabels[$actionPart])) {
            return $this->customActionLabels[$actionPart];
        }

        if (isset($this->standardActions[$actionPart])) {
            return $this->standardActions[$actionPart];
        }

        return ucwords(str_replace('-', ' ', $rest ?: $permissionName));
    }

    public function scanPermissionAudit(): array
    {
        $definedPermissions = collect($this->getModuleDefinitions())
            ->flatMap(fn ($module) => $module['permissions'])
            ->unique()
            ->values()
            ->all();

        $registeredPermissions = Permission::pluck('name')->toArray();
        $routePermissions = array_keys($this->scanRoutesForPermissions());
        $menuEntries = $this->scanMenuForPermissions();
        $menuPermissions = collect($menuEntries)->pluck('name')->unique()->values()->all();

        $unregistered = collect(array_merge($routePermissions, $menuPermissions, $definedPermissions))
            ->unique()
            ->reject(fn ($permission) => in_array($permission, $registeredPermissions, true))
            ->values()
            ->all();

        $uncatalogued = collect($registeredPermissions)
            ->reject(fn ($permission) => in_array($permission, $definedPermissions, true))
            ->values()
            ->all();

        $menuMap = collect($menuEntries)
            ->groupBy('name')
            ->map(function ($entries, $permissionName) {
                return [
                    'permission' => $permissionName,
                    'menus' => $entries->pluck('menu_path')->unique()->values()->all(),
                    'count' => $entries->pluck('menu_path')->unique()->count(),
                ];
            })
            ->filter(fn ($entry) => $entry['count'] > 1)
            ->sortByDesc('count')
            ->values()
            ->all();

        return [
            'unregistered' => $unregistered,
            'uncatalogued' => $uncatalogued,
            'shared_menu_permissions' => $menuMap,
            'registered_total' => count($registeredPermissions),
            'catalog_total' => count($definedPermissions),
            'route_total' => count($routePermissions),
            'menu_total' => count($menuPermissions),
        ];
    }

    /**
     * Scan routes for permissions used in middleware
     */
    public function scanRoutesForPermissions(): array
    {
        $foundPermissions = [];
        
        foreach (Route::getRoutes() as $route) {
            $middlewares = $route->gatherMiddleware();
            
            foreach ($middlewares as $middleware) {
                if (!is_string($middleware)) {
                    continue;
                }

                // Match permission:xxx or can:xxx patterns
                if (preg_match('/^(permission|can):(.+)$/', $middleware, $matches)) {
                    $permissionName = $matches[2];
                    $foundPermissions[$permissionName] = [
                        'name' => $permissionName,
                        'route' => $route->uri(),
                        'methods' => implode('|', $route->methods()),
                        'source' => 'route',
                    ];
                }
            }
        }
        
        return $foundPermissions;
    }

    /**
     * Scan menu config for permissions
     */
    public function scanMenuForPermissions(): array
    {
        $foundPermissions = [];
        $menu = config('adminlte.menu', []);
        
        $this->extractPermissionsFromMenu($menu, $foundPermissions);
        
        return $foundPermissions;
    }

    /**
     * Recursively extract permissions from menu array
     */
    protected function extractPermissionsFromMenu(array $menu, array &$permissions, array $parents = []): void
    {
        foreach ($menu as $item) {
            $label = $item['text'] ?? 'Unknown';
            $currentPath = array_merge($parents, [$label]);

            if (isset($item['can'])) {
                $permissionNames = is_array($item['can']) ? $item['can'] : [$item['can']];

                foreach ($permissionNames as $permissionName) {
                    if (!is_string($permissionName) || trim($permissionName) === '') {
                        continue;
                    }

                    $permissions[] = [
                        'name' => $permissionName,
                        'menu' => $label,
                        'menu_path' => implode(' > ', $currentPath),
                        'source' => 'menu',
                    ];
                }
            }
            
            if (isset($item['submenu']) && is_array($item['submenu'])) {
                $this->extractPermissionsFromMenu($item['submenu'], $permissions, $currentPath);
            }
        }
    }

    /**
     * Sync all permissions from module definitions
     */
    public function syncPermissionsFromModules(): array
    {
        $modules = $this->getModuleDefinitions();
        $created = 0;
        $existing = 0;
        
        foreach ($modules as $moduleKey => $module) {
            foreach ($module['permissions'] as $permName) {
                $permission = Permission::firstOrCreate(
                    ['name' => $permName, 'guard_name' => 'web'],
                    ['name' => $permName, 'guard_name' => 'web']
                );
                
                if ($permission->wasRecentlyCreated) {
                    $created++;
                } else {
                    $existing++;
                }
            }
        }
        
        return [
            'created' => $created,
            'existing' => $existing,
        ];
    }

    /**
     * Register a single permission if not exists
     */
    public function registerPermission(string $name): Permission
    {
        return Permission::firstOrCreate(
            ['name' => $name, 'guard_name' => 'web'],
            ['name' => $name, 'guard_name' => 'web']
        );
    }

    /**
     * Register multiple permissions
     */
    public function registerPermissions(array $names): array
    {
        $results = [];
        foreach ($names as $name) {
            $results[$name] = $this->registerPermission($name);
        }
        return $results;
    }

    /**
     * Get permissions grouped by module for display
     */
    public function getPermissionsGroupedByModule(): array
    {
        $modules = $this->getModuleDefinitions();
        $allPermissions = Permission::all()->keyBy('name');
        
        $result = [];
        foreach ($modules as $moduleKey => $module) {
            $moduleData = [
                'key' => $moduleKey,
                'label' => $module['label'],
                'icon' => $module['icon'],
                'color' => $module['color'] ?? 'primary',
                'description' => $module['description'],
                'permissions' => [],
            ];
            
            foreach ($module['permissions'] as $permName) {
                $permission = $allPermissions->get($permName);
                $moduleData['permissions'][] = [
                    'id' => $permission?->id,
                    'name' => $permName,
                    'exists' => $permission !== null,
                ];
            }
            
            $result[$moduleKey] = $moduleData;
        }
        
        return $result;
    }

    /**
     * Get role permission matrix for display
     * Returns: [role_id => [permission_name => bool, ...], ...]
     */
    public function getRolePermissionMatrix(): array
    {
        $roles = Role::with('permissions')->get();
        
        $matrix = [];
        foreach ($roles as $role) {
            $rolePermissions = $role->permissions->pluck('name')->toArray();
            $matrix[$role->id] = [];
            
            // Get all permissions from modules
            $modules = $this->getModuleDefinitions();
            foreach ($modules as $module) {
                foreach ($module['permissions'] as $permName) {
                    $matrix[$role->id][$permName] = in_array($permName, $rolePermissions);
                }
            }
        }
        
        return $matrix;
    }

    /**
     * Get unregistered permissions (permissions in module definitions that are not in database)
     */
    public function getUnregisteredPermissions(): array
    {
        $modules = $this->getModuleDefinitions();
        $existingPermissions = Permission::pluck('name')->toArray();
        
        $unregistered = [];
        foreach ($modules as $module) {
            foreach ($module['permissions'] as $permName) {
                if (!in_array($permName, $existingPermissions)) {
                    $unregistered[] = $permName;
                }
            }
        }
        
        return $unregistered;
    }

    /**
     * Get system roles that cannot be deleted
     */
    public function getSystemRoles(): array
    {
        return [
            'Super Admin',
            'Siswa',
            'GTK',
        ];
    }

    /**
     * Get protected roles that cannot have permissions modified
     */
    public function getProtectedRoles(): array
    {
        return [
            'Super Admin', // Always has all permissions
        ];
    }

    /**
     * Check if a role is protected
     */
    public function isProtectedRole(string $roleName): bool
    {
        return in_array($roleName, $this->getProtectedRoles());
    }

    /**
     * Check if a role is a system role
     */
    public function isSystemRole(string $roleName): bool
    {
        return in_array($roleName, $this->getSystemRoles());
    }
}
