<?php

namespace App\Models;

use App\Traits\HasActivityLog;
use App\Traits\HasCreatedUpdatedBy;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class RelasiKeluarga extends Model
{
    use HasActivityLog, HasCreatedUpdatedBy, HasUuid;

    protected $table = 'relasi_keluarga';
    protected $guarded = [];
    protected $casts = ['bukti_kecocokan' => 'array', 'diverifikasi_pada' => 'datetime'];

    public function siswa() { return $this->belongsTo(Siswa::class, 'siswa_id'); }
    public function siswaTerkait() { return $this->belongsTo(Siswa::class, 'siswa_terkait_id'); }
    public function gtk() { return $this->belongsTo(Gtk::class); }
}
