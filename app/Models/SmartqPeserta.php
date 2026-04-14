<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasUuid;

class SmartqPeserta extends Model
{
    use HasUuid, SoftDeletes;

    protected $table = 'smartq_pesertas';

    protected $fillable = [
        'smartq_periode_id',
        'siswa_id',
        'nomor_peserta',
        'kelas_asal_id',
        'bidang_mapel_id',
        'status',
        'total_nilai',
        'ranking',
        'peringkat_mapel',
        'pengumuman_dibuka_at',
        'pengumuman_dibuka_ip',
        'pengumuman_dibuka_user_agent',
        'catatan',
    ];

    protected $casts = [
        'total_nilai' => 'decimal:2',
        'ranking' => 'integer',
        'peringkat_mapel' => 'integer',
        'pengumuman_dibuka_at' => 'datetime',
    ];

    public function periode()
    {
        return $this->belongsTo(SmartqPeriode::class, 'smartq_periode_id');
    }

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }

    public function kelasAsal()
    {
        return $this->belongsTo(Kelas::class, 'kelas_asal_id');
    }

    public function bidangMapel()
    {
        return $this->belongsTo(MataPelajaran::class, 'bidang_mapel_id');
    }

    public function nilais()
    {
        return $this->hasMany(SmartqNilai::class, 'smartq_peserta_id');
    }

    public function getNilaiKomponen($komponenId)
    {
        return $this->nilais->where('smartq_komponen_nilai_id', $komponenId)->first();
    }

    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'terdaftar' => '<span class="badge badge-info"><i class="fas fa-user-clock"></i> Terdaftar</span>',
            'lulus' => '<span class="badge badge-success"><i class="fas fa-check-circle"></i> Diterima</span>',
            'cadangan' => '<span class="badge badge-warning"><i class="fas fa-hourglass-half"></i> Cadangan</span>',
            'tidak_lulus' => '<span class="badge badge-danger"><i class="fas fa-times-circle"></i> Tidak Lulus</span>',
            'mengundurkan_diri' => '<span class="badge badge-warning"><i class="fas fa-user-minus"></i> Mengundurkan Diri</span>',
            default => '<span class="badge badge-secondary">-</span>',
        };
    }
}
