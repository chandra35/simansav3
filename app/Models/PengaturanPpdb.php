<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class PengaturanPpdb extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'pengaturan_ppdb';

    protected $fillable = [
        'tahun_pelajaran_id',
        'tanggal_buka',
        'tanggal_tutup',
        'tanggal_pengumuman',
        'tanggal_daftar_ulang_mulai',
        'tanggal_daftar_ulang_selesai',
        'persyaratan',
        'alur_pendaftaran',
        'kontak_info',
        'pendaftaran_dibuka',
        'biaya_pendaftaran',
        'rekening_pembayaran',
        'dokumen_wajib',
        'jalur_tersedia',
    ];

    protected $casts = [
        'tanggal_buka' => 'date',
        'tanggal_tutup' => 'date',
        'tanggal_pengumuman' => 'date',
        'tanggal_daftar_ulang_mulai' => 'date',
        'tanggal_daftar_ulang_selesai' => 'date',
        'pendaftaran_dibuka' => 'boolean',
        'biaya_pendaftaran' => 'decimal:2',
        'dokumen_wajib' => 'array',
        'jalur_tersedia' => 'array',
    ];

    /**
     * Get active pengaturan (singleton pattern with cache)
     */
    public static function getActive()
    {
        return Cache::remember('pengaturan_ppdb_active', 3600, function () {
            return self::where('pendaftaran_dibuka', true)
                ->orderBy('created_at', 'desc')
                ->first() ?? new self();
        });
    }

    /**
     * Clear cache when updated
     */
    protected static function booted()
    {
        static::saved(function () {
            Cache::forget('pengaturan_ppdb_active');
        });

        static::deleted(function () {
            Cache::forget('pengaturan_ppdb_active');
        });
    }

    /**
     * Check if pendaftaran is open
     */
    public function isPendaftaranDibuka(): bool
    {
        if (!$this->pendaftaran_dibuka) {
            return false;
        }

        $today = now()->startOfDay();
        
        if ($this->tanggal_buka && $today->lt($this->tanggal_buka)) {
            return false;
        }
        
        if ($this->tanggal_tutup && $today->gt($this->tanggal_tutup->endOfDay())) {
            return false;
        }

        return true;
    }

    /**
     * Get formatted biaya pendaftaran
     */
    public function getFormattedBiayaAttribute(): string
    {
        return 'Rp ' . number_format($this->biaya_pendaftaran, 0, ',', '.');
    }

    /**
     * Get periode pendaftaran string
     */
    public function getPeriodePendaftaranAttribute(): string
    {
        if (!$this->tanggal_buka || !$this->tanggal_tutup) {
            return '-';
        }
        
        return $this->tanggal_buka->format('d M Y') . ' - ' . $this->tanggal_tutup->format('d M Y');
    }

    /**
     * Relationship to tahun pelajaran
     */
    public function tahunPelajaran()
    {
        return $this->belongsTo(TahunPelajaran::class, 'tahun_pelajaran_id');
    }

    /**
     * Get default jalur tersedia
     */
    public static function getDefaultJalur(): array
    {
        return [
            'reguler' => [
                'nama' => 'Jalur Reguler',
                'deskripsi' => 'Jalur pendaftaran umum berdasarkan nilai',
                'aktif' => true,
            ],
            'prestasi' => [
                'nama' => 'Jalur Prestasi',
                'deskripsi' => 'Jalur khusus untuk siswa berprestasi',
                'aktif' => true,
            ],
            'afirmasi' => [
                'nama' => 'Jalur Afirmasi',
                'deskripsi' => 'Jalur untuk siswa dari keluarga tidak mampu',
                'aktif' => true,
            ],
            'zonasi' => [
                'nama' => 'Jalur Zonasi',
                'deskripsi' => 'Jalur berdasarkan jarak domisili ke sekolah',
                'aktif' => false,
            ],
        ];
    }

    /**
     * Get jalur list for dropdown
     */
    public function getJalurDropdown(): array
    {
        $jalur = $this->jalur_tersedia ?? self::getDefaultJalur();
        $result = [];
        
        foreach ($jalur as $key => $value) {
            if ($value['aktif'] ?? false) {
                $result[$key] = $value['nama'];
            }
        }
        
        return $result;
    }
}
