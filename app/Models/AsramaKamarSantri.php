<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AsramaKamarSantri extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'asrama_kamar_santri';

    protected $fillable = [
        'asrama_kamar_id', 'asrama_santri_id', 'tanggal_masuk',
        'tanggal_keluar', 'status', 'ditetapkan_by',
    ];

    protected $casts = ['tanggal_masuk' => 'date', 'tanggal_keluar' => 'date'];

    public function kamar()
    {
        return $this->belongsTo(AsramaKamar::class, 'asrama_kamar_id');
    }

    public function santri()
    {
        return $this->belongsTo(AsramaSantri::class, 'asrama_santri_id');
    }
}
