<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SuratKeluar extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'surat_keluar';

    protected $fillable = [
        'template_surat_id',
        'nomor_surat',
        'tanggal_surat',
        'perihal',
        'tujuan',
        'konten_generate',
        'penandatangan_id',
        'nama_penandatangan',
        'jabatan_penandatangan',
        'status',
        'file_pdf',
        'jumlah_cetak',
        'data_tambahan',
        'created_by',
    ];

    protected $casts = [
        'tanggal_surat' => 'date',
        'data_tambahan' => 'array',
        'jumlah_cetak' => 'integer',
    ];

    const STATUS = [
        'draft' => 'Draft',
        'menunggu_ttd' => 'Menunggu TTD',
        'selesai' => 'Selesai',
        'dibatalkan' => 'Dibatalkan',
    ];

    // Relations
    public function template()
    {
        return $this->belongsTo(TemplateSurat::class, 'template_surat_id');
    }

    public function penandatangan()
    {
        return $this->belongsTo(Gtk::class, 'penandatangan_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function bukuNomorSurat()
    {
        return $this->morphOne(BukuNomorSurat::class, 'suratable');
    }

    // Generate nomor surat via SuratNumberService
    public static function generateNomorSurat($kode = 'PP')
    {
        return app(\App\Services\SuratNumberService::class)->generate($kode);
    }

    // Scopes
    public function scopeSelesai($query)
    {
        return $query->where('status', 'selesai');
    }

    public function scopeMenungguTtd($query)
    {
        return $query->where('status', 'menunggu_ttd');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    // Accessors
    public function getStatusLabelAttribute()
    {
        return self::STATUS[$this->status] ?? $this->status;
    }

    public function getStatusBadgeAttribute()
    {
        $badges = [
            'draft' => 'secondary',
            'menunggu_ttd' => 'warning',
            'selesai' => 'success',
            'dibatalkan' => 'danger',
        ];
        return $badges[$this->status] ?? 'secondary';
    }
}
