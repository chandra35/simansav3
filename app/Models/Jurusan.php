<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Jurusan extends Model
{
    use SoftDeletes, HasUuids;

    protected $table = 'jurusan';

    protected $fillable = [
        'kurikulum_id',
        'kode_jurusan',
        'nama_jurusan',
        'singkatan',
        'deskripsi',
        'urutan',
        'is_active',
    ];

    protected $casts = [
        'urutan' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Relationship: Jurusan belongs to Kurikulum
     */
    public function kurikulum(): BelongsTo
    {
        return $this->belongsTo(Kurikulum::class);
    }

    /**
     * Relationship: Jurusan memiliki banyak Kelas
     */
    public function kelas(): HasMany
    {
        return $this->hasMany(Kelas::class);
    }

    /**
     * Relationship: Jurusan memiliki banyak Siswa (yang memilih jurusan ini)
     */
    public function siswas(): HasMany
    {
        return $this->hasMany(Siswa::class, 'jurusan_pilihan_id');
    }

    /**
     * Scope: Filter by kurikulum
     */
    public function scopeByKurikulum($query, $kurikulumId)
    {
        return $query->where('kurikulum_id', $kurikulumId);
    }

    /**
     * Scope: Filter jurusan aktif
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Helper: Check if jurusan is active
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }

    /**
     * Helper: Get full name with kurikulum
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->nama_jurusan} ({$this->kurikulum->kode})";
    }

    /**
     * Helper: Get badge color
     */
    public function getBadgeColorAttribute(): string
    {
        $colors = [
            'IPA' => 'primary',
            'IPS' => 'success',
            'BAHASA' => 'warning',
            'KEAGAMAAN' => 'info',
            'UMUM' => 'secondary',
        ];

        return $colors[$this->kode_jurusan] ?? 'secondary';
    }
}
