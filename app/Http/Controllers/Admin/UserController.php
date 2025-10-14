<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
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

        return view('admin.users.index');
    }

    /**
     * Get users data for DataTables (AJAX)
     */
    public function data(Request $request)
    {
        $users = User::with('roles')->select('users.*');

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
        
        // Pagination
        if ($request->has('start') && $request->has('length')) {
            $users->skip($request->start)->take($request->length);
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
            // Generate checkbox for Super Admin only
            $checkbox = '';
            if (auth()->user()->hasRole('Super Admin')) {
                // Don't allow Super Admin to delete themselves
                if ($user->id !== auth()->id()) {
                    $checkbox = "<input type='checkbox' class='user-checkbox' value='{$user->id}'>";
                }
            }

            // Generate roles badges
            $rolesBadges = $user->roles->map(function ($role) {
                $colors = [
                    'Super Admin' => 'danger',
                    'Admin' => 'primary',
                    'Operator' => 'info',
                    'Guru' => 'success',
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

            $result = [
                'DT_RowIndex' => $request->start + $index + 1,
                'name' => $user->name,
                'username' => $user->username,
                'email' => $user->email,
                'phone' => $user->phone ?? '-',
                'roles' => $rolesBadges ?: '<span class="badge badge-secondary">No Role</span>',
                'status' => $statusHtml,
                'action' => $actions
            ];

            // Add checkbox column if Super Admin
            if (auth()->user()->hasRole('Super Admin')) {
                $result = array_merge(['checkbox' => $checkbox], $result);
            }

            return $result;
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
        $userPermissions = $user->permissions->pluck('id')->toArray();

        return response()->json([
            'roles' => $roles,
            'permissions' => $permissions,
            'userRoles' => $userRoles,
            'userPermissions' => $userPermissions
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
            // Sync roles
            if (isset($validated['roles'])) {
                $user->syncRoles($validated['roles']);
            } else {
                $user->syncRoles([]);
            }

            // Sync direct permissions (custom permissions)
            if (isset($validated['permissions'])) {
                $user->syncPermissions($validated['permissions']);
            } else {
                $user->syncPermissions([]);
            }

            DB::commit();

            return redirect()
                ->route('admin.users.index')
                ->with('success', 'Role dan permission berhasil diassign ke ' . $user->name);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error assigning role: ' . $e->getMessage());

            return redirect()
                ->back()
                ->with('error', 'Gagal assign role: ' . $e->getMessage());
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
     * Bulk delete users (Super Admin only)
     */
    public function bulkDelete(Request $request)
    {
        // Only Super Admin can bulk delete
        if (!auth()->user()->hasRole('Super Admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki izin untuk melakukan bulk delete'
            ], 403);
        }

        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:users,id'
        ]);

        try {
            $ids = $request->ids;
            $currentUserId = auth()->id();

            // Remove current user from deletion list
            $ids = array_filter($ids, function($id) use ($currentUserId) {
                return $id != $currentUserId;
            });

            if (empty($ids)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada user yang dapat dihapus'
                ], 400);
            }

            // Delete users
            $deletedCount = User::whereIn('id', $ids)->delete();

            return response()->json([
                'success' => true,
                'message' => "Berhasil menghapus {$deletedCount} user"
            ]);

        } catch (\Exception $e) {
            Log::error('Error bulk deleting users: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus user: ' . $e->getMessage()
            ], 500);
        }
    }
}
