<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasUuid;

class SmartqKomponenNilai extends Model
{
    use HasUuid;

    protected $table = 'smartq_komponen_nilais';

    protected $fillable = [
        'smartq_periode_id',
        'nama',
        'kode',
        'bobot',
        'nilai_maksimal',
        'sumber',
        'urutan',
    ];

    protected $casts = [
        'bobot' => 'decimal:2',
        'nilai_maksimal' => 'decimal:2',
        'urutan' => 'integer',
    ];

    public function periode()
    {
        return $this->belongsTo(SmartqPeriode::class, 'smartq_periode_id');
    }

    public function nilais()
    {
        return $this->hasMany(SmartqNilai::class, 'smartq_komponen_nilai_id');
    }

    public function isMoodle(): bool
    {
        return $this->sumber === 'moodle';
    }

    public function getSumberBadgeAttribute(): string
    {
        return match($this->sumber) {
            'moodle' => '<span class="badge badge-primary"><i class="fas fa-cloud-download-alt"></i> Moodle CBT</span>',
            'manual' => '<span class="badge badge-secondary"><i class="fas fa-edit"></i> Manual</span>',
            default => '<span class="badge badge-secondary">-</span>',
        };
    }
}
