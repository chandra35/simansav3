<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class AbsensiSetting extends Model
{
    protected $table = 'absensi_settings';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'key', 'value', 'type', 'group', 'label', 'description',
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

    /**
     * Get setting value by key with optional default
     */
    public static function getValue(string $key, $default = null)
    {
        $setting = static::where('key', $key)->first();
        if (!$setting) return $default;

        return match ($setting->type) {
            'boolean' => filter_var($setting->value, FILTER_VALIDATE_BOOLEAN),
            'integer' => (int) $setting->value,
            'float' => (float) $setting->value,
            'json' => json_decode($setting->value, true),
            'time' => $setting->value,
            default => $setting->value,
        };
    }

    /**
     * Set a setting value
     */
    public static function setValue(string $key, $value): void
    {
        $setting = static::where('key', $key)->first();
        if ($setting) {
            $setting->update([
                'value' => is_array($value) ? json_encode($value) : (string) $value,
            ]);
        }
    }
}
