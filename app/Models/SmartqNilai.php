<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasUuid;

class SmartqNilai extends Model
{
    use HasUuid;

    protected $table = 'smartq_nilais';

    protected $fillable = [
        'smartq_peserta_id',
        'smartq_komponen_nilai_id',
        'nilai',
        'nilai_konversi',
        'catatan',
        'moodle_attempt_id',
        'moodle_username',
        'dinilai_oleh',
        'dinilai_pada',
    ];

    protected $casts = [
        'nilai' => 'decimal:2',
        'nilai_konversi' => 'decimal:2',
        'moodle_attempt_id' => 'integer',
        'dinilai_pada' => 'datetime',
    ];

    public function peserta()
    {
        return $this->belongsTo(SmartqPeserta::class, 'smartq_peserta_id');
    }

    public function komponenNilai()
    {
        return $this->belongsTo(SmartqKomponenNilai::class, 'smartq_komponen_nilai_id');
    }

    public function penilai()
    {
        return $this->belongsTo(User::class, 'dinilai_oleh');
    }
}
