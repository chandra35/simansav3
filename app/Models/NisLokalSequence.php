<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NisLokalSequence extends Model
{
    protected $fillable = [
        'nsm',
        'tahun_masuk',
        'nomor_terakhir',
    ];

    protected $casts = [
        'tahun_masuk' => 'integer',
        'nomor_terakhir' => 'integer',
    ];
}
