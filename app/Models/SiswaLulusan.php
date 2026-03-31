<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SiswaLulusan extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    public const JALUR_MASUK = [
        'SNBP',
        'SNBT',
        'SPAN-PTKIN',
        'Poltekkes',
        'Lainnya',
    ];

    protected $table = 'siswa_lulusan';

    protected $fillable = [
        'siswa_id',
        'tahun_pelajaran_id',
        'snbp_registration_id',
        'referensi_perguruan_tinggi_id',
        'referensi_program_studi_id',
        'jalur_masuk',
        'nama_universitas',
        'nama_universitas_manual',
        'jurusan_fakultas',
        'program_studi',
        'program_studi_manual',
        'keterangan',
    ];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }

    public function tahunPelajaran()
    {
        return $this->belongsTo(TahunPelajaran::class, 'tahun_pelajaran_id');
    }

    public function snbpRegistration()
    {
        return $this->belongsTo(SnbpRegistration::class, 'snbp_registration_id');
    }

    public function referensiPerguruanTinggi()
    {
        return $this->belongsTo(ReferensiPerguruanTinggi::class, 'referensi_perguruan_tinggi_id');
    }

    public function referensiProgramStudi()
    {
        return $this->belongsTo(ReferensiProgramStudi::class, 'referensi_program_studi_id');
    }
}
