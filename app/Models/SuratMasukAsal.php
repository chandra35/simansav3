<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class SuratMasukAsal extends Model
{
    use HasUuid;

    protected $table = 'surat_masuk_asal';

    protected $fillable = ['nama', 'jenis', 'sumber', 'referensi_id', 'jumlah_surat'];

    protected $casts = ['jumlah_surat' => 'integer'];
}
