<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Asrama extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'asrama_units';

    protected $fillable = [
        'kode', 'nama', 'jenis', 'kepala_gtk_id', 'alamat', 'telepon',
        'deskripsi', 'is_active',
    ];

    protected $casts = ['is_active' => 'boolean'];

    public function kepala()
    {
        return $this->belongsTo(Gtk::class, 'kepala_gtk_id');
    }

    public function santri()
    {
        return $this->hasMany(AsramaSantri::class);
    }

    public function asatidz()
    {
        return $this->hasMany(AsramaAsatidz::class);
    }

    public function kelas()
    {
        return $this->hasMany(AsramaKelas::class);
    }

    public function mapel()
    {
        return $this->hasMany(AsramaMapel::class);
    }
}
