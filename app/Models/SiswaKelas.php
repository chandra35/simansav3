<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SiswaKelas extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'siswa_kelas';

    protected $fillable = [
        'siswa_id',
        'kelas_id',
        'tahun_pelajaran_id',
        'tingkat',
        'tanggal_masuk',
        'tanggal_keluar',
        'status',
        'nomor_urut_absen',
        'is_ketua_kelas',
        'ketua_kelas_mulai_at',
        'ketua_kelas_selesai_at',
        'ketua_kelas_ditetapkan_by',
        'catatan_perpindahan',
        'keberadaan_diverifikasi_at',
        'keberadaan_diverifikasi_by',
    ];

    protected $casts = [
        'tanggal_masuk' => 'date',
        'tanggal_keluar' => 'date',
        'is_ketua_kelas' => 'boolean',
        'ketua_kelas_mulai_at' => 'datetime',
        'ketua_kelas_selesai_at' => 'datetime',
        'keberadaan_diverifikasi_at' => 'datetime',
        'tingkat' => 'integer',
    ];

    /**
     * Relasi ke Siswa
     */
    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }

    /**
     * Relasi ke Kelas
     */
    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }

    /**
     * Relasi ke Tahun Pelajaran
     */
    public function tahunPelajaran()
    {
        return $this->belongsTo(TahunPelajaran::class, 'tahun_pelajaran_id');
    }

    public function penetapKetuaKelas()
    {
        return $this->belongsTo(User::class, 'ketua_kelas_ditetapkan_by');
    }

    public function sedangMenjabatKetuaKelas(): bool
    {
        return $this->status === 'aktif'
            && $this->is_ketua_kelas
            && $this->ketua_kelas_selesai_at === null;
    }

    protected static function booted(): void
    {
        static::updating(function (SiswaKelas $record): void {
            if (
                $record->isDirty('status')
                && $record->status !== 'aktif'
                && $record->is_ketua_kelas
                && $record->ketua_kelas_selesai_at === null
            ) {
                $record->ketua_kelas_selesai_at = now();
            }
        });

        static::deleting(function (SiswaKelas $record): void {
            if (
                ! $record->isForceDeleting()
                && $record->is_ketua_kelas
                && $record->ketua_kelas_selesai_at === null
            ) {
                $record->forceFill(['ketua_kelas_selesai_at' => now()])->saveQuietly();
            }
        });
    }

    /**
     * Scope untuk siswa yang aktif
     */
    public function scopeAktif($query)
    {
        return $query->where('status', 'aktif');
    }

    /**
     * Scope untuk tahun pelajaran tertentu
     */
    public function scopeTahunPelajaran($query, $tahunPelajaranId)
    {
        return $query->where('tahun_pelajaran_id', $tahunPelajaranId);
    }

    /**
     * Scope untuk kelas tertentu
     */
    public function scopeKelas($query, $kelasId)
    {
        return $query->where('kelas_id', $kelasId);
    }
}
