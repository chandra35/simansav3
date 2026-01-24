<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PermissionController extends Controller
{
    /**
     * Display a listing of permissions.
     */
    public function index()
    {
        $permissions = Permission::orderBy('name')->get();
        
        // Group permissions by category
        $groupedPermissions = $permissions->groupBy(function($permission) {
            $parts = explode('-', $permission->name);
            return $parts[0] ?? 'general';
        });
        
        return view('admin.permissions.index', compact('permissions', 'groupedPermissions'));
    }

    /**
     * Show the form for creating a new permission.
     */
    public function create()
    {
        // Get existing categories for suggestion
        $categories = Permission::all()
            ->map(function($permission) {
                $parts = explode('-', $permission->name);
                return $parts[0] ?? 'general';
            })
            ->unique()
            ->sort()
            ->values();
            
        return view('admin.permissions.create', compact('categories'));
    }

    /**
     * Store a newly created permission.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:permissions,name',
            'guard_name' => 'nullable|string|max:50',
        ]);

        $permission = Permission::create([
            'name' => $validated['name'],
            'guard_name' => $validated['guard_name'] ?? 'web',
        ]);

        ActivityLog::log('create', "Membuat permission: {$permission->name}", $permission);

        return redirect()->route('admin.permissions.index')
            ->with('success', 'Permission berhasil dibuat');
    }

    /**
     * Display the specified permission.
     */
    public function show(Permission $permission)
    {
        $permission->load('roles');
        
        return view('admin.permissions.show', compact('permission'));
    }

    /**
     * Show the form for editing the specified permission.
     */
    public function edit(Permission $permission)
    {
        $categories = Permission::all()
            ->map(function($p) {
                $parts = explode('-', $p->name);
                return $parts[0] ?? 'general';
            })
            ->unique()
            ->sort()
            ->values();
            
        return view('admin.permissions.edit', compact('permission', 'categories'));
    }

    /**
     * Update the specified permission.
     */
    public function update(Request $request, Permission $permission)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('permissions', 'name')->ignore($permission->id)],
        ]);

        $oldName = $permission->name;
        $permission->update(['name' => $validated['name']]);

        ActivityLog::log('update', "Mengupdate permission: {$oldName} -> {$permission->name}", $permission);

        return redirect()->route('admin.permissions.index')
            ->with('success', 'Permission berhasil diupdate');
    }

    /**
     * Remove the specified permission.
     */
    public function destroy(Permission $permission)
    {
        // Check if permission is used by any role
        if ($permission->roles()->count() > 0) {
            return redirect()->route('admin.permissions.index')
                ->with('error', 'Permission masih digunakan oleh ' . $permission->roles()->count() . ' role');
        }

        $permissionName = $permission->name;
        $permission->delete();

        ActivityLog::log('delete', "Menghapus permission: {$permissionName}");

        return redirect()->route('admin.permissions.index')
            ->with('success', 'Permission berhasil dihapus');
    }

    /**
     * Bulk create permissions
     */
    public function bulkCreate(Request $request)
    {
        $validated = $request->validate([
            'permissions' => 'required|string',
        ]);

        $lines = explode("\n", $validated['permissions']);
        $created = 0;
        $skipped = 0;

        foreach ($lines as $line) {
            $name = trim($line);
            if (empty($name)) continue;

            // Check if already exists
            if (Permission::where('name', $name)->exists()) {
                $skipped++;
                continue;
            }

            Permission::create([
                'name' => $name,
                'guard_name' => 'web',
            ]);
            $created++;
        }

        ActivityLog::log('create', "Bulk create permissions: {$created} created, {$skipped} skipped");

        return redirect()->route('admin.permissions.index')
            ->with('success', "{$created} permission berhasil dibuat, {$skipped} sudah ada");
    }
}
