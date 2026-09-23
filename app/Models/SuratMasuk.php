<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SuratMasuk extends Model
{
    use HasUuid, SoftDeletes;

    protected $table = 'surat_masuk';

    protected $fillable = [
        'tahun', 'nomor_urut', 'nomor_berkas', 'kode_unik', 'tanggal_nomor', 'asal', 'asal_id',
        'isi_ringkasan', 'diterima_tanggal', 'status', 'surat_masuk_path',
        'surat_masuk_nama', 'print_path', 'print_count', 'printed_at',
        'hasil_disposisi_path', 'hasil_disposisi_nama',
        'hasil_disposisi_uploaded_at', 'hasil_disposisi_uploaded_by',
        'created_by', 'updated_by',
    ];

    protected $casts = [
        'diterima_tanggal' => 'date',
        'printed_at' => 'datetime',
        'hasil_disposisi_uploaded_at' => 'datetime',
    ];

    public function uploader()
    {
        return $this->belongsTo(User::class, 'hasil_disposisi_uploaded_by');
    }

    public function asalReferensi()
    {
        return $this->belongsTo(SuratMasukAsal::class, 'asal_id');
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'selesai' => 'Selesai',
            'sudah_diprint' => 'Sudah Diprint',
            default => 'Dicatat PTSP',
        };
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'selesai' => 'success',
            'sudah_diprint' => 'info',
            default => 'secondary',
        };
    }
}
