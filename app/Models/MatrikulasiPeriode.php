<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MatrikulasiPeriode extends Model
{
    use HasUuid, SoftDeletes;

    protected $table = 'matrikulasi_periodes';

    protected $fillable = [
        'tahun_pelajaran_id',
        'nama',
        'status',
        'catatan',
    ];

    public function tahunPelajaran()
    {
        return $this->belongsTo(TahunPelajaran::class);
    }

    public function kelompoks()
    {
        return $this->hasMany(MatrikulasiKelompok::class, 'matrikulasi_periode_id');
    }

    public function pesertas()
    {
        return $this->hasMany(MatrikulasiPeserta::class, 'matrikulasi_periode_id');
    }
}
