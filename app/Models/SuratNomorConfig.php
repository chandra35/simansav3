<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class SuratNomorConfig extends Model
{
    use HasUuids;

    protected $table = 'surat_nomor_configs';

    protected $fillable = [
        'kode', 'nama', 'prefix', 'kode_satuan_kerja', 'kode_klasifikasi',
        'format_nomor', 'panjang_nomor', 'is_active',
    ];

    protected $casts = ['panjang_nomor' => 'integer', 'is_active' => 'boolean'];
}
