<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SpanPtkinRegistration extends Model
{
    use HasFactory, HasUuids;

    public const CHECK_STATUS = [
        'belum_dicek',
        'lulus',
        'tidak_lulus',
        'gagal_cek',
    ];

    protected $fillable = [
        'span_ptkin_menu_id',
        'siswa_id',
        'tahun_pelajaran_id',
        'nomor_pendaftaran',
        'nama_pendaftar',
        'jurusan_pendaftar',
        'source_file_name',
        'imported_at',
        'check_status',
        'last_checked_at',
        'last_check_message',
        'last_check_payload',
    ];

    protected $casts = [
        'imported_at' => 'datetime',
        'last_checked_at' => 'datetime',
        'last_check_payload' => 'array',
    ];

    public function spanPtkinMenu()
    {
        return $this->belongsTo(SpanPtkinMenu::class);
    }

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }

    public function tahunPelajaran()
    {
        return $this->belongsTo(TahunPelajaran::class, 'tahun_pelajaran_id');
    }

    public function lulusan()
    {
        return $this->hasOne(SiswaLulusan::class, 'span_ptkin_registration_id');
    }

    public function getCheckStatusLabelAttribute(): string
    {
        return match ($this->check_status) {
            'lulus' => 'Lulus',
            'tidak_lulus' => 'Tidak Lulus',
            'gagal_cek' => 'Gagal Cek',
            default => 'Belum Dicek',
        };
    }
}
