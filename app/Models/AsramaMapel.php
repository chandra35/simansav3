<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AsramaMapel extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'asrama_mapel';

    protected $fillable = [
        'asrama_id', 'kode', 'nama_latin', 'nama_arab', 'kategori',
        'skala_maksimum', 'nilai_minimum', 'urutan', 'is_active',
        'deskripsi', 'created_by', 'updated_by',
    ];

    protected $casts = [
        'skala_maksimum' => 'decimal:2', 'nilai_minimum' => 'decimal:2',
        'urutan' => 'integer', 'is_active' => 'boolean',
    ];

    public function asrama()
    {
        return $this->belongsTo(Asrama::class);
    }

    public function pengampu()
    {
        return $this->hasMany(AsramaPengampu::class);
    }
}
