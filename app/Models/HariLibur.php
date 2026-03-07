<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class HariLibur extends Model
{
    protected $table = 'hari_liburs';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'tanggal', 'nama', 'jenis', 'keterangan', 'is_recurring',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'is_recurring' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    /**
     * Check apakah tanggal tertentu adalah hari libur
     */
    public static function isHoliday($date): bool
    {
        $date = \Carbon\Carbon::parse($date);

        // Cek weekend (Sabtu & Minggu)
        if ($date->isWeekend()) {
            return true;
        }

        // Cek tanggal merah exact
        $exists = static::where('tanggal', $date->format('Y-m-d'))->exists();
        if ($exists) return true;

        // Cek recurring (tanggal & bulan sama, beda tahun)
        return static::where('is_recurring', true)
            ->whereMonth('tanggal', $date->month)
            ->whereDay('tanggal', $date->day)
            ->exists();
    }

    /**
     * Get holidays in a date range
     */
    public static function getInRange($startDate, $endDate)
    {
        return static::whereBetween('tanggal', [
            \Carbon\Carbon::parse($startDate)->format('Y-m-d'),
            \Carbon\Carbon::parse($endDate)->format('Y-m-d'),
        ])->orderBy('tanggal')->get();
    }

    /**
     * Scope by jenis
     */
    public function scopeJenis($query, $jenis)
    {
        return $query->where('jenis', $jenis);
    }
}
