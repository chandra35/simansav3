<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AsramaKamar extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'asrama_kamar';

    protected $fillable = [
        'kode', 'nama', 'gedung', 'lantai', 'kapasitas', 'pengasuh_asatidz_id',
        'catatan', 'is_active', 'created_by', 'updated_by',
    ];

    protected $casts = ['kapasitas' => 'integer', 'is_active' => 'boolean'];

    public function pengasuh()
    {
        return $this->belongsTo(AsramaAsatidz::class, 'pengasuh_asatidz_id');
    }

    public function penghuni()
    {
        return $this->hasMany(AsramaKamarSantri::class);
    }

    public function penghuniAktif()
    {
        return $this->penghuni()->where('status', 'aktif');
    }
}
