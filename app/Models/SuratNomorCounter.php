<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class SuratNomorCounter extends Model
{
    use HasUuids;

    protected $table = 'surat_nomor_counters';

    protected $fillable = ['tahun', 'nomor_terakhir'];

    protected $casts = ['tahun' => 'integer', 'nomor_terakhir' => 'integer'];
}
