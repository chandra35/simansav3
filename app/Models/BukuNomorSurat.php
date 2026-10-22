<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BukuNomorSurat extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'buku_nomor_surat';

    protected $fillable = [
        'nomor_surat',
        'nomor_urut',
        'tahun',
        'tanggal_surat',
        'surat_nomor_config_id',
        'kode_klasifikasi',
        'perihal',
        'tujuan',
        'modul_asal',
        'suratable_type',
        'suratable_id',
        'penandatangan_id',
        'nama_penandatangan',
        'jabatan_penandatangan',
        'created_by',
        'status',
        'alasan_dibatalkan',
        'dibatalkan_oleh',
        'dibatalkan_at',
        'file_arsip',
        'keterangan',
    ];

    protected $casts = [
        'tanggal_surat' => 'date',
        'dibatalkan_at' => 'datetime',
        'nomor_urut' => 'integer',
        'tahun' => 'integer',
    ];

    const STATUS = [
        'aktif' => 'Aktif',
        'dibatalkan' => 'Dibatalkan',
    ];

    const MODUL_ASAL = [
        'manual_tu' => 'Manual TU',
        'surat_keterangan' => 'Surat Keterangan Siswa',
        'mutasi_siswa' => 'Mutasi Siswa',
        'surat_tugas' => 'Surat Tugas GTK',
        'sk' => 'Surat Keputusan',
        'undangan' => 'Undangan / Humas',
        'lainnya' => 'Lainnya',
    ];

    // Relations
    public function config()
    {
        return $this->belongsTo(SuratNomorConfig::class, 'surat_nomor_config_id');
    }

    public function suratable()
    {
        return $this->morphTo();
    }

    public function penandatangan()
    {
        return $this->belongsTo(Gtk::class, 'penandatangan_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function dibatalkanOleh()
    {
        return $this->belongsTo(User::class, 'dibatalkan_oleh');
    }

    // Scopes
    public function scopeAktif($query)
    {
        return $query->where('status', 'aktif');
    }

    public function scopeDibatalkan($query)
    {
        return $query->where('status', 'dibatalkan');
    }

    public function scopeTahun($query, $tahun)
    {
        return $query->where('tahun', $tahun);
    }

    public function scopeKlasifikasi($query, $kode)
    {
        return $query->where('kode_klasifikasi', $kode);
    }

    // Accessors
    public function getStatusLabelAttribute(): string
    {
        return self::STATUS[$this->status] ?? ucfirst($this->status);
    }

    public function getStatusBadgeAttribute(): string
    {
        return $this->status === 'aktif' ? 'success' : 'danger';
    }

    public function getModulAsalLabelAttribute(): string
    {
        return self::MODUL_ASAL[$this->modul_asal] ?? ucwords(str_replace('_', ' ', $this->modul_asal));
    }
}
