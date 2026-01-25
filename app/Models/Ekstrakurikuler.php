<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Ekstrakurikuler extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'ekstrakurikuler';

    protected $fillable = [
        'tahun_pelajaran_id',
        'nama',
        'kode',
        'deskripsi',
        'kategori',
        'jenis',
        'pembina_id',
        'hari_latihan',
        'jam_mulai',
        'jam_selesai',
        'tempat',
        'kuota',
        'biaya',
        'foto',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'jam_mulai' => 'datetime:H:i',
        'jam_selesai' => 'datetime:H:i',
        'hari_latihan' => 'array',
        'kuota' => 'integer',
        'biaya' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    // Relations
    public function tahunPelajaran()
    {
        return $this->belongsTo(TahunPelajaran::class, 'tahun_pelajaran_id');
    }

    public function pembina()
    {
        return $this->belongsTo(Gtk::class, 'pembina_id');
    }

    public function anggota()
    {
        return $this->hasMany(EkstrakurikulerAnggota::class, 'ekstrakurikuler_id');
    }

    public function anggotaAktif()
    {
        return $this->hasMany(EkstrakurikulerAnggota::class, 'ekstrakurikuler_id')
            ->where('status', 'aktif');
    }

    public function siswa()
    {
        return $this->belongsToMany(Siswa::class, 'ekstrakurikuler_anggota', 'ekstrakurikuler_id', 'siswa_id')
            ->withPivot(['status', 'jabatan', 'nilai_ekskul', 'predikat'])
            ->withTimestamps();
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeWajib($query)
    {
        return $query->where('kategori', 'wajib');
    }

    public function scopePilihan($query)
    {
        return $query->where('kategori', 'pilihan');
    }

    // Accessors
    public function getJumlahAnggotaAttribute()
    {
        return $this->anggotaAktif()->count();
    }

    public function getSisaKuotaAttribute()
    {
        if ($this->kuota == 0) return 'Unlimited';
        return max(0, $this->kuota - $this->jumlah_anggota);
    }
}
