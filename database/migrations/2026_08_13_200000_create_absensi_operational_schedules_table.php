<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('absensi_operational_schedules', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('user_type', 10);
            $table->unsignedTinyInteger('day_of_week');
            $table->boolean('is_active')->default(true);
            $table->time('check_in_open')->default('06:00');
            $table->time('on_time_until')->default('07:00');
            $table->time('check_in_close')->default('08:00');
            $table->time('check_out_open')->default('15:00');
            $table->time('check_out_close')->default('23:59');
            $table->timestamps();

            $table->unique(['user_type', 'day_of_week'], 'attendance_schedule_type_day_unique');
            $table->index(['day_of_week', 'is_active'], 'attendance_schedule_day_active_index');
        });

        $now = now();
        foreach (['gtk', 'siswa'] as $type) {
            foreach (range(1, 7) as $day) {
                $checkout = match ($day) {
                    1 => '15:00:00',
                    2, 3, 4 => '16:30:00',
                    5 => '14:30:00',
                    default => '15:00:00',
                };
                DB::table('absensi_operational_schedules')->insert([
                    'id' => (string) Str::uuid(),
                    'user_type' => $type,
                    'day_of_week' => $day,
                    'is_active' => $day <= 5,
                    'check_in_open' => '06:00:00',
                    'on_time_until' => '07:00:00',
                    'check_in_close' => '08:00:00',
                    'check_out_open' => $checkout,
                    'check_out_close' => '23:59:00',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('absensi_operational_schedules');
    }
};
