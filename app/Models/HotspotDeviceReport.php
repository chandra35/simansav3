<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HotspotDeviceReport extends Model
{
    protected $fillable = [
        'hotspot_user_id', 'username', 'mac_address', 'last_ip', 'vendor',
        'model', 'marketing_name', 'device_type', 'platform', 'platform_version',
        'browser', 'user_agent', 'client_hints', 'first_seen_at', 'last_seen_at',
    ];

    protected $casts = [
        'client_hints' => 'array',
        'first_seen_at' => 'datetime',
        'last_seen_at' => 'datetime',
    ];

    public function hotspotUser()
    {
        return $this->belongsTo(HotspotUser::class);
    }

    public function getDisplayLabelAttribute(): string
    {
        if ($this->marketing_name) {
            return $this->marketing_name;
        }

        if ($this->vendor && $this->model && ! str_contains(strtolower($this->model), strtolower($this->vendor))) {
            return trim($this->vendor.' '.$this->model);
        }

        return $this->model
            ?: match ($this->device_type) {
                'mobile' => $this->platform === 'iOS' ? 'Apple iPhone' : 'Ponsel',
                'tablet' => 'Tablet',
                'desktop' => 'Komputer / Laptop',
                default => 'Perangkat belum teridentifikasi',
            };
    }
}
