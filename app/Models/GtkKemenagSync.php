<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GtkKemenagSync extends Model
{
    use HasUuids;

    protected $table = 'gtk_kemenag_sync';

    protected $fillable = [
        // Reference
        'gtk_id',
        'raw_response',
        
        // Data Identitas
        'nip',
        'nip_baru',
        'nama',
        'nama_lengkap',
        'nik',
        'agama',
        'tempat_lahir',
        'tanggal_lahir',
        'jenis_kelamin',
        'status_kawin',
        
        // Data Pendidikan
        'pendidikan',
        'jenjang_pendidikan',
        'kode_bidang_studi',
        'bidang_studi',
        
        // Data Kontak
        'telepon',
        'no_hp',
        'email',
        'email_dinas',
        
        // Data Alamat
        'alamat_1',
        'alamat_2',
        'kab_kota',
        'provinsi',
        'kode_pos',
        'kode_lokasi',
        'lat',
        'lon',
        
        // Data Kepegawaian
        'status_pegawai',
        'kode_pangkat',
        'pangkat',
        'gol_ruang',
        'tmt_cpns',
        'tmt_pangkat',
        'tmt_pangkat_yad',
        
        // Data Jabatan
        'tipe_jabatan',
        'kode_jabatan',
        'tampil_jabatan',
        'kode_level_jabatan',
        'level_jabatan',
        'tmt_jabatan',
        
        // Data Satuan Kerja
        'kode_satuan_kerja',
        'satker_1',
        'kode_satker_2',
        'satker_2',
        'kode_satker_3',
        'satker_3',
        'kode_satker_4',
        'satker_4',
        'kode_satker_5',
        'satker_5',
        'kode_grup_satuan_kerja',
        'grup_satuan_kerja',
        'keterangan_satuan_kerja',
        'satker_kelola',
        
        // Data Masa Kerja
        'mk_tahun',
        'mk_bulan',
        'mk_tahun_1',
        'mk_bulan_1',
        
        // Data Gaji
        'gaji_pokok',
        'tmt_kgb_yad',
        
        // Data Pensiun
        'usia_pensiun',
        'tmt_pensiun',
        
        // Data Madrasah
        'nsm',
        'npsn',
        'kode_kua',
        'hari_kerja',
        
        // Data Tambahan
        'iso',
        'keterangan',
        
        // Metadata Sync
        'sync_status',
        'sync_message',
        'synced_at',
        'synced_by',
        
        // Status Perbandingan
        'has_differences',
        'differences',
        
        // Riwayat Update
        'last_applied_at',
        'applied_by',
    ];

    protected $casts = [
        'raw_response' => 'array',
        'differences' => 'array',
        'has_differences' => 'boolean',
        'tanggal_lahir' => 'date',
        'tmt_cpns' => 'date',
        'tmt_pangkat' => 'date',
        'tmt_pangkat_yad' => 'date',
        'tmt_jabatan' => 'date',
        'tmt_kgb_yad' => 'date',
        'tmt_pensiun' => 'date',
        'synced_at' => 'datetime',
        'last_applied_at' => 'datetime',
        'gaji_pokok' => 'decimal:2',
        'lat' => 'decimal:8',
        'lon' => 'decimal:8',
        'mk_tahun' => 'integer',
        'mk_bulan' => 'integer',
        'mk_tahun_1' => 'integer',
        'mk_bulan_1' => 'integer',
        'usia_pensiun' => 'integer',
        'hari_kerja' => 'integer',
    ];

    /**
     * Relationship dengan GTK
     */
    public function gtk(): BelongsTo
    {
        return $this->belongsTo(Gtk::class, 'gtk_id');
    }

    /**
     * User yang melakukan sync
     */
    public function syncedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'synced_by');
    }

    /**
     * User yang apply data ke lokal
     */
    public function appliedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'applied_by');
    }

    /**
     * Accessor untuk format gaji
     */
    public function getGajiPokoFormattedAttribute()
    {
        return 'Rp ' . number_format($this->gaji_pokok, 0, ',', '.');
    }

    /**
     * Accessor untuk masa kerja format lengkap
     */
    public function getMasaKerjaFormattedAttribute()
    {
        return "{$this->mk_tahun} Tahun {$this->mk_bulan} Bulan";
    }

    /**
     * Accessor untuk status sync dengan badge color
     */
    public function getSyncStatusBadgeAttribute()
    {
        return match($this->sync_status) {
            'success' => 'success',
            'failed' => 'danger',
            'partial' => 'warning',
            default => 'secondary'
        };
    }

    /**
     * Accessor untuk alamat lengkap
     */
    public function getAlamatLengkapAttribute()
    {
        $parts = array_filter([
            $this->alamat_1,
            $this->alamat_2,
            $this->kab_kota,
            $this->provinsi,
            $this->kode_pos
        ]);
        
        return implode(', ', $parts);
    }

    /**
     * Accessor untuk nama dengan gelar atau nama biasa
     */
    public function getNamaDisplayAttribute()
    {
        return $this->nama_lengkap ?: $this->nama;
    }

    /**
     * Check apakah data sync masih fresh (< 30 hari)
     */
    public function isFresh(): bool
    {
        if (!$this->synced_at) {
            return false;
        }
        
        return $this->synced_at->diffInDays(now()) < 30;
    }

    /**
     * Check apakah sudah pernah di-apply ke data lokal
     */
    public function hasBeenApplied(): bool
    {
        return !is_null($this->last_applied_at);
    }

    /**
     * Get jumlah perbedaan dengan data lokal (termasuk info only)
     */
    public function getDifferencesCountAttribute()
    {
        return is_array($this->differences) ? count($this->differences) : 0;
    }

    /**
     * Get jumlah perbedaan yang akan di-apply (exclude info only)
     */
    public function getApplicableDifferencesCountAttribute()
    {
        if (!is_array($this->differences)) {
            return 0;
        }

        $count = 0;
        foreach ($this->differences as $field => $diff) {
            // Skip field yang hanya informasi
            if (!isset($diff['is_info_only']) || !$diff['is_info_only']) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Scope untuk filter sync yang berhasil
     */
    public function scopeSuccessful($query)
    {
        return $query->where('sync_status', 'success');
    }

    /**
     * Scope untuk filter yang memiliki perbedaan
     */
    public function scopeHasDifferences($query)
    {
        return $query->where('has_differences', true);
    }

    /**
     * Scope untuk filter yang belum di-apply
     */
    public function scopeNotApplied($query)
    {
        return $query->whereNull('last_applied_at');
    }
}
