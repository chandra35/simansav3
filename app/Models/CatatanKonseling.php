<?php

namespace App\Models;

use App\Traits\HasActivityLog;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CatatanKonseling extends Model
{
    use HasActivityLog, HasFactory, HasUuids, SoftDeletes;

    protected $table = 'catatan_konseling';

    protected $fillable = [
        'siswa_id', 'konselor_id', 'tahun_pelajaran_id', 'tanggal_konseling',
        'waktu_mulai', 'waktu_selesai', 'jenis_konseling', 'kategori_masalah',
        'permasalahan', 'hasil_konseling', 'rekomendasi', 'tindak_lanjut',
        'tanggal_tindak_lanjut', 'status', 'rujukan_ke', 'is_confidential', 'created_by',
    ];

    protected $casts = [
        'tanggal_konseling' => 'date',
        'tanggal_tindak_lanjut' => 'date',
        'is_confidential' => 'boolean',
    ];

    public const JENIS_KONSELING = [
        'individual' => 'Individual',
        'kelompok' => 'Kelompok',
        'klasikal' => 'Klasikal',
        'konsultasi_orangtua' => 'Konsultasi Orang Tua',
        'home_visit' => 'Kunjungan Rumah',
    ];

    public const KATEGORI_MASALAH = [
        'pribadi' => 'Pribadi',
        'sosial' => 'Sosial',
        'belajar' => 'Belajar / Akademik',
        'karir' => 'Karier',
        'keluarga' => 'Keluarga',
        'perilaku' => 'Perilaku',
        'kesehatan' => 'Kesehatan',
        'lainnya' => 'Lainnya',
    ];

    public const STATUS = [
        'baru' => 'Baru',
        'dalam_proses' => 'Dalam Proses',
        'selesai' => 'Selesai',
        'perlu_rujukan' => 'Perlu Rujukan',
    ];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'siswa_id')->withTrashed();
    }

    public function konselor()
    {
        return $this->belongsTo(Gtk::class, 'konselor_id')->withTrashed();
    }

    public function tahunPelajaran()
    {
        return $this->belongsTo(TahunPelajaran::class, 'tahun_pelajaran_id')->withTrashed();
    }

    public function pembuat()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeStatus($query, ?string $status)
    {
        return $query->when($status, fn ($q) => $q->where('status', $status));
    }

    public function scopePerluTindakLanjut($query)
    {
        return $query->whereNotNull('tanggal_tindak_lanjut')->where('status', '!=', 'selesai');
    }

    public function getJenisLabelAttribute(): string
    {
        return self::JENIS_KONSELING[$this->jenis_konseling] ?? $this->jenis_konseling;
    }

    public function getKategoriLabelAttribute(): string
    {
        return self::KATEGORI_MASALAH[$this->kategori_masalah] ?? $this->kategori_masalah;
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS[$this->status] ?? $this->status;
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'baru' => 'info',
            'dalam_proses' => 'warning',
            'selesai' => 'success',
            'perlu_rujukan' => 'danger',
            default => 'secondary',
        };
    }

    public function getTindakLanjutTerlambatAttribute(): bool
    {
        return $this->status !== 'selesai'
            && $this->tanggal_tindak_lanjut
            && $this->tanggal_tindak_lanjut->isPast();
    }
}
