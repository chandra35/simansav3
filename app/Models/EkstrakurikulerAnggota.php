<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EkstrakurikulerAnggota extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'ekstrakurikuler_anggota';

    protected $fillable = [
        'ekstrakurikuler_id',
        'siswa_id',
        'tahun_pelajaran_id',
        'tanggal_bergabung',
        'tanggal_keluar',
        'status',
        'jabatan',
        'nilai_ekskul',
        'predikat',
        'catatan',
        'created_by',
    ];

    protected $casts = [
        'tanggal_bergabung' => 'date',
        'tanggal_keluar' => 'date',
        'nilai_ekskul' => 'integer',
    ];

    // Relations
    public function ekstrakurikuler()
    {
        return $this->belongsTo(Ekstrakurikuler::class, 'ekstrakurikuler_id');
    }

    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }

    public function tahunPelajaran()
    {
        return $this->belongsTo(TahunPelajaran::class, 'tahun_pelajaran_id');
    }

    // Scopes
    public function scopeAktif($query)
    {
        return $query->where('status', 'aktif');
    }

    // Calculate predikat from nilai
    public static function hitungPredikat($nilai)
    {
        if ($nilai >= 90) return 'A';
        if ($nilai >= 80) return 'B';
        if ($nilai >= 70) return 'C';
        if ($nilai >= 60) return 'D';
        return 'E';
    }
}
