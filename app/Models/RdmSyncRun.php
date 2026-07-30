<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RdmSyncRun extends Model
{
    use HasUuids;

    protected $table = 'rdm_sync_runs';

    protected $fillable = [
        'rdm_tahunajaran_id',
        'rdm_semester_id',
        'rdm_tingkat_id',
        'rdm_kelas_nama',
        'simansa_tahun_pelajaran_id',
        'simansa_kelas_id',
        'status',
        'total_records',
        'matched_records',
        'mismatch_siswa_count',
        'mismatch_mapel_count',
        'mismatch_tahun_count',
        'applied_count',
        'initiated_by',
        'started_at',
        'finished_at',
        'notes',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function initiatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'initiated_by');
    }

    public function stagingRows(): HasMany
    {
        return $this->hasMany(RdmSyncStaging::class, 'run_id');
    }
}
