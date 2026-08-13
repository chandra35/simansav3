<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        if (! DB::table('absensi_settings')->where('key', 'face_detect_public_token')->exists()) {
            DB::table('absensi_settings')->insert([
                'id' => (string) Str::uuid(),
                'key' => 'face_detect_public_token',
                'value' => Str::random(64),
                'type' => 'string',
                'group' => 'kiosk',
                'label' => 'Token Publik Face Detect',
                'description' => 'Token rahasia dan dapat dirotasi untuk perangkat Face Detect tanpa login.',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('absensi_settings')->where('key', 'face_detect_public_token')->delete();
    }
};
