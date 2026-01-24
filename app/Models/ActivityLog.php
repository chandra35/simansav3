<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasUuid;

class ActivityLog extends Model
{
    use HasUuid;

    protected $fillable = [
        'user_id',
        'activity_type',
        'model_type',
        'model_id',
        'description',
        'properties',
        'ip_address',
        'user_agent',
        // Device & Browser
        'device_type',
        'browser',
        'browser_version',
        'platform',
        'platform_version',
        // Geo Location
        'country',
        'country_code',
        'region',
        'city',
        'postal_code',
        'latitude',
        'longitude',
        'timezone',
        // Change Tracking
        'old_values',
        'new_values',
        'changed_fields',
        // Additional
        'url',
        'method',
        'notes',
    ];

    protected $casts = [
        'properties' => 'array',
        'old_values' => 'array',
        'new_values' => 'array',
        'changed_fields' => 'array',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getModel()
    {
        if ($this->model_type && $this->model_id) {
            return $this->model_type::find($this->model_id);
        }
        return null;
    }
}
