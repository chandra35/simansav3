<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\TugasTambahan;
use App\Services\PermissionSyncService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Yajra\DataTables\Facades\DataTables;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('view-users');

        // Statistik dan tabel memakai populasi akun operasional yang sama.
        $directoryUsers = $this->accountDirectoryQuery();
        $stats = [
            'total_users' => (clone $directoryUsers)->count(),
            'active' => (clone $directoryUsers)->where('is_active', true)->count(),
            'inactive' => (clone $directoryUsers)->where('is_active', false)->count(),
            'admin' => (clone $directoryUsers)->role(['Super Admin', 'Admin'])->count(),
            'gtk' => (clone $directoryUsers)->whereHas('gtk', fn ($gtk) => $gtk->where('status_aktif', true))->count(),
            'siswa' => (clone $directoryUsers)->whereHas('siswa', fn ($siswa) => $siswa->where('status_siswa', 'aktif'))->count(),
        ];

        // Get all roles for filter
        $roles = Role::orderBy('name')->get();

        return view('admin.users.index', compact('stats', 'roles'));
    }

    /**
     * Get users data for DataTables (AJAX)
     */
    public function data(Request $request)
    {
        $users = $this->accountDirectoryQuery()->with([
            'roles', 'latestSession',
            'gtk:id,user_id,nama_lengkap,jenis_kelamin,foto_profile',
            'siswa:id,user_id,nama_lengkap,jenis_kelamin,foto_profile',
        ])->select('users.*');

        // Filter by Role
        if ($request->filled('role')) {
            $users->whereHas('roles', function($q) use ($request) {
                $q->where('name', $request->role);
            });
        }

        if ($request->filled('account_type')) {
            match ($request->account_type) {
                'gtk' => $users->whereHas('gtk'),
                'siswa' => $users->whereHas('siswa'),
                'lainnya' => $users->whereDoesntHave('gtk')->whereDoesntHave('siswa'),
                default => null,
            };
        }

        // Search functionality
        if ($request->has('search') && $request->search['value']) {
            $search = $request->search['value'];
            $users->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $totalRecords = $this->accountDirectoryQuery()->count();
        $filteredRecords = $users->count();
        
        // Pagination - Handle "All" option
        if ($request->has('start') && $request->has('length')) {
            $length = $request->length;
            if ($length != -1) {
                $users->skip($request->start)->take($length);
            }
        }

        // Ordering
        if ($request->has('order')) {
            $columns = [null, 'name', 'email', null, 'is_active', null];
            $orderColumn = $columns[$request->order[0]['column']] ?? 'created_at';
            $orderDirection = $request->order[0]['dir'];
            $users->orderBy($orderColumn, $orderDirection);
        } else {
            $users->latest();
        }

        $data = $users->get()->map(function($user, $index) use ($request) {
            // Generate roles badges
            $rolesBadges = $user->roles->map(function ($role) {
                $colors = [
                    'Super Admin' => 'danger',
                    'Admin' => 'primary',
                    'Operator' => 'info',
                    'GTK' => 'success',
                    'Siswa' => 'secondary',
                ];
                $color = $colors[$role->name] ?? 'secondary';
                return "<span class='badge badge-{$color}'>".e($role->name).'</span>';
            })->implode(' ');

            $isOnline = $user->latestSession?->isStillOnline() ?? false;
            $lastSeen = $user->latestSession?->last_activity?->diffForHumans();
            $name = e($user->name);
            $username = e($user->username);
            $email = e($user->email ?: 'Email belum diisi');
            $phone = e($user->phone ?: 'Telepon belum diisi');
            $initials = collect(preg_split('/\s+/', trim((string) $user->name)))
                ->filter()->take(2)->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))->implode('');
            $accountType = $user->gtk ? 'GTK' : ($user->siswa ? 'Siswa' : 'Akun sistem');
            $photoUrl = $user->gtk?->foto_profile_url
                ?? $user->siswa?->foto_profile_url
                ?? ($user->avatar ? $user->avatar_url : null);
            $photo = $photoUrl
                ? "<img src='".e($photoUrl)."' alt='Foto {$name}' loading='lazy' onerror='this.remove()'>"
                : '';
            $primaryRole = $user->roles->first()?->name ?: 'Tanpa role';
            $identityHtml = "<div class='simansa-user-identity'>
                <span class='simansa-user-avatar'><b>".e($initials ?: 'U')."</b>{$photo}</span>
                <div><strong>{$name}</strong><span><i class='fas fa-at'></i>{$username}</span><small>".e($accountType).' · '.e($primaryRole)."</small></div>
            </div>";
            $contactHtml = "<div class='simansa-user-contact'>
                <span title='{$email}'><i class='fas fa-envelope'></i>{$email}</span>
                <span title='{$phone}'><i class='fas fa-phone'></i>{$phone}</span>
            </div>";

            // Generate status toggle
            $checked = $user->is_active ? 'checked' : '';
            $statusToggle = auth()->user()->can('edit-users')
                ? "<div class='custom-control custom-switch d-inline-block'><input type='checkbox' class='custom-control-input toggle-status' id='status{$user->id}' data-id='{$user->id}' {$checked}><label class='custom-control-label' for='status{$user->id}'></label></div>"
                : '';
            $statusHtml = "<div class='simansa-user-status'>
                <span class='simansa-user-presence ".($isOnline ? 'is-online' : 'is-offline')."'><i></i>".($isOnline ? 'Online' : 'Offline')."</span>
                {$statusToggle}
                <small>".($user->is_active ? 'Akun aktif' : 'Akun nonaktif').' · '.e($lastSeen ?: 'Belum ada sesi')."</small>
            </div>";

            // Satu dropdown menjaga tabel tetap ringkas tanpa menghilangkan aksi lama.
            $actionItems = '';
            if (auth()->user()->can('assign-roles')) {
                $assignmentFormUrl = e(route('admin.users.assign-role-form', $user));
                $assignmentUrl = e(route('admin.users.assign-role', $user));
                $actionItems .= "<button type='button' class='dropdown-item btn-assign-role' data-id='{$user->id}' data-name='{$name}' data-form-url='{$assignmentFormUrl}' data-assignment-url='{$assignmentUrl}' onclick='return window.openUserPermission(this);'><i class='fas fa-user-tag text-warning'></i>Role & permission</button>";
            }
            if (auth()->user()->can('edit-users') && !$user->isSiswa() && $user->id !== auth()->id()) {
                $resetUrl = route('admin.users.reset-password', $user->id);
                $actionItems .= "<button type='button' class='dropdown-item btn-reset-password' data-url='".e($resetUrl)."' data-name='{$name}' data-username='{$username}'><i class='fas fa-key text-secondary'></i>Reset password</button>";
            }
            if (auth()->user()->can('edit-users')) {
                $actionItems .= "<a href='".e(route('admin.users.edit', $user->id))."' class='dropdown-item'><i class='fas fa-edit text-primary'></i>Edit akun</a>";
            }
            if (auth()->user()->can('delete-users') && $user->id !== auth()->id()) {
                $actionItems .= "<div class='dropdown-divider'></div><button type='button' class='dropdown-item text-danger btn-delete' data-id='{$user->id}' data-name='{$name}'><i class='fas fa-trash'></i>Hapus akun</button>";
            }
            $actions = $actionItems === '' ? '<span class="text-muted">—</span>' : "<div class='dropdown simansa-user-actions'><button type='button' class='btn btn-sm btn-outline-primary dropdown-toggle' data-toggle='dropdown' data-boundary='viewport' aria-haspopup='true' aria-expanded='false'><i class='fas fa-ellipsis-v mr-1'></i>Aksi</button><div class='dropdown-menu dropdown-menu-right'>{$actionItems}</div></div>";

            return [
                'DT_RowIndex' => $request->start + $index + 1,
                'identity' => $identityHtml,
                'contact' => $contactHtml,
                'name' => $user->name,
                'username' => $user->username,
                'email' => $user->email,
                'phone' => $user->phone ?? '-',
                'roles' => '<div class="roles-badges">'.($rolesBadges ?: '<span class="badge badge-secondary">Tanpa role</span>').'</div>',
                'status' => $statusHtml,
                'action' => $actions
            ];
        });

        return response()->json([
            'draw' => intval($request->draw),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $data
        ]);
    }

    /**
     * Akun yang relevan untuk direktori operasional.
     *
     * Akun sistem tetap tersedia. Akun yang terhubung ke GTK hanya dimuat
     * ketika GTK aktif, sedangkan akun siswa hanya dimuat ketika siswa aktif.
     * Histori alumni, lulus, keluar, dan mutasi keluar tetap tersimpan pada
     * modul asalnya tanpa membebani daftar akun aktif.
     */
    private function accountDirectoryQuery(): Builder
    {
        return User::query()->where(function (Builder $query): void {
            $query->where(function (Builder $systemAccount): void {
                $systemAccount->whereDoesntHave('gtk')->whereDoesntHave('siswa');
            })->orWhereHas('gtk', function (Builder $gtk): void {
                $gtk->where('status_aktif', true);
            })->orWhereHas('siswa', function (Builder $siswa): void {
                $siswa->where('status_siswa', 'aktif');
            });
        });
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create-users');

        $roles = Role::all();
        return view('admin.users.create', compact('roles'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create-users');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username',
            'email' => 'required|email|max:255|unique:users,email',
            'phone' => 'nullable|string|max:15',
            'password' => 'required|string|min:8|confirmed',
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,id',
        ], [
            'name.required' => 'Nama wajib diisi',
            'username.required' => 'Username wajib diisi',
            'username.unique' => 'Username sudah digunakan',
            'email.required' => 'Email wajib diisi',
            'email.email' => 'Format email tidak valid',
            'email.unique' => 'Email sudah digunakan',
            'password.required' => 'Password wajib diisi',
            'password.min' => 'Password minimal 8 karakter',
            'password.confirmed' => 'Konfirmasi password tidak cocok',
        ]);

        DB::beginTransaction();
        try {
            $user = User::create([
                'name' => $validated['name'],
                'username' => $validated['username'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'password' => Hash::make($validated['password']),
                'role' => 'operator', // default role column (legacy)
                'is_first_login' => true,
            ]);
            $user->readable_password = $validated['password'];
            $user->save();

            // Assign roles jika ada
            if (!empty($validated['roles'])) {
                $roles = Role::whereIn('id', $validated['roles'])->get();
                $user->syncRoles($roles);
            }

            DB::commit();

            return redirect()
                ->route('admin.users.index')
                ->with('success', 'User berhasil ditambahkan');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating user: ' . $e->getMessage());

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Gagal menambahkan user: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        $this->authorize('view-users');

        $user->load(['roles.permissions', 'permissions']);
        return view('admin.users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        $this->authorize('edit-users');

        $roles = Role::all();
        $userRoles = $user->roles->pluck('id')->toArray();

        return view('admin.users.edit', compact('user', 'roles', 'userRoles'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $this->authorize('edit-users');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => ['required', 'string', 'max:255', Rule::unique('users')->ignore($user->id)],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'phone' => 'nullable|string|max:15',
            'password' => 'nullable|string|min:8|confirmed',
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,id',
        ], [
            'name.required' => 'Nama wajib diisi',
            'username.required' => 'Username wajib diisi',
            'username.unique' => 'Username sudah digunakan',
            'email.required' => 'Email wajib diisi',
            'email.email' => 'Format email tidak valid',
            'email.unique' => 'Email sudah digunakan',
            'password.min' => 'Password minimal 8 karakter',
            'password.confirmed' => 'Konfirmasi password tidak cocok',
        ]);

        DB::beginTransaction();
        try {
            $updateData = [
                'name' => $validated['name'],
                'username' => $validated['username'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
            ];

            // Update password jika diisi
            if (!empty($validated['password'])) {
                $updateData['password'] = Hash::make($validated['password']);
                $updateData['is_first_login'] = true;
                $updateData['password_reset_at'] = now();
                $updateData['password_reset_by'] = auth()->user()?->name;
            }

            $user->update($updateData);

            if (!empty($validated['password'])) {
                $user->readable_password = $validated['password'];
                $user->save();
            }

            // Sync roles
            if (isset($validated['roles'])) {
                $roles = Role::whereIn('id', $validated['roles'])->get();
                $user->syncRoles($roles);
            } else {
                $user->syncRoles([]);
            }

            DB::commit();

            return redirect()
                ->route('admin.users.index')
                ->with('success', 'User berhasil diperbarui');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating user: ' . $e->getMessage());

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Gagal memperbarui user: ' . $e->getMessage());
        }
    }

    /**
     * Reset password user umum/GTK ke username.
     */
    public function resetPassword(User $user)
    {
        $this->authorize('edit-users');

        if ($user->isSiswa()) {
            return response()->json([
                'success' => false,
                'message' => 'Reset password siswa dilakukan dari menu Data Siswa agar mengikuti NISN.',
            ], 422);
        }

        try {
            $defaultPassword = $user->username;

            $user->password = Hash::make($defaultPassword);
            $user->is_first_login = true;
            $user->password_reset_at = now();
            $user->password_reset_by = auth()->user()?->name;
            $user->readable_password = $defaultPassword;
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Password user berhasil direset ke username.',
                'default_password' => $defaultPassword,
            ]);
        } catch (\Exception $e) {
            Log::error('Error resetting user password: ' . $e->getMessage(), [
                'user_id' => $user->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal reset password: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $this->authorize('delete-users');

        // Prevent deleting own account
        if ($user->id === auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak dapat menghapus akun sendiri'
            ], 403);
        }

        // Protect Super Admin accounts
        if ($user->hasRole('Super Admin')) {
            // Check if this is the last Super Admin
            $superAdminCount = User::role('Super Admin')->count();
            if ($superAdminCount <= 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak dapat menghapus Super Admin terakhir. Sistem harus memiliki minimal satu Super Admin.'
                ], 403);
            }
            
            // Only another Super Admin can delete a Super Admin
            if (!auth()->user()->hasRole('Super Admin')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Hanya Super Admin yang dapat menghapus akun Super Admin lainnya.'
                ], 403);
            }
        }

        DB::beginTransaction();
        try {
            // Remove all roles and permissions
            $user->syncRoles([]);
            $user->syncPermissions([]);

            $user->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'User berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting user: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus user: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show assign role form (return JSON for AJAX)
     */
    public function assignRoleForm(User $user)
    {
        $this->authorize('assign-roles');

        $roles = Role::withCount('permissions')->get();
        $permissions = Permission::all()->groupBy(function ($permission) {
            $parts = explode('-', $permission->name);
            return count($parts) > 1 ? $parts[1] : 'other';
        });

        $userRoles = $user->roles->pluck('id')->toArray();
        
        // Get DIRECT permissions only (not from roles)
        $userDirectPermissions = $user->permissions->pluck('id')->toArray();
        
        // Get ALL permissions (from roles + direct) - for display purposes
        $userAllPermissions = $user->getAllPermissions()->pluck('id')->toArray();

        // Get tugas tambahan for this user
        $tugasTambahan = TugasTambahan::with('role')
            ->where('user_id', $user->id)
            ->orderBy('is_active', 'desc')
            ->orderBy('mulai_tugas', 'desc')
            ->get();

        // Get roles that can be assigned as tugas tambahan
        // Exclude base roles: Super Admin, GTK, Siswa
        $tugasTambahanRoles = Role::whereNotIn('name', ['Super Admin', 'GTK', 'Siswa', 'Guru', 'Staff TU', 'Kepala Madrasah', 'WAKA'])
            ->orderBy('name')
            ->get();

        return response()->json([
            'roles' => $roles,
            'permissions' => $permissions,
            'userRoles' => $userRoles,
            'userPermissions' => $userDirectPermissions, // Direct permissions (editable)
            'userAllPermissions' => $userAllPermissions, // All permissions (for display/readonly)
            'tugasTambahan' => $tugasTambahan,
            'tugasTambahanRoles' => $tugasTambahanRoles->map(function($role) {
                return [
                    'id' => $role->id,
                    'name' => $role->name,
                ];
            })->values()->all(), // Explicitly convert to simple array
        ]);
    }

    /**
     * Assign roles and permissions to user
     */
    public function assignRole(Request $request, User $user)
    {
        $this->authorize('assign-roles');

        $validated = $request->validate([
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,id',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        DB::beginTransaction();
        try {
            // Sync roles - convert IDs to Role objects
            if (isset($validated['roles']) && !empty($validated['roles'])) {
                $roles = Role::whereIn('id', $validated['roles'])->get();
                $user->syncRoles($roles);
            } else {
                $user->syncRoles([]);
            }

            // Sync direct permissions - convert IDs to Permission objects
            if (isset($validated['permissions']) && !empty($validated['permissions'])) {
                $permissions = Permission::whereIn('id', $validated['permissions'])->get();
                $user->syncPermissions($permissions);
            } else {
                $user->syncPermissions([]);
            }

            DB::commit();

            // Return JSON response for AJAX
            return response()->json([
                'success' => true,
                'message' => 'Role dan permission berhasil diassign ke ' . $user->name
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error assigning role: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal assign role: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle user active status
     */
    public function toggleStatus(User $user)
    {
        $this->authorize('edit-users');

        if ($user->gtk()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Status akun GTK harus diubah melalui modul Mutasi & Status GTK agar alasan dan dampak operasional tercatat.',
            ], 422);
        }

        try {
            $user->is_active = !$user->is_active;
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Status user berhasil diubah',
                'is_active' => $user->is_active
            ]);
        } catch (\Exception $e) {
            Log::error('Error toggling user status: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengubah status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show enhanced permission matrix with editable checkboxes
     */
    public function permissionMatrix()
    {
        $this->authorize('view-permission');

        $permissionService = new PermissionSyncService();
        
        // Get all roles with user count
        $roles = Role::withCount('users')->get();
        
        // Get permissions grouped by module from service
        $moduleDefinitions = $permissionService->getModuleDefinitions();
        
        // Build permission matrix data
        $permissionMatrix = $permissionService->getRolePermissionMatrix();
        
        // Get all registered permissions
        $allPermissions = Permission::all()->pluck('name')->toArray();
        
        // Get unregistered permissions (for scan feature)
        $unregisteredPermissions = $permissionService->getUnregisteredPermissions();
        
        $totalUsers = User::count();
        $totalPermissions = Permission::count();

        return view('admin.users.permission-matrix', compact(
            'roles',
            'moduleDefinitions',
            'permissionMatrix',
            'allPermissions',
            'unregisteredPermissions',
            'totalUsers',
            'totalPermissions'
        ));
    }

    /**
     * Update permission matrix via AJAX
     */
    public function updatePermissionMatrix(Request $request)
    {
        $this->authorize('manage-permission');

        $validated = $request->validate([
            'changes' => 'required|array',
            'changes.*.role_id' => 'required|integer|exists:roles,id',
            'changes.*.permission' => 'required|string',
            'changes.*.action' => 'required|in:grant,revoke',
        ]);

        DB::beginTransaction();
        try {
            $grantCount = 0;
            $revokeCount = 0;

            foreach ($validated['changes'] as $change) {
                $role = Role::find($change['role_id']);
                
                // Protect Super Admin role
                if ($role->name === 'Super Admin') {
                    continue; // Skip changes to Super Admin
                }

                $permission = Permission::where('name', $change['permission'])->first();
                
                if (!$permission) {
                    continue; // Skip if permission doesn't exist
                }

                if ($change['action'] === 'grant') {
                    if (!$role->hasPermissionTo($permission)) {
                        $role->givePermissionTo($permission);
                        $grantCount++;
                    }
                } else {
                    if ($role->hasPermissionTo($permission)) {
                        $role->revokePermissionTo($permission);
                        $revokeCount++;
                    }
                }
            }

            DB::commit();

            // Clear permission cache
            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

            return response()->json([
                'success' => true,
                'message' => "Berhasil update permission: {$grantCount} diberikan, {$revokeCount} dicabut",
                'granted' => $grantCount,
                'revoked' => $revokeCount
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating permission matrix: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal update permission: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Scan for unregistered permissions
     */
    public function scanPermissions()
    {
        $this->authorize('manage-permission');

        $permissionService = new PermissionSyncService();
        
        try {
            $audit = $permissionService->scanPermissionAudit();
            
            return response()->json([
                'success' => true,
                'unregistered' => $audit['unregistered'],
                'uncatalogued' => $audit['uncatalogued'],
                'shared_menu_permissions' => $audit['shared_menu_permissions'],
                'from_routes' => $audit['route_total'],
                'from_menu' => $audit['menu_total'],
                'catalog_total' => $audit['catalog_total'],
                'registered_total' => $audit['registered_total'],
                'total' => count($audit['unregistered'])
            ]);

        } catch (\Exception $e) {
            Log::error('Error scanning permissions: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal scan permission: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Sync/register all permissions from module definitions
     */
    public function syncPermissions()
    {
        $this->authorize('manage-permission');

        $permissionService = new PermissionSyncService();
        
        DB::beginTransaction();
        try {
            $result = $permissionService->syncPermissionsFromModules();
            
            DB::commit();

            // Clear permission cache
            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

            return response()->json([
                'success' => true,
                'message' => "Berhasil sync permission: {$result['created']} baru, {$result['existing']} sudah ada",
                'created' => $result['created'],
                'existing' => $result['existing']
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error syncing permissions: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal sync permission: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk update role permissions (select all / clear all for a role)
     */
    public function bulkUpdateRolePermissions(Request $request)
    {
        $this->authorize('manage-permission');

        $validated = $request->validate([
            'role_id' => 'required|integer|exists:roles,id',
            'action' => 'required|in:grant_all,revoke_all',
            'module' => 'nullable|string',
        ]);

        $role = Role::find($validated['role_id']);
        
        // Protect Super Admin role
        if ($role->name === 'Super Admin') {
            return response()->json([
                'success' => false,
                'message' => 'Role Super Admin tidak dapat diubah'
            ], 403);
        }

        DB::beginTransaction();
        try {
            $permissionService = new PermissionSyncService();
            $moduleDefinitions = $permissionService->getModuleDefinitions();
            
            $permissions = [];
            
            if (!empty($validated['module'])) {
                // Only for specific module
                if (isset($moduleDefinitions[$validated['module']])) {
                    $permissions = $moduleDefinitions[$validated['module']]['permissions'];
                }
            } else {
                // All permissions from all modules
                foreach ($moduleDefinitions as $module) {
                    $permissions = array_merge($permissions, $module['permissions']);
                }
            }

            $count = 0;
            foreach ($permissions as $permName) {
                $permission = Permission::where('name', $permName)->first();
                if (!$permission) continue;

                if ($validated['action'] === 'grant_all') {
                    if (!$role->hasPermissionTo($permission)) {
                        $role->givePermissionTo($permission);
                        $count++;
                    }
                } else {
                    if ($role->hasPermissionTo($permission)) {
                        $role->revokePermissionTo($permission);
                        $count++;
                    }
                }
            }

            DB::commit();

            // Clear permission cache
            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

            $actionText = $validated['action'] === 'grant_all' ? 'diberikan' : 'dicabut';
            
            return response()->json([
                'success' => true,
                'message' => "{$count} permission {$actionText} untuk role {$role->name}",
                'count' => $count
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error bulk updating role permissions: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal update permission: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a new role
     */
    public function storeRole(Request $request)
    {
        $this->authorize('create-role');

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'description' => 'nullable|string|max:500',
        ]);

        DB::beginTransaction();
        try {
            $role = Role::create([
                'name' => $validated['name'],
                'guard_name' => 'web',
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Role '{$role->name}' berhasil dibuat",
                'role' => [
                    'id' => $role->id,
                    'name' => $role->name
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating role: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat role: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a role (with protection)
     */
    public function destroyRole(Role $role)
    {
        $this->authorize('delete-role');

        // Protect system roles
        $protectedRoles = ['Super Admin', 'Admin', 'GTK', 'Siswa', 'Kepala Madrasah', 'Wali Kelas'];
        
        if (in_array($role->name, $protectedRoles)) {
            return response()->json([
                'success' => false,
                'message' => "Role '{$role->name}' adalah role sistem dan tidak dapat dihapus"
            ], 403);
        }

        // Check if role has users
        if ($role->users()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => "Role '{$role->name}' masih digunakan oleh " . $role->users()->count() . " user"
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Revoke all permissions first
            $role->syncPermissions([]);
            
            $roleName = $role->name;
            $role->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Role '{$roleName}' berhasil dihapus"
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting role: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus role: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Assign tugas tambahan to user
     */
    public function assignTugasTambahan(Request $request, User $user)
    {
        $this->authorize('edit-users');

        $validated = $request->validate([
            'role_id' => 'required|exists:roles,id',
            'mulai_tugas' => 'required|date',
            'selesai_tugas' => 'nullable|date|after:mulai_tugas',
            'sk_number' => 'nullable|string|max:100',
            'sk_date' => 'nullable|date',
            'keterangan' => 'nullable|string|max:500',
        ]);

        DB::beginTransaction();
        try {
            // Get role name
            $role = Role::findOrFail($validated['role_id']);

            if (in_array($role->name, ['Kepala Madrasah', 'WAKA'], true)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Penugasan Kepala Madrasah dan WAKA dikelola melalui modul Penugasan GTK.',
                ], 422);
            }

            // Check if user has GTK role (only GTK can have tugas tambahan)
            if (!$user->hasRole('GTK')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tugas tambahan hanya dapat diberikan kepada user dengan role GTK'
                ], 422);
            }

            // Check if Kepala Madrasah - only 1 active allowed
            if ($role->name === 'Kepala Madrasah') {
                $existingKepala = TugasTambahan::where('role_id', $role->id)
                    ->where('is_active', true)
                    ->where('user_id', '!=', $user->id)
                    ->exists();

                if ($existingKepala) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Sudah ada Kepala Madrasah aktif. Nonaktifkan yang lama terlebih dahulu.'
                    ], 422);
                }
            }

            // Create tugas tambahan record
            $tugasTambahan = TugasTambahan::create([
                'user_id' => $user->id,
                'role_id' => $validated['role_id'],
                'mulai_tugas' => $validated['mulai_tugas'],
                'selesai_tugas' => $validated['selesai_tugas'] ?? null,
                'sk_number' => $validated['sk_number'] ?? null,
                'sk_date' => $validated['sk_date'] ?? null,
                'keterangan' => $validated['keterangan'] ?? null,
                'is_active' => true,
                'created_by' => auth()->id(),
            ]);

            // Assign role to user (via Spatie)
            $user->assignRole($role);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Tugas tambahan berhasil ditambahkan',
                'data' => $tugasTambahan->load('role')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error assigning tugas tambahan: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan tugas tambahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Deactivate tugas tambahan
     */
    public function deactivateTugasTambahan(TugasTambahan $tugasTambahan)
    {
        $this->authorize('edit-users');

        DB::beginTransaction();
        try {
            $tugasTambahan->update([
                'is_active' => false,
                'updated_by' => auth()->id(),
            ]);

            // Remove role from user if no other active tugas tambahan with same role
            $hasOtherActiveRole = TugasTambahan::where('user_id', $tugasTambahan->user_id)
                ->where('role_id', $tugasTambahan->role_id)
                ->where('id', '!=', $tugasTambahan->id)
                ->where('is_active', true)
                ->exists();

            if (!$hasOtherActiveRole) {
                $tugasTambahan->user->removeRole($tugasTambahan->role);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Tugas tambahan berhasil dinonaktifkan'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deactivating tugas tambahan: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal menonaktifkan tugas tambahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Activate tugas tambahan
     */
    public function activateTugasTambahan(TugasTambahan $tugasTambahan)
    {
        $this->authorize('edit-users');

        DB::beginTransaction();
        try {
            // Check if Kepala Madrasah - only 1 active allowed
            $role = $tugasTambahan->role;
            if ($role->name === 'Kepala Madrasah') {
                $existingKepala = TugasTambahan::where('role_id', $role->id)
                    ->where('is_active', true)
                    ->where('id', '!=', $tugasTambahan->id)
                    ->exists();

                if ($existingKepala) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Sudah ada Kepala Madrasah aktif. Nonaktifkan yang lama terlebih dahulu.'
                    ], 422);
                }
            }

            $tugasTambahan->update([
                'is_active' => true,
                'updated_by' => auth()->id(),
            ]);

            // Assign role to user
            $tugasTambahan->user->assignRole($role);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Tugas tambahan berhasil diaktifkan'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error activating tugas tambahan: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengaktifkan tugas tambahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete tugas tambahan
     */
    public function deleteTugasTambahan(TugasTambahan $tugasTambahan)
    {
        $this->authorize('edit-users');

        DB::beginTransaction();
        try {
            // Remove role from user if no other tugas tambahan with same role
            $hasOtherRole = TugasTambahan::where('user_id', $tugasTambahan->user_id)
                ->where('role_id', $tugasTambahan->role_id)
                ->where('id', '!=', $tugasTambahan->id)
                ->exists();

            if (!$hasOtherRole) {
                $tugasTambahan->user->removeRole($tugasTambahan->role);
            }

            $tugasTambahan->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Tugas tambahan berhasil dihapus'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting tugas tambahan: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus tugas tambahan: ' . $e->getMessage()
            ], 500);
        }
    }
}
