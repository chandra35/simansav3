<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class AbsensiSiswaAudit extends Model
{
    use HasUuids;

    protected $fillable = [
        'session_id', 'record_id', 'siswa_id', 'actor_user_id', 'action',
        'before_values', 'after_values', 'reason', 'ip_address', 'user_agent',
    ];

    protected $casts = [
        'before_values' => 'array',
        'after_values' => 'array',
    ];

    public function session()
    {
        return $this->belongsTo(AbsensiSiswaSession::class, 'session_id');
    }

    public function record()
    {
        return $this->belongsTo(AbsensiSiswaRecord::class, 'record_id');
    }

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }
}
