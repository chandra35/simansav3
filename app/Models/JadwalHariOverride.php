<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class JadwalHariOverride extends Model
{
    use HasUuids;

    protected $fillable = ['tahun_pelajaran_id', 'hari', 'jam_pulang'];
}
