<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasUuid;
use App\Traits\HasCreatedUpdatedBy;
use Illuminate\Support\Facades\Storage;

class DokumenSiswa extends Model
{
    use HasUuid, HasCreatedUpdatedBy, SoftDeletes;

    protected $table = 'dokumen_siswa';

    protected $fillable = [
        'siswa_id',
        'jenis_dokumen',
        'nama_file',
        'file_path',
        'file_size',
        'mime_type',
        'keterangan',
        // New security fields
        'file_uuid',
        'original_name',
        'storage_disk',
        'tahun_pelajaran',
        'kelas_id',
        'uploaded_by_role',
        'status',
        'approved_by',
        'approved_at',
        'accessed_at',
        'access_count',
        // Legacy fields
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'approved_at' => 'datetime',
        'accessed_at' => 'datetime',
        'access_count' => 'integer',
    ];

    // Relations
    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }

    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
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
        // For new secure files, use authenticated route
        if ($this->file_uuid) {
            return route('siswa.dokumen.preview', $this->id);
        }
        
        // Legacy files (backward compatible)
        return Storage::url($this->file_path);
    }

    public function getSecureFilePath()
    {
        // Return absolute path for secure files
        if ($this->file_uuid) {
            return storage_path("app/private/{$this->file_path}");
        }
        
        // Legacy path
        return storage_path("app/public/{$this->file_path}");
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
            // Handle both secure and legacy files
            if ($dokumen->file_uuid) {
                // New secure files in private storage
                if (Storage::disk('private')->exists($dokumen->file_path)) {
                    Storage::disk('private')->delete($dokumen->file_path);
                }
            } else {
                // Legacy files in public storage
                if (Storage::disk('public')->exists($dokumen->file_path)) {
                    Storage::disk('public')->delete($dokumen->file_path);
                }
            }
        });
    }
}
