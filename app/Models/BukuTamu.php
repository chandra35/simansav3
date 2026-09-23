<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BukuTamu extends Model
{
    use HasUuid, SoftDeletes;

    protected $table = 'buku_tamu';

    protected $fillable = [
        'tanggal_kunjungan',
        'nama',
        'alamat_instansi',
        'nomor_hp',
        'keperluan',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'tanggal_kunjungan' => 'date',
    ];
}
