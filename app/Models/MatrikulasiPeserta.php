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
        'user_id',
        'akun_created_at',
        'akun_last_reset_at',
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
        'status_pembayaran',
        'status_matrikulasi',
        'tanggal_hadir_matrikulasi',
        'verified_at',
        'verified_by',
        'catatan_validasi',
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
        'akun_created_at' => 'datetime',
        'akun_last_reset_at' => 'datetime',
        'tanggal_hadir_matrikulasi' => 'date',
        'verified_at' => 'datetime',
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

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function dokumens()
    {
        return $this->hasMany(MatrikulasiDokumen::class, 'matrikulasi_peserta_id');
    }
}
