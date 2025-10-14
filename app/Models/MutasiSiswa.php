<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class MutasiSiswa extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'mutasi_siswa';

    protected $fillable = [
        'siswa_id',
        'jenis_mutasi',
        'tahun_pelajaran_id',
        'sekolah_asal',
        'npsn_sekolah_asal',
        'alamat_sekolah_asal',
        'kelas_asal',
        'alasan_mutasi_masuk',
        'sekolah_tujuan',
        'npsn_sekolah_tujuan',
        'alamat_sekolah_tujuan',
        'alasan_mutasi_keluar',
        'tanggal_mutasi',
        'nomor_surat_mutasi',
        'file_surat_mutasi',
        'status_verifikasi',
        'verifikator_id',
        'tanggal_verifikasi',
        'catatan_verifikasi',
        'catatan',
    ];

    protected $casts = [
        'tanggal_mutasi' => 'date',
        'tanggal_verifikasi' => 'datetime',
    ];

    /**
     * Relationship: Mutasi belongs to Siswa
     */
    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class);
    }

    /**
     * Relationship: Mutasi belongs to Tahun Pelajaran
     */
    public function tahunPelajaran(): BelongsTo
    {
        return $this->belongsTo(TahunPelajaran::class);
    }

    /**
     * Relationship: Verifikator (User)
     */
    public function verifikator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verifikator_id');
    }

    /**
     * Scope: Mutasi Masuk
     */
    public function scopeMasuk($query)
    {
        return $query->where('jenis_mutasi', 'masuk');
    }

    /**
     * Scope: Mutasi Keluar
     */
    public function scopeKeluar($query)
    {
        return $query->where('jenis_mutasi', 'keluar');
    }

    /**
     * Scope: Pending verification
     */
    public function scopePending($query)
    {
        return $query->where('status_verifikasi', 'pending');
    }

    /**
     * Scope: Approved
     */
    public function scopeApproved($query)
    {
        return $query->where('status_verifikasi', 'approved');
    }

    /**
     * Scope: Rejected
     */
    public function scopeRejected($query)
    {
        return $query->where('status_verifikasi', 'rejected');
    }

    /**
     * Helper: Check if mutasi masuk
     */
    public function isMutasiMasuk(): bool
    {
        return $this->jenis_mutasi === 'masuk';
    }

    /**
     * Helper: Check if mutasi keluar
     */
    public function isMutasiKeluar(): bool
    {
        return $this->jenis_mutasi === 'keluar';
    }

    /**
     * Helper: Check if pending
     */
    public function isPending(): bool
    {
        return $this->status_verifikasi === 'pending';
    }

    /**
     * Helper: Check if approved
     */
    public function isApproved(): bool
    {
        return $this->status_verifikasi === 'approved';
    }

    /**
     * Helper: Check if rejected
     */
    public function isRejected(): bool
    {
        return $this->status_verifikasi === 'rejected';
    }

    /**
     * Helper: Approve mutasi
     */
    public function approveMutasi(User $verifikator, ?string $catatan = null): bool
    {
        $this->status_verifikasi = 'approved';
        $this->verifikator_id = $verifikator->id;
        $this->tanggal_verifikasi = now();
        $this->catatan_verifikasi = $catatan;

        if ($this->save()) {
            // Update status siswa jika mutasi keluar
            if ($this->isMutasiKeluar()) {
                $this->siswa->update([
                    'status_siswa' => 'mutasi_keluar',
                    'kelas_saat_ini_id' => null,
                ]);
                
                // Disable user account
                $this->siswa->user->update(['is_active' => false]);
            }
            
            return true;
        }

        return false;
    }

    /**
     * Helper: Reject mutasi
     */
    public function rejectMutasi(User $verifikator, string $alasan): bool
    {
        $this->status_verifikasi = 'rejected';
        $this->verifikator_id = $verifikator->id;
        $this->tanggal_verifikasi = now();
        $this->catatan_verifikasi = $alasan;

        return $this->save();
    }

    /**
     * Helper: Get file URL
     */
    public function getFileSuratUrlAttribute(): ?string
    {
        if (!$this->file_surat_mutasi) {
            return null;
        }

        return Storage::url($this->file_surat_mutasi);
    }

    /**
     * Helper: Get badge color for status
     */
    public function getStatusBadgeColorAttribute(): string
    {
        return match($this->status_verifikasi) {
            'pending' => 'warning',
            'approved' => 'success',
            'rejected' => 'danger',
            default => 'secondary',
        };
    }

    /**
     * Helper: Get jenis mutasi text
     */
    public function getJenisMutasiTextAttribute(): string
    {
        return $this->jenis_mutasi === 'masuk' ? 'Mutasi Masuk' : 'Mutasi Keluar';
    }

    /**
     * Helper: Get status text
     */
    public function getStatusTextAttribute(): string
    {
        return match($this->status_verifikasi) {
            'pending' => 'Menunggu Verifikasi',
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
            default => 'Unknown',
        };
    }

    /**
     * Helper: Get nama sekolah (asal atau tujuan)
     */
    public function getNamaSekolahAttribute(): string
    {
        return $this->isMutasiMasuk() 
            ? $this->sekolah_asal ?? 'N/A'
            : $this->sekolah_tujuan ?? 'N/A';
    }

    /**
     * Helper: Get NPSN (asal atau tujuan)
     */
    public function getNpsnAttribute(): string
    {
        return $this->isMutasiMasuk()
            ? $this->npsn_sekolah_asal ?? 'N/A'
            : $this->npsn_sekolah_tujuan ?? 'N/A';
    }
}
