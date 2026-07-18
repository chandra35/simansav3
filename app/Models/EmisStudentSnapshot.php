<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmisStudentSnapshot extends Model
{
    use HasUuid;

    protected $fillable = [
        'sync_id',
        'tahun_pelajaran_id',
        'siswa_id',
        'emis_student_id',
        'learning_activity_id',
        'nisn',
        'full_name',
        'birth_place',
        'birth_date',
        'gender',
        'student_status_id',
        'student_status',
        'status_description',
        'dukcapil_verification_status_id',
        'valid_nisn',
        'level_name',
        'study_group_name',
        'major_name',
        'academic_year',
        'academic_year_status',
        'simansa_data',
        'comparison_status',
        'name_similarity',
        'comparison_details',
        'synced_at',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'valid_nisn' => 'boolean',
        'name_similarity' => 'float',
        'comparison_details' => 'array',
        'simansa_data' => 'array',
        'synced_at' => 'datetime',
    ];

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class);
    }

    public function sync(): BelongsTo
    {
        return $this->belongsTo(EmisStudentSync::class, 'sync_id');
    }

    public function tahunPelajaran(): BelongsTo
    {
        return $this->belongsTo(TahunPelajaran::class);
    }

    public function scopeForYear($query, ?string $yearId)
    {
        return $query->where('tahun_pelajaran_id', $yearId);
    }
}
