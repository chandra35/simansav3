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
        'nsm',
        'nama',
        'status',
        'bentuk_pendidikan',
        'jenjang_pendidikan',
        'kementerian_pembina',
        'npyp',
        'no_sk_pendirian',
        'tanggal_sk_pendirian',
        'no_sk_operasional',
        'tanggal_sk_operasional',
        'akreditasi',
        'luas_tanah',
        'akses_internet',
        'sumber_listrik',
        'alamat_jalan',
        'rt',
        'rw',
        'desa_kelurahan',
        'kecamatan',
        'kabupaten_kota',
        'provinsi',
        'kode_pos',
        'telepon',
        'email',
        'website',
        'operator',
        'lintang',
        'bujur',
        'sumber_data_sekolah',
        'last_fetched_at',
    ];
    
    protected $casts = [
        'last_fetched_at' => 'datetime',
        'lintang' => 'decimal:8',
        'bujur' => 'decimal:8',
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
