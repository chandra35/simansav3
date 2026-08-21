<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
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
        if (! $this->isPending()) {
            throw new \DomainException('Mutasi ini sudah diverifikasi.');
        }

        $siswa = $this->siswa()->lockForUpdate()->first();

        if (! $siswa) {
            throw new \DomainException('Data siswa pada mutasi ini tidak ditemukan.');
        }

        $this->status_verifikasi = 'approved';
        $this->verifikator_id = $verifikator->id;
        $this->tanggal_verifikasi = now();
        $this->catatan_verifikasi = $catatan;
        $this->saveOrFail();

        if ($this->isMutasiKeluar()) {
            SiswaKelas::query()
                ->where('siswa_id', $this->siswa_id)
                ->where('status', 'aktif')
                ->lockForUpdate()
                ->get()
                ->each(function (SiswaKelas $riwayatKelas): void {
                    $catatan = trim(implode(' ', array_filter([
                        $riwayatKelas->catatan_perpindahan,
                        'Ditutup karena mutasi keluar.',
                    ])));

                    $riwayatKelas->updateOrFail([
                        'status' => 'keluar',
                        'tanggal_keluar' => $this->tanggal_mutasi ?? today(),
                        'catatan_perpindahan' => $catatan,
                    ]);
                });

            $siswa->updateOrFail([
                'status_siswa' => 'mutasi_keluar',
                'kelas_saat_ini_id' => null,
            ]);

            if ($siswa->user) {
                $siswa->user->updateOrFail(['is_active' => false]);
                UserSession::query()
                    ->where('user_id', $siswa->user->id)
                    ->update(['is_online' => false]);
            }
        }

        return true;
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
        if (! $this->file_surat_mutasi) {
            return null;
        }

        return Storage::disk('public')->url($this->file_surat_mutasi);
    }

    /**
     * Helper: Get badge color for status
     */
    public function getStatusBadgeColorAttribute(): string
    {
        return match ($this->status_verifikasi) {
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
        return match ($this->status_verifikasi) {
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
            : $this->sekolah_tujuan ?? 'Belum ditentukan';
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

    /**
     * Kelas asal yang tersimpan pada mutasi, dengan fallback ke riwayat kelas
     * untuk data mutasi keluar lama yang dibuat sebelum snapshot kelas disimpan.
     */
    public function getAsalKelasAttribute(): string
    {
        if (filled($this->kelas_asal)) {
            return $this->kelas_asal;
        }

        if (! $this->isMutasiKeluar() || ! $this->siswa) {
            return '-';
        }

        $riwayatKelas = $this->siswa->siswaKelasRecords
            ->filter(fn (SiswaKelas $record) => $record->tahun_pelajaran_id === $this->tahun_pelajaran_id)
            ->sortByDesc(fn (SiswaKelas $record) => sprintf(
                '%s-%s',
                $record->tanggal_keluar?->format('Y-m-d') ?? '0000-00-00',
                $record->created_at?->format('Y-m-d H:i:s.u') ?? ''
            ))
            ->first();

        return $riwayatKelas?->kelas?->nama_lengkap
            ?? $riwayatKelas?->kelas?->nama_kelas
            ?? '-';
    }
}
