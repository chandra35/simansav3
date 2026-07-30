<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RdmSyncStaging extends Model
{
    use HasUuids;

    protected $table = 'rdm_sync_staging';

    protected $fillable = [
        'run_id',
        'rdm_siswa_id',
        'rdm_nisn',
        'rdm_nis',
        'rdm_nama',
        'rdm_kelas_nama',
        'rdm_tingkat_id',
        'rdm_mapel_id',
        'rdm_mapel_nama',
        'rdm_nilai',
        'rdm_nilai_pengetahuan',
        'rdm_nilai_keterampilan',
        'rdm_predikat',
        'rdm_deskripsi_pengetahuan',
        'rdm_deskripsi_keterampilan',
        'rdm_tahunajaran_id',
        'rdm_semester_id',
        'simansa_siswa_id',
        'simansa_mata_pelajaran_id',
        'simansa_tahun_pelajaran_id',
        'simansa_semester',
        'apply_action',
        'existing_nilai',
        'existing_nilai_pengetahuan',
        'existing_nilai_keterampilan',
        'match_status',
        'match_notes',
    ];

    protected $casts = [
        'rdm_nilai' => 'decimal:2',
        'rdm_nilai_pengetahuan' => 'decimal:2',
        'rdm_nilai_keterampilan' => 'decimal:2',
        'existing_nilai' => 'decimal:2',
        'existing_nilai_pengetahuan' => 'decimal:2',
        'existing_nilai_keterampilan' => 'decimal:2',
        'rdm_semester_id' => 'integer',
        'simansa_semester' => 'integer',
    ];

    public function run(): BelongsTo
    {
        return $this->belongsTo(RdmSyncRun::class, 'run_id');
    }

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class, 'simansa_siswa_id');
    }

    public function mataPelajaran(): BelongsTo
    {
        return $this->belongsTo(MataPelajaran::class, 'simansa_mata_pelajaran_id');
    }
}
