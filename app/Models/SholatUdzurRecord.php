<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SholatUdzurRecord extends Model
{
    use HasUuids;

    protected $table = 'sholat_udzur_records';

    protected $fillable = [
        'siswa_id',
        'tanggal_mulai',
        'tanggal_selesai',
        'keterangan',
        'created_by',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
    ];

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeActiveOnDate($query, $date)
    {
        $targetDate = $date instanceof Carbon ? $date->toDateString() : (string) $date;

        return $query->where('tanggal_mulai', '<=', $targetDate)
            ->where('tanggal_selesai', '>=', $targetDate);
    }

    public static function isSiswaUdzur(string $siswaId, string $date): bool
    {
        return static::query()
            ->where('siswa_id', $siswaId)
            ->activeOnDate($date)
            ->exists();
    }
}
