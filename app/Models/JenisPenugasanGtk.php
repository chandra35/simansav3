<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class JenisPenugasanGtk extends Model
{
    use HasUuids;

    protected $table = 'jenis_penugasan_gtk';

    protected $fillable = [
        'kode', 'nama', 'kelompok', 'kategori', 'ekuivalensi_jtm',
        'minimal_jtm_mengajar', 'jenis_unit', 'maks_pemegang', 'wajib_sk',
        'dapat_dirangkap', 'role_id', 'dasar_hukum', 'berlaku_mulai',
        'berlaku_selesai', 'is_active', 'metadata',
    ];

    protected $casts = [
        'ekuivalensi_jtm' => 'integer',
        'minimal_jtm_mengajar' => 'integer',
        'maks_pemegang' => 'integer',
        'wajib_sk' => 'boolean',
        'dapat_dirangkap' => 'boolean',
        'is_active' => 'boolean',
        'berlaku_mulai' => 'date',
        'berlaku_selesai' => 'date',
        'metadata' => 'array',
    ];

    public function assignments()
    {
        return $this->hasMany(PenugasanGtk::class, 'jenis_penugasan_id');
    }

    public function role()
    {
        return $this->belongsTo(\Spatie\Permission\Models\Role::class, 'role_id');
    }
}
