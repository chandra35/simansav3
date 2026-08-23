<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permission = Permission::firstOrCreate([
            'name' => 'view-gtk-dashboard',
            'guard_name' => 'web',
        ]);

        // Dashboard GTK hanya memuat ruang kerja dan profil akun sendiri.
        // Setiap akun yang benar-benar memiliki profil GTK perlu hak dasar ini,
        // termasuk GTK lama yang hanya memiliki role penugasan seperti BK.
        User::query()
            ->whereHas('gtk')
            ->cursor()
            ->each(fn (User $user) => $user->givePermissionTo($permission));

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        // Permission dashboard dapat juga diberikan secara sadar oleh admin.
        // Jangan mencabutnya secara massal saat rollback agar tidak menghapus
        // akses yang telah diberikan setelah migration ini berjalan.
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
