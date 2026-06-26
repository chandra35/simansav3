<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MatrikulasiPeserta extends Model
{
    use HasUuid, SoftDeletes;

    protected $table = 'matrikulasi_pesertas';

    protected $fillable = [
        'matrikulasi_periode_id',
        'matrikulasi_kelompok_id',
        'siswa_id',
        'ppdb_calon_siswa_id',
        'ppdb_tahun_pelajaran_id',
        'nomor_registrasi',
        'nomor_tes',
        'nisn',
        'nik',
        'nama_lengkap',
        'jenis_kelamin',
        'jurusan_awal',
        'jurusan_final',
        'data_siswa',
        'data_ortu',
        'data_ppdb',
        'status',
        'imported_at',
        'promoted_at',
        'promoted_by',
        'catatan',
    ];

    protected $casts = [
        'data_siswa' => 'array',
        'data_ortu' => 'array',
        'data_ppdb' => 'array',
        'imported_at' => 'datetime',
        'promoted_at' => 'datetime',
    ];

    public function periode()
    {
        return $this->belongsTo(MatrikulasiPeriode::class, 'matrikulasi_periode_id');
    }

    public function kelompok()
    {
        return $this->belongsTo(MatrikulasiKelompok::class, 'matrikulasi_kelompok_id');
    }

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }

    public function dokumens()
    {
        return $this->hasMany(MatrikulasiDokumen::class, 'matrikulasi_peserta_id');
    }
}
