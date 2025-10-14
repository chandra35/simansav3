<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sekolah extends Model
{
    protected $table = 'sekolah';
    protected $primaryKey = 'npsn';
    public $incrementing = false;
    protected $keyType = 'string';
    
    protected $fillable = [
        'npsn',
        'nama',
        'status',
        'bentuk_pendidikan',
        'alamat_jalan',
        'desa_kelurahan',
        'kecamatan',
        'kabupaten_kota',
        'provinsi',
        'last_fetched_at',
    ];
    
    protected $casts = [
        'last_fetched_at' => 'datetime',
    ];
    
    /**
     * Relasi: Sekolah memiliki banyak siswa
     */
    public function siswa()
    {
        return $this->hasMany(Siswa::class, 'npsn_asal_sekolah', 'npsn');
    }
    
    /**
     * Accessor: Mendapatkan alamat lengkap sekolah
     */
    public function getAlamatLengkapAttribute()
    {
        $parts = array_filter([
            $this->alamat_jalan,
            $this->desa_kelurahan,
            $this->kecamatan,
            $this->kabupaten_kota,
            $this->provinsi,
        ]);
        
        return implode(', ', $parts);
    }
    
    /**
     * Helper: Cek apakah data sudah usang (lebih dari 6 bulan)
     */
    public function isStale()
    {
        return $this->last_fetched_at && 
               $this->last_fetched_at->diffInMonths(now()) > 6;
    }
}
