<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class TugasTambahan extends Model
{
    use HasFactory;

    protected $table = 'tugas_tambahan';

    protected $fillable = [
        'user_id',
        'role_id',
        'is_active',
        'mulai_tugas',
        'selesai_tugas',
        'sk_number',
        'sk_date',
        'keterangan',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'mulai_tugas' => 'date',
        'selesai_tugas' => 'date',
        'sk_date' => 'date',
    ];

    /**
     * Boot function to auto-generate UUID and set audit fields
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (auth()->check()) {
                $model->created_by = auth()->id();
            }
        });

        static::updating(function ($model) {
            if (auth()->check()) {
                $model->updated_by = auth()->id();
            }
        });
    }

    /**
     * Relationship: TugasTambahan belongs to User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relationship: TugasTambahan belongs to Role
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(\Spatie\Permission\Models\Role::class, 'role_id');
    }

    /**
     * Relationship: Created by user
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relationship: Updated by user
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Scope: Only active tugas tambahan
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Only inactive/completed tugas tambahan
     */
    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    /**
     * Scope: Tugas tambahan for specific role
     */
    public function scopeForRole($query, $roleName)
    {
        return $query->whereHas('role', function ($q) use ($roleName) {
            $q->where('name', $roleName);
        });
    }

    /**
     * Scope: Tugas tambahan for specific user
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Check if this tugas tambahan is still active
     */
    public function isActive(): bool
    {
        return $this->is_active && (is_null($this->selesai_tugas) || $this->selesai_tugas->isFuture());
    }

    /**
     * Deactivate this tugas tambahan
     */
    public function deactivate(?string $reason = null)
    {
        $this->update([
            'is_active' => false,
            'selesai_tugas' => now(),
            'keterangan' => $reason ? $this->keterangan . ' | Dinonaktifkan: ' . $reason : $this->keterangan,
        ]);
    }

    /**
     * Reactivate this tugas tambahan
     */
    public function activate()
    {
        $this->update([
            'is_active' => true,
            'selesai_tugas' => null,
        ]);
    }

    /**
     * Get formatted period string
     */
    public function getPeriodAttribute(): string
    {
        $start = $this->mulai_tugas ? $this->mulai_tugas->format('d M Y') : 'N/A';
        $end = $this->selesai_tugas ? $this->selesai_tugas->format('d M Y') : 'Sekarang';
        
        return "{$start} - {$end}";
    }

    /**
     * Get formatted status badge
     */
    public function getStatusBadgeAttribute(): string
    {
        return $this->is_active 
            ? '<span class="badge badge-success">Aktif</span>' 
            : '<span class="badge badge-secondary">Nonaktif</span>';
    }
}
