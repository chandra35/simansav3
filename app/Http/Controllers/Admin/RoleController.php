<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    /**
     * Display a listing of roles.
     */
    public function index()
    {
        $roles = Role::with('permissions')
            ->withCount('users')
            ->orderBy('name')
            ->get();
            
        return view('admin.roles.index', compact('roles'));
    }

    /**
     * Show the form for creating a new role.
     */
    public function create()
    {
        $permissions = Permission::orderBy('name')->get()->groupBy(function($permission) {
            $parts = explode('-', $permission->name);
            return $parts[0] ?? 'general';
        });
        
        return view('admin.roles.create', compact('permissions'));
    }

    /**
     * Store a newly created role.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50|unique:roles,name',
            'guard_name' => 'nullable|string|max:50',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        $role = Role::create([
            'name' => $validated['name'],
            'guard_name' => $validated['guard_name'] ?? 'web',
        ]);

        if (!empty($validated['permissions'])) {
            $role->syncPermissions($validated['permissions']);
        }

        ActivityLog::log('create', "Membuat role: {$role->name}", $role);

        return redirect()->route('admin.roles.index')
            ->with('success', 'Role berhasil dibuat');
    }

    /**
     * Display the specified role.
     */
    public function show(Role $role)
    {
        $role->load('users', 'permissions');
        
        // Group permissions by category
        $groupedPermissions = $role->permissions->groupBy(function($permission) {
            $parts = explode('-', $permission->name);
            return $parts[0] ?? 'general';
        });
        
        return view('admin.roles.show', compact('role', 'groupedPermissions'));
    }

    /**
     * Show the form for editing the specified role.
     */
    public function edit(Role $role)
    {
        // Protect system roles
        $systemRoles = ['Super Admin', 'Siswa'];
        if (in_array($role->name, $systemRoles)) {
            return redirect()->route('admin.roles.index')
                ->with('error', 'Role sistem tidak dapat diedit');
        }

        $permissions = Permission::orderBy('name')->get()->groupBy(function($permission) {
            $parts = explode('-', $permission->name);
            return $parts[0] ?? 'general';
        });
        
        $rolePermissions = $role->permissions->pluck('name')->toArray();
        
        return view('admin.roles.edit', compact('role', 'permissions', 'rolePermissions'));
    }

    /**
     * Update the specified role.
     */
    public function update(Request $request, Role $role)
    {
        // Protect system roles
        $systemRoles = ['Super Admin', 'Siswa'];
        if (in_array($role->name, $systemRoles)) {
            return redirect()->route('admin.roles.index')
                ->with('error', 'Role sistem tidak dapat diedit');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:50', Rule::unique('roles', 'name')->ignore($role->id)],
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        $oldName = $role->name;
        $role->update(['name' => $validated['name']]);
        $role->syncPermissions($validated['permissions'] ?? []);

        ActivityLog::log('update', "Mengupdate role: {$oldName} -> {$role->name}", $role);

        return redirect()->route('admin.roles.index')
            ->with('success', 'Role berhasil diupdate');
    }

    /**
     * Remove the specified role.
     */
    public function destroy(Role $role)
    {
        // Protect system roles
        $systemRoles = ['Super Admin', 'Siswa', 'GTK', 'Admin', 'Kepala Madrasah'];
        if (in_array($role->name, $systemRoles)) {
            return redirect()->route('admin.roles.index')
                ->with('error', 'Role sistem tidak dapat dihapus');
        }

        if ($role->users()->count() > 0) {
            return redirect()->route('admin.roles.index')
                ->with('error', 'Role masih digunakan oleh ' . $role->users()->count() . ' user');
        }

        $roleName = $role->name;
        $role->delete();

        ActivityLog::log('delete', "Menghapus role: {$roleName}");

        return redirect()->route('admin.roles.index')
            ->with('success', 'Role berhasil dihapus');
    }

    /**
     * Assign role to user
     */
    public function assignUser(Request $request, Role $role)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $user = User::findOrFail($validated['user_id']);
        $user->assignRole($role);

        ActivityLog::log('update', "Menambahkan user {$user->name} ke role {$role->name}", $role);

        return redirect()->route('admin.roles.show', $role)
            ->with('success', "User {$user->name} berhasil ditambahkan ke role {$role->name}");
    }

    /**
     * Remove user from role
     */
    public function removeUser(Request $request, Role $role)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $user = User::findOrFail($validated['user_id']);
        $user->removeRole($role);

        ActivityLog::log('update', "Menghapus user {$user->name} dari role {$role->name}", $role);

        return redirect()->route('admin.roles.show', $role)
            ->with('success', "User {$user->name} berhasil dihapus dari role {$role->name}");
    }
}
