<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReferensiPerguruanTinggi extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    public const JENIS = [
        'PTN',
        'PTKIN',
        'Poltekkes',
        'Lainnya',
    ];

    protected $table = 'referensi_perguruan_tinggi';

    protected $fillable = [
        'nama',
        'jenis',
        'sumber_referensi',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function siswaLulusan()
    {
        return $this->hasMany(SiswaLulusan::class, 'referensi_perguruan_tinggi_id');
    }
}
