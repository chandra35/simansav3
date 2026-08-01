<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CatatanWaliKelas extends Model
{
    use HasUuids, HasFactory, SoftDeletes;

    protected $table = 'catatan_wali_kelas';

    protected $fillable = [
        'siswa_id',
        'kelas_id',
        'tahun_pelajaran_id',
        'created_by',
        'tanggal',
        'kategori',
        'catatan',
        'is_penting',
        'dibaca_bk_at',
        'dibaca_bk_by',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'is_penting' => 'boolean',
        'dibaca_bk_at' => 'datetime',
    ];

    public const KATEGORI = [
        'akademik' => 'Akademik',
        'sikap' => 'Sikap & Perilaku',
        'kehadiran' => 'Kehadiran',
        'prestasi' => 'Prestasi',
        'lainnya' => 'Lainnya',
    ];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }

    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }

    public function tahunPelajaran()
    {
        return $this->belongsTo(TahunPelajaran::class, 'tahun_pelajaran_id');
    }

    public function penulis()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function pembacaBk()
    {
        return $this->belongsTo(User::class, 'dibaca_bk_by');
    }

    public function getKategoriLabelAttribute(): string
    {
        return self::KATEGORI[$this->kategori] ?? '—';
    }
}
