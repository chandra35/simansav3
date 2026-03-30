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
        'jalur_masuk',
        'nama_universitas',
        'jurusan_fakultas',
        'program_studi',
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
}
