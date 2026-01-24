<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Kelas extends Model
{
    use SoftDeletes, HasUuids;

    protected $table = 'kelas';

    protected $fillable = [
        'tahun_pelajaran_id',
        'kurikulum_id',
        'jurusan_id',
        'nama_kelas',
        'tingkat',
        'kode_kelas',
        'wali_kelas_id',
        'kapasitas',
        'ruang_kelas',
        'deskripsi',
        'is_active',
    ];

    protected $casts = [
        'tingkat' => 'integer',
        'kapasitas' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'id';
    }

    /**
     * Relationship: Kelas belongs to Tahun Pelajaran
     */
    public function tahunPelajaran(): BelongsTo
    {
        return $this->belongsTo(TahunPelajaran::class);
    }

    /**
     * Relationship: Kelas belongs to Kurikulum
     */
    public function kurikulum(): BelongsTo
    {
        return $this->belongsTo(Kurikulum::class);
    }

    /**
     * Relationship: Kelas belongs to Jurusan (nullable)
     */
    public function jurusan(): BelongsTo
    {
        return $this->belongsTo(Jurusan::class);
    }

    /**
     * Relationship: Kelas belongs to Wali Kelas (User)
     */
    public function waliKelas(): BelongsTo
    {
        return $this->belongsTo(User::class, 'wali_kelas_id');
    }

    /**
     * Relationship: Kelas has many Siswa (through pivot siswa_kelas)
     */
    public function siswas(): BelongsToMany
    {
        return $this->belongsToMany(Siswa::class, 'siswa_kelas', 'kelas_id', 'siswa_id')
                    ->withPivot(['tahun_pelajaran_id', 'tanggal_masuk', 'tanggal_keluar', 'status', 'nomor_urut_absen', 'catatan_perpindahan'])
                    ->whereNull('siswa_kelas.deleted_at')
                    ->withTimestamps();
    }

    /**
     * Relationship: Siswa aktif di kelas ini
     */
    public function siswaAktif(): BelongsToMany
    {
        return $this->belongsToMany(Siswa::class, 'siswa_kelas', 'kelas_id', 'siswa_id')
                    ->withPivot(['tahun_pelajaran_id', 'tanggal_masuk', 'tanggal_keluar', 'status', 'nomor_urut_absen', 'catatan_perpindahan'])
                    ->whereNull('siswa_kelas.deleted_at')
                    ->where('siswa_kelas.status', 'aktif')
                    ->withTimestamps();
    }

    /**
     * Relationship: Kelas has many SiswaKelas (pivot records)
     */
    public function siswaKelas()
    {
        return $this->hasMany(SiswaKelas::class, 'kelas_id');
    }

    /**
     * Scope: Filter by tingkat (10, 11, 12)
     */
    public function scopeByTingkat($query, $tingkat)
    {
        return $query->where('tingkat', $tingkat);
    }

    /**
     * Scope: Filter by jurusan
     */
    public function scopeByJurusan($query, $jurusanId)
    {
        return $query->where('jurusan_id', $jurusanId);
    }

    /**
     * Scope: Filter by kurikulum
     */
    public function scopeByKurikulum($query, $kurikulumId)
    {
        return $query->where('kurikulum_id', $kurikulumId);
    }

    /**
     * Scope: Filter by tahun pelajaran
     */
    public function scopeByTahunPelajaran($query, $tahunPelajaranId)
    {
        return $query->where('tahun_pelajaran_id', $tahunPelajaranId);
    }

    /**
     * Scope: Kelas aktif
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Helper: Get nama kelas lengkap dengan jurusan (jika ada)
     */
    public function getNamaLengkapAttribute(): ?string
    {
        if (!$this->nama_kelas) {
            return null;
        }
        
        if ($this->jurusan) {
            return "{$this->nama_kelas} ({$this->jurusan->singkatan})";
        }
        return $this->nama_kelas;
    }

    /**
     * Helper: Get jumlah siswa aktif
     */
    public function getJumlahSiswaAttribute(): int
    {
        return $this->siswaAktif()->count();
    }

    /**
     * Helper: Get sisa tempat/kursi
     */
    public function getSisaTempatAttribute(): int
    {
        return max(0, $this->kapasitas - $this->jumlah_siswa);
    }

    /**
     * Helper: Check if kelas is full
     */
    public function isFull(): bool
    {
        return $this->jumlah_siswa >= $this->kapasitas;
    }

    /**
     * Helper: Get percentage filled
     */
    public function getPercentageFilledAttribute(): float
    {
        if ($this->kapasitas == 0) return 0;
        return round(($this->jumlah_siswa / $this->kapasitas) * 100, 1);
    }

    /**
     * Helper: Get tingkat text (X, XI, XII)
     */
    public function getTingkatRomawi(): string
    {
        return match($this->tingkat) {
            10 => 'X',
            11 => 'XI',
            12 => 'XII',
            default => (string)$this->tingkat,
        };
    }

    /**
     * Helper: Get badge color based on capacity
     */
    public function getCapacityBadgeColorAttribute(): string
    {
        $percentage = $this->percentage_filled;
        
        if ($percentage >= 100) return 'danger';
        if ($percentage >= 90) return 'warning';
        if ($percentage >= 70) return 'info';
        return 'success';
    }

    /**
     * Helper: Generate kode kelas otomatis
     * Format: X-IPA-1-2024
     */
    public static function generateKodeKelas($tingkat, $jurusanKode, $nomor, $tahun): string
    {
        $tingkatRomawi = match($tingkat) {
            10 => 'X',
            11 => 'XI',
            12 => 'XII',
            default => (string)$tingkat,
        };

        return "{$tingkatRomawi}-{$jurusanKode}-{$nomor}-{$tahun}";
    }
}
