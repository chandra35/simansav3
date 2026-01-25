<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class JenisPembayaran extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'jenis_pembayaran';

    protected $fillable = [
        'tahun_pelajaran_id',
        'nama',
        'kode',
        'kategori',
        'nominal',
        'is_wajib',
        'is_bulanan',
        'bulan_berlaku',
        'keterangan',
        'is_aktif',
    ];

    protected $casts = [
        'nominal' => 'decimal:2',
        'is_wajib' => 'boolean',
        'is_bulanan' => 'boolean',
        'bulan_berlaku' => 'array',
        'is_aktif' => 'boolean',
    ];

    const KATEGORI = [
        'spp' => 'SPP',
        'daftar_ulang' => 'Daftar Ulang',
        'seragam' => 'Seragam',
        'kegiatan' => 'Kegiatan',
        'lainnya' => 'Lainnya',
    ];

    // Relations
    public function tahunPelajaran()
    {
        return $this->belongsTo(TahunPelajaran::class, 'tahun_pelajaran_id');
    }

    public function tagihan()
    {
        return $this->hasMany(Tagihan::class, 'jenis_pembayaran_id');
    }

    // Scopes
    public function scopeAktif($query)
    {
        return $query->where('is_aktif', true);
    }

    public function scopeWajib($query)
    {
        return $query->where('is_wajib', true);
    }

    public function scopeBulanan($query)
    {
        return $query->where('is_bulanan', true);
    }

    // Accessors
    public function getKategoriLabelAttribute()
    {
        return self::KATEGORI[$this->kategori] ?? $this->kategori;
    }

    public function getNominalFormatAttribute()
    {
        return 'Rp ' . number_format($this->nominal, 0, ',', '.');
    }
}
