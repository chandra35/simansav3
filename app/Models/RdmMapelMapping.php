<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RdmMapelMapping extends Model
{
    use HasUuids;

    protected $table = 'rdm_mapel_mappings';

    protected $fillable = [
        'rdm_mapel_id',
        'rdm_mapel_nama',
        'rdm_kurikulum_id',
        'mata_pelajaran_id',
        'mapped_by',
    ];

    protected $casts = [
        'rdm_mapel_id' => 'integer',
    ];

    public function mataPelajaran(): BelongsTo
    {
        return $this->belongsTo(MataPelajaran::class, 'mata_pelajaran_id');
    }

    public function mappedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'mapped_by');
    }
}
