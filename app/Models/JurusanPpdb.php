<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JurusanPpdb extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'jurusan_ppdb';

    protected $fillable = [
        'kode',
        'nama',
        'deskripsi',
        'kuota',
        'terisi',
        'is_active',
        'urutan',
    ];

    protected $casts = [
        'kuota' => 'integer',
        'terisi' => 'integer',
        'is_active' => 'boolean',
        'urutan' => 'integer',
    ];

    /**
     * Scope for active jurusan
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for ordered jurusan
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('urutan')->orderBy('nama');
    }

    /**
     * Get sisa kuota
     */
    public function getSisaKuotaAttribute(): int
    {
        return max(0, $this->kuota - $this->terisi);
    }

    /**
     * Get persentase terisi
     */
    public function getPersentaseTerisiAttribute(): float
    {
        if ($this->kuota <= 0) {
            return 0;
        }
        return round(($this->terisi / $this->kuota) * 100, 1);
    }

    /**
     * Check if kuota is full
     */
    public function isKuotaPenuh(): bool
    {
        return $this->terisi >= $this->kuota;
    }

    /**
     * Increment terisi count
     */
    public function incrementTerisi(): void
    {
        $this->increment('terisi');
    }

    /**
     * Decrement terisi count
     */
    public function decrementTerisi(): void
    {
        if ($this->terisi > 0) {
            $this->decrement('terisi');
        }
    }
}
