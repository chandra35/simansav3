<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CatatanKonseling extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'catatan_konseling';

    protected $fillable = [
        'siswa_id',
        'konselor_id',
        'tahun_pelajaran_id',
        'tanggal_konseling',
        'jenis_konseling',
        'kategori_masalah',
        'deskripsi_masalah',
        'hasil_konseling',
        'tindak_lanjut',
        'status',
        'jadwal_tindak_lanjut',
        'is_rahasia',
    ];

    protected $casts = [
        'tanggal_konseling' => 'date',
        'jadwal_tindak_lanjut' => 'date',
        'is_rahasia' => 'boolean',
    ];

    const JENIS_KONSELING = [
        'individual' => 'Individual',
        'kelompok' => 'Kelompok',
        'klasikal' => 'Klasikal',
    ];

    const KATEGORI_MASALAH = [
        'akademik' => 'Akademik',
        'pribadi' => 'Pribadi',
        'sosial' => 'Sosial',
        'karir' => 'Karir',
        'keluarga' => 'Keluarga',
        'lainnya' => 'Lainnya',
    ];

    const STATUS = [
        'proses' => 'Dalam Proses',
        'selesai' => 'Selesai',
        'perlu_tindak_lanjut' => 'Perlu Tindak Lanjut',
    ];

    // Relations
    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }

    public function konselor()
    {
        return $this->belongsTo(Gtk::class, 'konselor_id');
    }

    public function tahunPelajaran()
    {
        return $this->belongsTo(TahunPelajaran::class, 'tahun_pelajaran_id');
    }

    // Scopes
    public function scopeRahasia($query, $rahasia = true)
    {
        return $query->where('is_rahasia', $rahasia);
    }

    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopePerluTindakLanjut($query)
    {
        return $query->where('status', 'perlu_tindak_lanjut');
    }

    // Accessors
    public function getJenisLabelAttribute()
    {
        return self::JENIS_KONSELING[$this->jenis_konseling] ?? $this->jenis_konseling;
    }

    public function getKategoriLabelAttribute()
    {
        return self::KATEGORI_MASALAH[$this->kategori_masalah] ?? $this->kategori_masalah;
    }

    public function getStatusLabelAttribute()
    {
        return self::STATUS[$this->status] ?? $this->status;
    }

    public function getStatusBadgeAttribute()
    {
        $badges = [
            'proses' => 'warning',
            'selesai' => 'success',
            'perlu_tindak_lanjut' => 'danger',
        ];
        return $badges[$this->status] ?? 'secondary';
    }
}
