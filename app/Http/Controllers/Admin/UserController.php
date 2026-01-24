<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\TugasTambahan;
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
        // $this->authorize('view-user'); // Uncomment after testing

        // Get statistics
        $stats = [
            'total_users' => User::count(),
            'admin' => User::role(['Super Admin', 'Admin'])->count(),
            'gtk' => User::whereHas('roles', function($q) {
                $q->whereIn('name', ['GTK', 'Wali Kelas', 'Kepala Sekolah', 'Wakil Kepala Sekolah']);
            })->count(),
            'siswa' => User::role('Siswa')->count(),
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
        $users = User::with('roles')->select('users.*');

        // Filter by Role
        if ($request->filled('role')) {
            $users->whereHas('roles', function($q) use ($request) {
                $q->where('name', $request->role);
            });
        }

        // Search functionality
        if ($request->has('search') && $request->search['value']) {
            $search = $request->search['value'];
            $users->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $totalRecords = User::count();
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
            $columns = ['id', 'name', 'username', 'email', 'phone'];
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
                return "<span class='badge badge-{$color}'>{$role->name}</span>";
            })->implode(' ');

            // Generate status toggle
            $checked = $user->is_active ? 'checked' : '';
            $statusHtml = "<div class='custom-control custom-switch' style='padding-left: 2.25rem;'>
                <input type='checkbox' class='custom-control-input toggle-status' id='status{$user->id}' data-id='{$user->id}' {$checked}>
                <label class='custom-control-label' for='status{$user->id}'></label>
            </div>";

            // Generate action buttons with button group
            $actions = "<div class='btn-group' role='group'>";
            $actions .= "<button class='btn btn-sm btn-warning btn-assign-role' data-id='{$user->id}' data-name='{$user->name}' title='Assign Role'>
                            <i class='fas fa-user-tag'></i>
                        </button>";
            $actions .= "<a href='" . route('admin.users.edit', $user->id) . "' class='btn btn-sm btn-primary' title='Edit'>
                            <i class='fas fa-edit'></i>
                        </a>";
            
            if ($user->id !== auth()->id()) {
                $actions .= "<button class='btn btn-sm btn-danger btn-delete' data-id='{$user->id}' data-name='{$user->name}' title='Hapus'>
                                <i class='fas fa-trash'></i>
                            </button>";
            }
            $actions .= "</div>";

            return [
                'DT_RowIndex' => $request->start + $index + 1,
                'name' => $user->name,
                'username' => $user->username,
                'email' => $user->email,
                'phone' => $user->phone ?? '-',
                'roles' => $rolesBadges ?: '<span class="badge badge-secondary">No Role</span>',
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
    }    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create-user');

        $roles = Role::all();
        return view('admin.users.create', compact('roles'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create-user');

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

            // Assign roles jika ada
            if (!empty($validated['roles'])) {
                $user->syncRoles($validated['roles']);
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
        $this->authorize('view-user');

        $user->load(['roles.permissions', 'permissions']);
        return view('admin.users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        $this->authorize('edit-user');

        $roles = Role::all();
        $userRoles = $user->roles->pluck('id')->toArray();

        return view('admin.users.edit', compact('user', 'roles', 'userRoles'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $this->authorize('edit-user');

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
            }

            $user->update($updateData);

            // Sync roles
            if (isset($validated['roles'])) {
                $user->syncRoles($validated['roles']);
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
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $this->authorize('delete-user');

        // Prevent deleting own account
        if ($user->id === auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak dapat menghapus akun sendiri'
            ], 403);
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
        $this->authorize('assign-role');

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
        $tugasTambahanRoles = Role::whereNotIn('name', ['Super Admin', 'GTK', 'Siswa', 'Guru', 'Staff TU'])
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
        $this->authorize('assign-role');

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
        $this->authorize('edit-user');

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
     * Show permission matrix
     */
    public function permissionMatrix()
    {
        $this->authorize('view-permission');

        $roles = Role::withCount(['users', 'permissions'])->get();
        $permissions = Permission::all();
        
        $permissionsByModule = $permissions->groupBy(function ($permission) {
            $parts = explode('-', $permission->name);
            return count($parts) > 1 ? $parts[1] : 'other';
        });

        $totalUsers = User::count();

        return view('admin.users.permission-matrix', compact(
            'roles',
            'permissions',
            'permissionsByModule',
            'totalUsers'
        ));
    }

    /**
     * Assign tugas tambahan to user
     */
    public function assignTugasTambahan(Request $request, User $user)
    {
        $this->authorize('assign-role');

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
        $this->authorize('assign-role');

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
        $this->authorize('assign-role');

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
        $this->authorize('assign-role');

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
