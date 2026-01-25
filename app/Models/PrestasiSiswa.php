<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PrestasiSiswa extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'prestasi_siswa';

    protected $fillable = [
        'siswa_id',
        'tahun_pelajaran_id',
        'nama_prestasi',
        'deskripsi',
        'jenis',
        'tingkat',
        'peringkat',
        'penyelenggara',
        'tanggal_prestasi',
        'tempat',
        'nomor_sertifikat',
        'file_sertifikat',
        'foto',
        'pembina_id',
        'is_verified',
        'verified_by',
        'verified_at',
        'created_by',
    ];

    protected $casts = [
        'tanggal_prestasi' => 'date',
        'verified_at' => 'datetime',
        'is_verified' => 'boolean',
    ];

    // Relations
    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }

    public function tahunPelajaran()
    {
        return $this->belongsTo(TahunPelajaran::class, 'tahun_pelajaran_id');
    }

    public function pembina()
    {
        return $this->belongsTo(Gtk::class, 'pembina_id');
    }

    public function verifiedBy()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    // Scopes
    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    public function scopeJenis($query, $jenis)
    {
        return $query->where('jenis', $jenis);
    }

    public function scopeTingkat($query, $tingkat)
    {
        return $query->where('tingkat', $tingkat);
    }

    // Accessors
    public function getTingkatLabelAttribute()
    {
        return match($this->tingkat) {
            'sekolah' => 'Sekolah/Madrasah',
            'kecamatan' => 'Kecamatan',
            'kabupaten' => 'Kabupaten/Kota',
            'provinsi' => 'Provinsi',
            'nasional' => 'Nasional',
            'internasional' => 'Internasional',
            default => $this->tingkat
        };
    }

    public function getPeringkatLabelAttribute()
    {
        return match($this->peringkat) {
            'juara_1' => 'Juara 1',
            'juara_2' => 'Juara 2',
            'juara_3' => 'Juara 3',
            'harapan_1' => 'Harapan 1',
            'harapan_2' => 'Harapan 2',
            'harapan_3' => 'Harapan 3',
            'peserta' => 'Peserta',
            'finalis' => 'Finalis',
            default => $this->peringkat
        };
    }
}
