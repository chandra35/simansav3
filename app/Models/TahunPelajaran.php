<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class TahunPelajaran extends Model
{
    use SoftDeletes, HasUuids;

    protected $table = 'tahun_pelajaran';

    protected $fillable = [
        'kurikulum_id',
        'nama',
        'tahun_mulai',
        'tahun_selesai',
        'semester_aktif',
        'tanggal_mulai',
        'tanggal_selesai',
        'is_active',
        'status',
        'kuota_ppdb',
        'keterangan',
    ];

    protected $casts = [
        'tahun_mulai' => 'integer',
        'tahun_selesai' => 'integer',
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
        'is_active' => 'boolean',
        'kuota_ppdb' => 'integer',
    ];

    /**
     * Relationship: Tahun Pelajaran belongs to Kurikulum
     */
    public function kurikulum(): BelongsTo
    {
        return $this->belongsTo(Kurikulum::class);
    }

    /**
     * Relationship: Tahun Pelajaran memiliki banyak Kelas
     */
    public function kelas(): HasMany
    {
        return $this->hasMany(Kelas::class);
    }

    /**
     * Relationship: Tahun Pelajaran memiliki banyak Siswa (through pivot)
     */
    public function siswas()
    {
        return $this->belongsToMany(Siswa::class, 'siswa_kelas')
                    ->withPivot(['tanggal_masuk', 'tanggal_keluar', 'status', 'nomor_urut_absen'])
                    ->withTimestamps();
    }

    /**
     * Relationship: Mutasi Masuk
     */
    public function mutasiMasuk(): HasMany
    {
        return $this->hasMany(MutasiSiswa::class)->where('jenis_mutasi', 'masuk');
    }

    /**
     * Relationship: Mutasi Keluar
     */
    public function mutasiKeluar(): HasMany
    {
        return $this->hasMany(MutasiSiswa::class)->where('jenis_mutasi', 'keluar');
    }

    /**
     * Scope: Get tahun pelajaran aktif
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Get by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Helper: Check if this is active year
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }

    /**
     * Helper: Get semester text
     */
    public function getSemesterAttribute(): string
    {
        return $this->semester_aktif;
    }

    /**
     * Helper: Format nama lengkap dengan semester
     */
    public function getFormattedNameAttribute(): string
    {
        return "{$this->nama} - Semester {$this->semester_aktif}";
    }

    /**
     * Helper: Get kuota tersedia (kuota - jumlah siswa aktif)
     */
    public function getKuotaTersediaAttribute(): int
    {
        $jumlahSiswa = $this->siswas()->wherePivot('status', 'aktif')->count();
        return max(0, $this->kuota_ppdb - $jumlahSiswa);
    }

    /**
     * Helper: Get badge color based on status
     */
    public function getBadgeColorAttribute(): string
    {
        return match($this->status) {
            'aktif' => 'success',
            'selesai' => 'secondary',
            'non-aktif' => 'warning',
            default => 'secondary',
        };
    }

    /**
     * Helper: Get status semester badge
     */
    public function getSemesterBadgeAttribute(): string
    {
        return $this->semester_aktif === 'Ganjil' ? 'primary' : 'info';
    }

    /**
     * Helper: Check if semester is ganjil
     */
    public function isSemesterGanjil(): bool
    {
        return $this->semester_aktif === 'Ganjil';
    }

    /**
     * Helper: Switch semester
     */
    public function switchSemester(): void
    {
        $this->semester_aktif = $this->isSemesterGanjil() ? 'Genap' : 'Ganjil';
        $this->save();
    }

    /**
     * Helper: Set as active year (only 1 can be active)
     */
    public function setAsActive(): void
    {
        // Deactivate all other years
        self::where('id', '!=', $this->id)->update(['is_active' => false, 'status' => 'non-aktif']);
        
        // Activate this year
        $this->is_active = true;
        $this->status = 'aktif';
        $this->save();
    }

    /**
     * Helper: Get duration in months
     */
    public function getDurationMonthsAttribute(): int
    {
        return $this->tanggal_mulai->diffInMonths($this->tanggal_selesai);
    }
}
