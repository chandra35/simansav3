<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReferensiProgramStudi extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'referensi_program_studi';

    protected $fillable = [
        'referensi_perguruan_tinggi_id',
        'nama',
        'jenjang',
        'fakultas',
        'sumber_referensi',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function perguruanTinggi()
    {
        return $this->belongsTo(ReferensiPerguruanTinggi::class, 'referensi_perguruan_tinggi_id');
    }

    public function siswaLulusan()
    {
        return $this->hasMany(SiswaLulusan::class, 'referensi_program_studi_id');
    }
}
