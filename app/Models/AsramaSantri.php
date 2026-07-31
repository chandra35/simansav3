<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AsramaSantri extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'asrama_santri';

    protected $fillable = [
        'asrama_id', 'siswa_id', 'nomor_induk_asrama', 'tanggal_masuk',
        'tanggal_keluar', 'status', 'catatan', 'created_by', 'updated_by',
    ];

    protected $casts = ['tanggal_masuk' => 'date', 'tanggal_keluar' => 'date'];

    public function asrama()
    {
        return $this->belongsTo(Asrama::class);
    }

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }

    public function kelasRecords()
    {
        return $this->hasMany(AsramaKelasSantri::class);
    }

    public function kelasAktif()
    {
        return $this->hasOne(AsramaKelasSantri::class)
            ->where('status', 'aktif')->latestOfMany();
    }

    public function kamarRecords()
    {
        return $this->hasMany(AsramaKamarSantri::class);
    }

    public function kamarAktif()
    {
        return $this->hasOne(AsramaKamarSantri::class)->where('status', 'aktif')->latestOfMany();
    }
}
