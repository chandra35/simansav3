<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SuratKeterangan extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'surat_keterangan';

    protected $fillable = [
        'template_surat_id',
        'siswa_id',
        'nomor_surat',
        'tanggal_surat',
        'keperluan',
        'data_tambahan',
        'status',
        'file_surat',
        'created_by',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'tanggal_surat' => 'date',
        'data_tambahan' => 'array',
        'approved_at' => 'datetime',
    ];

    const STATUS = [
        'draft' => 'Draft',
        'pending' => 'Menunggu Persetujuan',
        'approved' => 'Disetujui',
        'rejected' => 'Ditolak',
        'printed' => 'Sudah Dicetak',
    ];

    // Relations
    public function template()
    {
        return $this->belongsTo(TemplateSurat::class, 'template_surat_id');
    }

    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Generate nomor surat
    public static function generateNomorSurat($kode = 'SK')
    {
        return app(\App\Services\SuratNumberService::class)->generate('PP');
    }

    // Scopes
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
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
            'pending' => 'warning',
            'approved' => 'success',
            'rejected' => 'danger',
            'printed' => 'info',
        ];
        return $badges[$this->status] ?? 'secondary';
    }
}
