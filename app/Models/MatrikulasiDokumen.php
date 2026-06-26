<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MatrikulasiDokumen extends Model
{
    use HasUuid, SoftDeletes;

    protected $table = 'matrikulasi_dokumens';

    protected $fillable = [
        'matrikulasi_peserta_id',
        'ppdb_calon_dokumen_id',
        'jenis_dokumen',
        'nama_dokumen',
        'nama_file',
        'file_path',
        'file_size',
        'mime_type',
        'storage_disk',
        'ppdb_source_disk',
        'ppdb_source_url',
        'status_verifikasi',
        'imported_at',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'imported_at' => 'datetime',
    ];

    public function peserta()
    {
        return $this->belongsTo(MatrikulasiPeserta::class, 'matrikulasi_peserta_id');
    }
}
