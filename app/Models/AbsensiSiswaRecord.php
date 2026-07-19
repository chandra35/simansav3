<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AbsensiSiswaRecord extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'absensi_siswa_records';

    protected $fillable = [
        'session_id',
        'siswa_id',
        'status',
        'late_minutes',
        'left_early_minutes',
        'notes',
        'attendance_method',
        'source_reference',
        'face_confidence',
        'checked_at',
        'checked_by',
    ];

    protected $casts = [
        'checked_at' => 'datetime',
        'face_confidence' => 'decimal:4',
        'late_minutes' => 'integer',
        'left_early_minutes' => 'integer',
    ];

    public function session()
    {
        return $this->belongsTo(AbsensiSiswaSession::class, 'session_id');
    }

    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }

    public function checker()
    {
        return $this->belongsTo(User::class, 'checked_by');
    }
}
