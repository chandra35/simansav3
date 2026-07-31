<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AsramaPengampu extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'asrama_pengampu';

    protected $fillable = [
        'asrama_kelas_id', 'asrama_mapel_id', 'asrama_asatidz_id',
        'semester', 'is_active', 'created_by', 'updated_by',
    ];

    protected $casts = ['is_active' => 'boolean'];

    public function kelas()
    {
        return $this->belongsTo(AsramaKelas::class, 'asrama_kelas_id');
    }

    public function mapel()
    {
        return $this->belongsTo(AsramaMapel::class, 'asrama_mapel_id');
    }

    public function asatidz()
    {
        return $this->belongsTo(AsramaAsatidz::class, 'asrama_asatidz_id');
    }

    public function nilai()
    {
        return $this->hasMany(AsramaNilai::class);
    }
}
