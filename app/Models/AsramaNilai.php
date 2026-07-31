<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AsramaNilai extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'asrama_nilai';

    protected $fillable = [
        'asrama_pengampu_id', 'asrama_kelas_santri_id', 'nilai',
        'catatan', 'input_by', 'input_at',
    ];

    protected $casts = ['nilai' => 'decimal:2', 'input_at' => 'datetime'];

    public function pengampu()
    {
        return $this->belongsTo(AsramaPengampu::class, 'asrama_pengampu_id');
    }

    public function kelasSantri()
    {
        return $this->belongsTo(AsramaKelasSantri::class, 'asrama_kelas_santri_id');
    }

    public function penginput()
    {
        return $this->belongsTo(User::class, 'input_by');
    }
}
