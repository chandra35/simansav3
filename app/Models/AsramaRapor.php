<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class AsramaRapor extends Model
{
    use HasUuids;

    protected $table = 'asrama_rapor';

    protected $fillable = [
        'asrama_kelas_santri_id', 'semester', 'nilai_kebersihan',
        'nilai_kelakuan', 'nilai_kerajinan', 'sakit', 'izin', 'lain_lain',
        'predikat', 'keputusan', 'catatan_wali', 'tanggal_rapor',
        'tanggal_hijriah', 'status', 'snapshot', 'published_by', 'published_at',
    ];

    protected $casts = [
        'nilai_kebersihan' => 'decimal:2', 'nilai_kelakuan' => 'decimal:2',
        'nilai_kerajinan' => 'decimal:2', 'sakit' => 'integer', 'izin' => 'integer',
        'lain_lain' => 'integer', 'tanggal_rapor' => 'date', 'snapshot' => 'array',
        'published_at' => 'datetime',
    ];

    public function kelasSantri()
    {
        return $this->belongsTo(AsramaKelasSantri::class, 'asrama_kelas_santri_id');
    }

    public function penerbit()
    {
        return $this->belongsTo(User::class, 'published_by');
    }
}
