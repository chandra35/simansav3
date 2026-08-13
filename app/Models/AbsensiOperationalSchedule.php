<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class AbsensiOperationalSchedule extends Model
{
    protected $fillable = [
        'user_type', 'day_of_week', 'is_active', 'check_in_open', 'on_time_until',
        'check_in_close', 'check_out_open', 'check_out_close',
    ];

    protected $casts = [
        'day_of_week' => 'integer',
        'is_active' => 'boolean',
    ];

    public $incrementing = false;

    protected $keyType = 'string';

    protected static function booted(): void
    {
        static::creating(function (self $schedule) {
            $schedule->id ??= (string) Str::uuid();
        });
    }

    public function getDayLabelAttribute(): string
    {
        return [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'][$this->day_of_week] ?? '-';
    }

    public function shortTime(string $attribute): string
    {
        return substr((string) $this->{$attribute}, 0, 5);
    }
}
