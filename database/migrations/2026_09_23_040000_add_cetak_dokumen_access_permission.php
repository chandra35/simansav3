<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;

return new class extends Migration
{
    public function up(): void
    {
        Permission::firstOrCreate(
            ['name' => 'access-cetak-dokumen', 'guard_name' => 'web'],
        );
    }

    public function down(): void
    {
        Permission::where('name', 'access-cetak-dokumen')
            ->where('guard_name', 'web')
            ->delete();
    }
};
