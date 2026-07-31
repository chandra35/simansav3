<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AsramaKelasSantri extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'asrama_kelas_santri';

    protected $fillable = [
        'asrama_kelas_id', 'asrama_santri_id', 'nomor_urut', 'is_ketua_kelas',
        'tanggal_masuk', 'tanggal_keluar', 'status', 'ditetapkan_by',
    ];

    protected $casts = [
        'is_ketua_kelas' => 'boolean', 'tanggal_masuk' => 'date', 'tanggal_keluar' => 'date',
    ];

    public function kelas()
    {
        return $this->belongsTo(AsramaKelas::class, 'asrama_kelas_id');
    }

    public function santri()
    {
        return $this->belongsTo(AsramaSantri::class, 'asrama_santri_id');
    }

    public function nilai()
    {
        return $this->hasMany(AsramaNilai::class);
    }

    public function rapor()
    {
        return $this->hasMany(AsramaRapor::class);
    }

    public function pengasuhAssignment()
    {
        return $this->hasOne(AsramaPengasuhSantri::class, 'asrama_kelas_santri_id')
            ->where('is_active', true);
    }
}
