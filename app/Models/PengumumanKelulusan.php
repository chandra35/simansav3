<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PengumumanKelulusan extends Model
{
    use HasUuids, SoftDeletes;

    public const STATUS_LULUS = 'lulus';
    public const STATUS_LULUS_BERSYARAT = 'lulus_bersyarat';
    public const STATUS_TIDAK_LULUS = 'tidak_lulus';

    public const STATUSES = [
        self::STATUS_LULUS => 'Lulus',
        self::STATUS_LULUS_BERSYARAT => 'Lulus Bersyarat',
        self::STATUS_TIDAK_LULUS => 'Tidak Lulus',
    ];

    protected $table = 'pengumuman_kelulusan';

    protected $fillable = [
        'tahun_pelajaran_id',
        'siswa_id',
        'kelas_id',
        'status',
        'catatan',
        'opened_at',
        'opened_ip',
        'opened_user_agent',
    ];

    protected $casts = [
        'opened_at' => 'datetime',
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

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? '-';
    }

    public function getStatusBadgeColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_LULUS => 'success',
            self::STATUS_LULUS_BERSYARAT => 'warning',
            self::STATUS_TIDAK_LULUS => 'danger',
            default => 'secondary',
        };
    }
}
