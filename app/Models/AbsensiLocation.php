<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class AbsensiLocation extends Model
{
    use SoftDeletes;

    protected $table = 'absensi_locations';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'nama', 'kode', 'alamat', 'latitude', 'longitude',
        'radius_meter', 'is_active',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'is_active' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    public function absensis()
    {
        return $this->hasMany(Absensi::class, 'location_id');
    }
}
