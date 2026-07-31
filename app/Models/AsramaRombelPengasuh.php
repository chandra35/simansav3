<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AsramaRombelPengasuh extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'asrama_rombel_pengasuh';

    protected $fillable = [
        'asrama_kelas_id', 'asrama_asatidz_id', 'is_primary', 'tanggal_mulai',
        'tanggal_selesai', 'is_active', 'created_by',
    ];

    protected $casts = [
        'is_primary' => 'boolean', 'is_active' => 'boolean',
        'tanggal_mulai' => 'date', 'tanggal_selesai' => 'date',
    ];

    public function rombel()
    {
        return $this->belongsTo(AsramaKelas::class, 'asrama_kelas_id');
    }

    public function pengasuh()
    {
        return $this->belongsTo(AsramaAsatidz::class, 'asrama_asatidz_id');
    }

    public function santriAssignments()
    {
        return $this->hasMany(AsramaPengasuhSantri::class);
    }
}
