<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SholatSetting extends Model
{
    use HasUuids;

    protected $table = 'sholat_settings';

    protected $fillable = [
        'kode',
        'nama',
        'is_active',
        'jam_mulai',
        'jam_selesai',
        'hari_aktif',
        'target_gender',
        'urutan',
        'keterangan',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'hari_aktif' => 'array',
        'urutan' => 'integer',
    ];

    public function sessions(): HasMany
    {
        return $this->hasMany(SholatSession::class, 'sholat_setting_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('urutan')->orderBy('nama');
    }

    public function isAvailableOnDay(string $dayKey): bool
    {
        $dayKey = strtolower($dayKey);
        if (empty($this->hari_aktif)) {
            return true;
        }

        return in_array($dayKey, array_map('strtolower', (array) $this->hari_aktif));
    }

    public function isAvailableForGender(?string $gender): bool
    {
        if (empty($this->target_gender) || $this->target_gender === 'all') {
            return true;
        }

        return strtoupper((string) $gender) === strtoupper($this->target_gender);
    }

    public function getNamaSholatAttribute(): string
    {
        return $this->nama ?? '';
    }

    public function getGenderTargetAttribute(): string
    {
        return $this->target_gender ?? 'all';
    }

    public function getBatasMasbuqAttribute(): ?string
    {
        return $this->jam_selesai;
    }

    public function getDeskripsiAttribute(): ?string
    {
        return $this->keterangan;
    }
}
