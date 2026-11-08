<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AsramaKomponenNilai extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'asrama_komponen_nilai';

    protected $fillable = [
        'tahun_pelajaran_id',
        'kode',
        'nama',
        'bobot',
        'urutan',
        'is_active',
        'keterangan',
    ];

    protected $casts = [
        'bobot' => 'decimal:2',
        'urutan' => 'integer',
        'is_active' => 'boolean',
    ];

    public function tahunPelajaran(): BelongsTo
    {
        return $this->belongsTo(TahunPelajaran::class, 'tahun_pelajaran_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('urutan')->orderBy('kode');
    }
}
