<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MatrikulasiKelompok extends Model
{
    use HasUuid, SoftDeletes;

    protected $table = 'matrikulasi_kelompoks';

    protected $fillable = [
        'matrikulasi_periode_id',
        'nama',
        'kode',
        'tingkat_kelas',
        'jenis_kelompok',
        'kapasitas',
        'pembina_id',
        'status',
        'catatan',
    ];

    public function getLabelKelasAttribute(): string
    {
        $jenis = strtoupper((string) ($this->jenis_kelompok ?: 'reguler'));
        $tingkat = strtoupper((string) ($this->tingkat_kelas ?: $this->kode ?: ''));

        return trim($jenis . ' ' . $tingkat) ?: $this->nama;
    }

    public function periode()
    {
        return $this->belongsTo(MatrikulasiPeriode::class, 'matrikulasi_periode_id');
    }

    public function pesertas()
    {
        return $this->hasMany(MatrikulasiPeserta::class, 'matrikulasi_kelompok_id');
    }

    public function pembina()
    {
        return $this->belongsTo(Gtk::class, 'pembina_id');
    }
}
