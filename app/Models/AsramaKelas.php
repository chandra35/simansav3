<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AsramaKelas extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'asrama_kelas';

    protected $fillable = [
        'asrama_id', 'tahun_pelajaran_id', 'kelas_id', 'nama_kelas', 'nama_arab', 'tingkat',
        'jenis', 'wali_asatidz_id', 'kapasitas', 'ruang', 'is_active',
        'deskripsi', 'created_by', 'updated_by',
    ];

    protected $casts = ['tingkat' => 'integer', 'kapasitas' => 'integer', 'is_active' => 'boolean'];

    public function asrama()
    {
        return $this->belongsTo(Asrama::class);
    }

    public function tahunPelajaran()
    {
        return $this->belongsTo(TahunPelajaran::class);
    }

    public function kelasReguler()
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }

    public function wali()
    {
        return $this->belongsTo(AsramaAsatidz::class, 'wali_asatidz_id');
    }

    public function anggota()
    {
        return $this->hasMany(AsramaKelasSantri::class);
    }

    public function anggotaAktif()
    {
        return $this->anggota()->where('status', 'aktif');
    }

    public function pengampu()
    {
        return $this->hasMany(AsramaPengampu::class);
    }

    public function ketua()
    {
        return $this->hasOne(AsramaKelasSantri::class)->where('is_ketua_kelas', true)->where('status', 'aktif');
    }

    public function pengasuhRombel()
    {
        return $this->hasMany(AsramaRombelPengasuh::class)->where('is_active', true);
    }
}
