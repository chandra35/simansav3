<?php

namespace App\Models;

use Illuminate\\Database\\Eloquent\\Concerns\\HasUuids;
use Illuminate\\Database\\Eloquent\\Model;

class AlumniProfile extends Model
{
    use HasUuids;

    protected $table = 'alumni_profiles';

    protected $fillable = [
        'siswa_id', 'angkatan', 'tahun_lulus', 'nama_lengkap', 'nisn', 'nik', 'jenis_kelamin',
        'tempat_lahir', 'tanggal_lahir', 'nomor_hp', 'email', 'alamat', 'kabupaten_kota', 'provinsi',
        'status_setelah_lulus', 'institusi_lanjutan', 'program_studi', 'pekerjaan', 'instansi',
        'status_verifikasi', 'sumber_data', 'referensi_sumber', 'last_profile_update_at', 'catatan',
    ];

    protected $casts = [
        'tanggal_lahir' => 'date',
        'tahun_lulus' => 'integer',
        'last_profile_update_at' => 'datetime',
    ];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return [
            'kuliah' => 'Melanjutkan studi', 'bekerja' => 'Bekerja', 'wirausaha' => 'Wirausaha',
            'pesantren' => 'Pesantren', 'belum_terdata' => 'Belum terdata', 'lainnya' => 'Lainnya',
        ][$this->status_setelah_lulus] ?? 'Belum terdata';
    }
}
