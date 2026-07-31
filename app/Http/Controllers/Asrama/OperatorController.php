<?php

namespace App\Http\Controllers\Asrama;

use App\Http\Controllers\Controller;
use App\Models\Gtk;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class OperatorController extends Controller
{
    public function index()
    {
        return view('asrama.operator.index', [
            'operators' => User::role('Operator Asrama')->with('gtk')->orderBy('name')->get(),
            'gtks' => Gtk::with('user')->whereNotNull('user_id')->whereHas('user', fn ($query) => $query->where('is_active', true))
                ->orderBy('nama_lengkap')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate(['gtk_id' => ['required', 'exists:gtks,id']]);
        $gtk = Gtk::with('user')->whereNotNull('user_id')->findOrFail($data['gtk_id']);
        abort_unless($gtk->user?->is_active, 422, 'GTK belum memiliki akun aktif.');
        Role::findOrCreate('Operator Asrama', 'web');
        $gtk->user->assignRole('Operator Asrama');
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return back()->with('success', $gtk->nama_lengkap.' ditetapkan sebagai Operator Asrama.');
    }

    public function destroy(User $user)
    {
        abort_unless($user->hasRole('Operator Asrama'), 404);
        $user->removeRole('Operator Asrama');
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return back()->with('success', 'Tugas Operator Asrama dicabut dari '.$user->name.'.');
    }
}
