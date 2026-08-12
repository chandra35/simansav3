<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('jenis_penugasan_gtk') && Schema::hasColumn('jenis_penugasan_gtk', 'wajib_sk')) {
            DB::table('jenis_penugasan_gtk')->update(['wajib_sk' => false]);
        }
    }

    public function down(): void
    {
        // Tidak dikembalikan: persyaratan SK sudah tidak digunakan oleh modul.
    }
};
