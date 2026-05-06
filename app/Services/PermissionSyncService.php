<?php

namespace App\Services;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\File;
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
                    'view-pip',
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
            'absensi' => [
                'label' => 'Absensi',
                'icon' => 'clipboard-check',
                'color' => 'danger',
                'description' => 'Manajemen absensi siswa',
                'permissions' => [
                    'view-absensi',
                    'input-absensi',
                    'edit-absensi',
                    'rekap-absensi',
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
                ],
            ],
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
    protected function extractPermissionsFromMenu(array $menu, array &$permissions): void
    {
        foreach ($menu as $item) {
            if (isset($item['can']) && is_string($item['can'])) {
                $permissions[$item['can']] = [
                    'name' => $item['can'],
                    'menu' => $item['text'] ?? 'Unknown',
                    'source' => 'menu',
                ];
            }
            
            if (isset($item['submenu']) && is_array($item['submenu'])) {
                $this->extractPermissionsFromMenu($item['submenu'], $permissions);
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
