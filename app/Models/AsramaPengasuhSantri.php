<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AsramaPengasuhSantri extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'asrama_pengasuh_santri';

    protected $fillable = [
        'asrama_rombel_pengasuh_id', 'asrama_kelas_santri_id', 'tanggal_mulai',
        'tanggal_selesai', 'is_active', 'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean', 'tanggal_mulai' => 'date', 'tanggal_selesai' => 'date',
    ];

    public function rombelPengasuh()
    {
        return $this->belongsTo(AsramaRombelPengasuh::class, 'asrama_rombel_pengasuh_id');
    }

    public function anggota()
    {
        return $this->belongsTo(AsramaKelasSantri::class, 'asrama_kelas_santri_id');
    }
}
