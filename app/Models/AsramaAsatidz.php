<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AsramaAsatidz extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'asrama_asatidz';

    protected $fillable = [
        'asrama_id', 'gtk_id', 'nomor_identitas', 'jabatan', 'dapat_mengasuh_rombel',
        'dapat_mengasuh_kamar', 'dapat_mengampu_mapel', 'tanggal_mulai',
        'tanggal_selesai', 'is_active', 'catatan', 'created_by', 'updated_by',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date', 'tanggal_selesai' => 'date', 'is_active' => 'boolean',
        'dapat_mengasuh_rombel' => 'boolean', 'dapat_mengasuh_kamar' => 'boolean',
        'dapat_mengampu_mapel' => 'boolean',
    ];

    public function asrama()
    {
        return $this->belongsTo(Asrama::class);
    }

    public function gtk()
    {
        return $this->belongsTo(Gtk::class);
    }

    public function kelasWali()
    {
        return $this->hasMany(AsramaKelas::class, 'wali_asatidz_id');
    }

    public function pengampu()
    {
        return $this->hasMany(AsramaPengampu::class);
    }

    public function rombelDiasuh()
    {
        return $this->hasMany(AsramaRombelPengasuh::class);
    }

    public function kamarDiasuh()
    {
        return $this->hasMany(AsramaKamar::class, 'pengasuh_asatidz_id');
    }
}
