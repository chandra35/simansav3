<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class AbsensiSiswaSession extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'absensi_siswa_sessions';

    protected $fillable = [
        'tahun_pelajaran_id',
        'kelas_id',
        'jadwal_pelajaran_id',
        'mapel_id',
        'guru_user_id',
        'tanggal',
        'mode',
        'attendance_method',
        'status',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function tahunPelajaran()
    {
        return $this->belongsTo(TahunPelajaran::class, 'tahun_pelajaran_id');
    }

    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }

    public function jadwalPelajaran()
    {
        return $this->belongsTo(JadwalPelajaran::class, 'jadwal_pelajaran_id');
    }

    public function mapel()
    {
        return $this->belongsTo(MataPelajaran::class, 'mapel_id');
    }

    public function guru()
    {
        return $this->belongsTo(User::class, 'guru_user_id');
    }

    public function records()
    {
        return $this->hasMany(AbsensiSiswaRecord::class, 'session_id');
    }
}
