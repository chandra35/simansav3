<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class SuratMasukSetting extends Model
{
    use HasUuid;

    protected $table = 'surat_masuk_settings';

    protected $fillable = ['nomor_terakhir', 'updated_by'];

    protected $casts = ['nomor_terakhir' => 'integer'];

    public static function current(): self
    {
        return static::query()->firstOrCreate([], ['nomor_terakhir' => 360]);
    }
}
