<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TkaPeserta extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'tka_pesertas';

    protected $fillable = [
        'tahun',
        'siswa_id',
        'nisn',
        'nama_peserta',
        'tempat_tanggal_lahir',
        'username',
        'password',
        'id_proktor',
        'ruang',
        'gelombang',
        'sesi',
        'jadwal',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'jadwal' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Siswa relation
     */
    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }

    /**
     * Scope filter by year
     */
    public function scopeTahun($query, $tahun)
    {
        if ($tahun) {
            return $query->where('tahun', $tahun);
        }
        return $query;
    }

    /**
     * Scope filter active
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope filter ruang
     */
    public function scopeRuang($query, $ruang)
    {
        if ($ruang) {
            return $query->where('ruang', $ruang);
        }
        return $query;
    }

    /**
     * Scope filter gelombang
     */
    public function scopeGelombang($query, $gelombang)
    {
        if ($gelombang) {
            return $query->where('gelombang', $gelombang);
        }
        return $query;
    }

    /**
     * Scope filter sesi
     */
    public function scopeSesi($query, $sesi)
    {
        if ($sesi) {
            return $query->where('sesi', $sesi);
        }
        return $query;
    }

    /**
     * Get list of unique available years
     */
    public static function getAvailableYears(): array
    {
        return static::select('tahun')
            ->distinct()
            ->orderBy('tahun', 'desc')
            ->pluck('tahun')
            ->toArray();
    }
}
