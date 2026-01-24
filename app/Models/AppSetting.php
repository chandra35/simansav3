<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Support\Facades\Storage;

class AppSetting extends Model
{
    use HasUuids;

    protected $table = 'app_settings';

    protected $fillable = [
        'nama_sekolah',
        'npsn',
        'logo_kemenag_path',
        'logo_kemenag_height',
        'logo_sekolah_path',
        'logo_sekolah_height',
        'logo_display_height',
        'logo_column_width',
        'alamat',
        'rt',
        'rw',
        'kelurahan_code',
        'kecamatan_code',
        'kota_code',
        'provinsi_code',
        'kode_pos',
        'telepon',
        'email',
        'website',
        'facebook_url',
        'instagram_url',
        'youtube_url',
        'twitter_url',
        'kop_mode',
        'kop_surat_config',
        'kop_surat_custom_path',
        'kop_margin_top',
        'kop_height',
    ];

    protected $casts = [
        'kop_surat_config' => 'array',
        'kop_margin_top' => 'integer',
        'kop_height' => 'integer',
        'logo_kemenag_height' => 'integer',
        'logo_sekolah_height' => 'integer',
        'logo_display_height' => 'integer',
        'logo_column_width' => 'integer',
    ];

    /**
     * Singleton Pattern - Get or Create Instance
     */
    public static function getInstance()
    {
        $instance = self::first();
        
        if (!$instance) {
            $instance = self::create([
                'nama_sekolah' => 'Nama Sekolah',
                'npsn' => '00000000',
                'alamat' => 'Alamat Sekolah',
                'kelurahan_code' => '5371010001',
                'kecamatan_code' => '5371010',
                'kota_code' => '5371',
                'provinsi_code' => '53',
                'kode_pos' => '85000',
                'telepon' => '0000000000',
                'email' => 'info@sekolah.sch.id',
                'kop_mode' => 'builder',
            ]);
        }
        
        return $instance;
    }

    /**
     * Relationships
     */
    public function provinsi()
    {
        return $this->belongsTo(\Laravolt\Indonesia\Models\Province::class, 'provinsi_code', 'code');
    }

    public function kota()
    {
        return $this->belongsTo(\Laravolt\Indonesia\Models\City::class, 'kota_code', 'code');
    }

    public function kecamatan()
    {
        return $this->belongsTo(\Laravolt\Indonesia\Models\District::class, 'kecamatan_code', 'code');
    }

    public function kelurahan()
    {
        return $this->belongsTo(\Laravolt\Indonesia\Models\Village::class, 'kelurahan_code', 'code');
    }

    /**
     * Accessors
     */
    public function getLogoKemenagUrlAttribute()
    {
        if ($this->logo_kemenag_path) {
            return Storage::url($this->logo_kemenag_path);
        }
        return asset('vendor/adminlte/dist/img/logo-kemenag.png'); // Default logo
    }

    public function getLogoSekolahUrlAttribute()
    {
        if ($this->logo_sekolah_path) {
            return Storage::url($this->logo_sekolah_path);
        }
        return asset('vendor/adminlte/dist/img/logo-sekolah.png'); // Default logo
    }

    public function getKopSuratCustomUrlAttribute()
    {
        if ($this->kop_surat_custom_path) {
            return Storage::url($this->kop_surat_custom_path);
        }
        return null;
    }

    public function getAlamatLengkapAttribute()
    {
        $parts = array_filter([
            $this->alamat,
            $this->rt ? "RT {$this->rt}" : null,
            $this->rw ? "RW {$this->rw}" : null,
            $this->kelurahan?->name,
            $this->kecamatan?->name,
            $this->kota?->name,
            $this->provinsi?->name,
            $this->kode_pos,
        ]);
        
        return implode(', ', $parts);
    }

    public function getAlamatSingkatAttribute()
    {
        $parts = array_filter([
            $this->alamat,
            $this->kota?->name,
            $this->provinsi?->name,
        ]);
        
        return implode(', ', $parts);
    }

    /**
     * Get Kepala Sekolah (from active Tugas Tambahan)
     */
    public function getKepalaSekolah()
    {
        return User::role('Kepala Madrasah')
            ->whereHas('tugasTambahan', function($query) {
                $query->where('is_active', true)
                      ->whereHas('role', function($q) {
                          $q->where('name', 'Kepala Madrasah');
                      });
            })
            ->first();
    }

    /**
     * Get Kepala Sekolah dengan Tugas Tambahan info
     */
    public function getKepalaSekolahWithTugas()
    {
        $kepala = $this->getKepalaSekolah();
        
        if (!$kepala) {
            return null;
        }
        
        $tugas = $kepala->tugasTambahan()
            ->where('is_active', true)
            ->whereHas('role', function($q) {
                $q->where('name', 'Kepala Madrasah');
            })
            ->first();
        
        return [
            'user' => $kepala,
            'tugas' => $tugas,
        ];
    }
}
