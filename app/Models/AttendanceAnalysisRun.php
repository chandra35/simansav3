<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class AttendanceAnalysisRun extends Model
{
    use HasUuids;

    protected $fillable = [
        'tahun_pelajaran_id', 'actor_user_id', 'source', 'status', 'result',
        'ip_address', 'user_agent',
    ];

    protected $casts = ['result' => 'array'];

    public function tahunPelajaran()
    {
        return $this->belongsTo(TahunPelajaran::class);
    }

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }
}
