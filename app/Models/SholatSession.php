<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SholatSession extends Model
{
    use HasUuids;

    protected $table = 'sholat_sessions';

    protected $fillable = [
        'tanggal',
        'sholat_setting_id',
        'kelas_id',
        'tahun_pelajaran_id',
        'semester',
        'status',
        'recorded_by',
        'catatan',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'semester' => 'integer',
    ];

    public function sholatSetting(): BelongsTo
    {
        return $this->belongsTo(SholatSetting::class, 'sholat_setting_id');
    }

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }

    public function tahunPelajaran(): BelongsTo
    {
        return $this->belongsTo(TahunPelajaran::class, 'tahun_pelajaran_id');
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function records(): HasMany
    {
        return $this->hasMany(SholatRecord::class, 'sholat_session_id');
    }
}
