<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kurikulum extends Model
{
    use SoftDeletes, HasUuids;

    protected $table = 'kurikulum';

    protected $fillable = [
        'kode',
        'nama_kurikulum',
        'deskripsi',
        'tahun_berlaku',
        'has_jurusan',
        'is_active',
    ];

    protected $casts = [
        'tahun_berlaku' => 'integer',
        'has_jurusan' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Relationship: Kurikulum memiliki banyak Jurusan
     */
    public function jurusans(): HasMany
    {
        return $this->hasMany(Jurusan::class);
    }

    /**
     * Relationship: Kurikulum memiliki banyak Tahun Pelajaran
     */
    public function tahunPelajarans(): HasMany
    {
        return $this->hasMany(TahunPelajaran::class);
    }

    /**
     * Relationship: Kurikulum memiliki banyak Kelas
     */
    public function kelas(): HasMany
    {
        return $this->hasMany(Kelas::class);
    }

    /**
     * Scope: Filter kurikulum yang aktif
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Helper: Cek apakah kurikulum memiliki penjurusan
     */
    public function hasJurusan(): bool
    {
        return $this->has_jurusan;
    }

    /**
     * Helper: Get list jurusan untuk kurikulum ini
     */
    public function getJurusanList()
    {
        return $this->jurusans()->where('is_active', true)->orderBy('urutan')->get();
    }

    /**
     * Helper: Format nama kurikulum lengkap
     */
    public function getFormattedNameAttribute(): string
    {
        return "{$this->nama_kurikulum} ({$this->kode})";
    }

    /**
     * Helper: Get badge color berdasarkan status
     */
    public function getBadgeColorAttribute(): string
    {
        return $this->is_active ? 'success' : 'secondary';
    }

    /**
     * Helper: Get status text
     */
    public function getStatusTextAttribute(): string
    {
        return $this->is_active ? 'Aktif' : 'Non-Aktif';
    }
}
