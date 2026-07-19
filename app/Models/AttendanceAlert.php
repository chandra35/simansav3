<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class AttendanceAlert extends Model
{
    use HasUuids;

    protected $fillable = [
        'siswa_id', 'tahun_pelajaran_id', 'fingerprint', 'rule_code', 'severity',
        'score', 'title', 'explanation', 'evidence', 'period_start', 'period_end',
        'status', 'is_active', 'first_detected_at', 'last_detected_at', 'assigned_to',
        'reviewed_by', 'reviewed_at', 'review_notes',
    ];

    protected $casts = [
        'evidence' => 'array',
        'period_start' => 'date',
        'period_end' => 'date',
        'is_active' => 'boolean',
        'first_detected_at' => 'datetime',
        'last_detected_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }

    public function tahunPelajaran()
    {
        return $this->belongsTo(TahunPelajaran::class);
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
