<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class JadwalPelajaran extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'jadwal_pelajaran';

    protected $fillable = [
        'tahun_pelajaran_id',
        'kelas_id',
        'mapel_id',
        'gtk_id',
        'hari',
        'jam_ke',
        'waktu_mulai',
        'waktu_selesai',
        'ruangan',
        'semester',
        'is_aktif',
    ];

    protected $casts = [
        'jam_ke' => 'integer',
        'waktu_mulai' => 'datetime:H:i',
        'waktu_selesai' => 'datetime:H:i',
        'semester' => 'integer',
        'is_aktif' => 'boolean',
    ];

    const HARI = [
        'senin' => 'Senin',
        'selasa' => 'Selasa',
        'rabu' => 'Rabu',
        'kamis' => 'Kamis',
        'jumat' => 'Jumat',
        'sabtu' => 'Sabtu',
    ];

    // Relations
    public function tahunPelajaran()
    {
        return $this->belongsTo(TahunPelajaran::class, 'tahun_pelajaran_id');
    }

    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }

    public function mapel()
    {
        return $this->belongsTo(Mapel::class, 'mapel_id');
    }

    public function gtk()
    {
        return $this->belongsTo(Gtk::class, 'gtk_id');
    }

    // Scopes
    public function scopeAktif($query)
    {
        return $query->where('is_aktif', true);
    }

    public function scopeHari($query, $hari)
    {
        return $query->where('hari', $hari);
    }

    public function scopeSemester($query, $semester)
    {
        return $query->where('semester', $semester);
    }

    // Accessors
    public function getHariLabelAttribute()
    {
        return self::HARI[$this->hari] ?? $this->hari;
    }

    public function getJamAttribute()
    {
        $mulai = $this->waktu_mulai ? $this->waktu_mulai->format('H:i') : '';
        $selesai = $this->waktu_selesai ? $this->waktu_selesai->format('H:i') : '';
        return "{$mulai} - {$selesai}";
    }
}
