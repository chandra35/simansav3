<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class JadwalHariJam extends Model
{
    use HasUuids;

    protected $table = 'jadwal_hari_jam';

    protected $fillable = [
        'tahun_pelajaran_id',
        'semester',
        'hari',
        'urutan',
        'jam_ke',
        'waktu_mulai',
        'waktu_selesai',
        'tipe',
        'label',
    ];

    protected $casts = [
        'semester' => 'integer',
        'urutan'   => 'integer',
        'jam_ke'   => 'integer',
    ];

    const HARI = ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu'];
    const TIPE = ['pelajaran', 'istirahat', 'upacara', 'khusus'];

    public function tahunPelajaran()
    {
        return $this->belongsTo(TahunPelajaran::class, 'tahun_pelajaran_id');
    }

    /** Apakah slot ini bisa diisi jadwal pelajaran (mapel/guru) */
    public function isPelajaran(): bool
    {
        return $this->tipe === 'pelajaran';
    }

    /** Label tampilan untuk slot non-pelajaran */
    public function displayLabel(): string
    {
        if ($this->label) return $this->label;
        return match($this->tipe) {
            'istirahat' => 'Istirahat',
            'upacara'   => 'Upacara',
            'khusus'    => 'Khusus',
            default     => '-',
        };
    }
}
