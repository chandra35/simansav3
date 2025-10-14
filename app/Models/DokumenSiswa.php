<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasUuid;
use App\Traits\HasCreatedUpdatedBy;
use Illuminate\Support\Facades\Storage;

class DokumenSiswa extends Model
{
    use HasUuid, HasCreatedUpdatedBy;

    protected $table = 'dokumen_siswa';

    protected $fillable = [
        'siswa_id',
        'jenis_dokumen',
        'nama_file',
        'file_path',
        'file_size',
        'mime_type',
        'keterangan',
        'created_by',
        'updated_by',
    ];

    // Relations
    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }

    // Helper methods
    public function getJenisDokumenLabel()
    {
        $labels = [
            'kk' => 'Kartu Keluarga',
            'ijazah_smp' => 'Ijazah SMP',
            'kip' => 'Kartu Indonesia Pintar (KIP)',
            'sktm' => 'Surat Keterangan Tidak Mampu (SKTM)',
        ];

        return $labels[$this->jenis_dokumen] ?? $this->jenis_dokumen;
    }

    public function getFileUrl()
    {
        return Storage::url($this->file_path);
    }

    public function getFileSizeFormatted()
    {
        if (!$this->file_size) return '-';
        
        $size = $this->file_size;
        if ($size < 1024) {
            return $size . ' KB';
        } else {
            return round($size / 1024, 2) . ' MB';
        }
    }

    // Delete file when model is deleted
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($dokumen) {
            if (Storage::exists($dokumen->file_path)) {
                Storage::delete($dokumen->file_path);
            }
        });
    }
}
