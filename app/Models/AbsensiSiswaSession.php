<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AbsensiSiswaSession extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'absensi_siswa_sessions';

    protected $fillable = [
        'tahun_pelajaran_id',
        'session_key',
        'kelas_id',
        'jadwal_pelajaran_id',
        'mapel_id',
        'guru_user_id',
        'tanggal',
        'semester',
        'tingkat',
        'kelas_snapshot',
        'mapel_snapshot',
        'guru_snapshot',
        'scheduled_start',
        'scheduled_end',
        'mode',
        'attendance_method',
        'status',
        'finalized_at',
        'locked_at',
        'finalized_by',
        'version',
        'revision_reason',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'tingkat' => 'integer',
        'finalized_at' => 'datetime',
        'locked_at' => 'datetime',
        'version' => 'integer',
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

    public function audits()
    {
        return $this->hasMany(AbsensiSiswaAudit::class, 'session_id')->latest();
    }

    public function finalizer()
    {
        return $this->belongsTo(User::class, 'finalized_by');
    }
}
