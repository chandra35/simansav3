<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Traits\HasUuid;

class CustomMenu extends Model
{
    use HasUuid;

    protected $fillable = [
        'judul',
        'slug',
        'icon',
        'menu_group',
        'content_type',
        'konten',
        'custom_fields',
        'urutan',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'custom_fields' => 'array',
        'is_active' => 'boolean',
        'urutan' => 'integer',
    ];

    /**
     * Boot method to auto-generate slug
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($menu) {
            if (empty($menu->slug)) {
                $menu->slug = Str::slug($menu->judul);
            }
        });
    }

    /**
     * Relationship: Menu belongs to user (creator)
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relationship: Menu has many siswa through pivot table
     */
    public function siswaAssigned()
    {
        return $this->belongsToMany(Siswa::class, 'custom_menu_siswa', 'custom_menu_id', 'siswa_id')
            ->withPivot('personal_data', 'is_read', 'read_at')
            ->withTimestamps();
    }

    /**
     * Relationship: Menu has many custom_menu_siswa records
     */
    public function menuSiswa()
    {
        return $this->hasMany(CustomMenuSiswa::class, 'custom_menu_id');
    }

    /**
     * Scope: Only active menus
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: By group
     */
    public function scopeByGroup($query, $group)
    {
        return $query->where('menu_group', $group);
    }

    /**
     * Scope: Ordered by urutan
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('urutan')->orderBy('judul');
    }

    /**
     * Toggle active status
     */
    public function toggleStatus()
    {
        $this->is_active = !$this->is_active;
        $this->save();
        return $this->is_active;
    }

    /**
     * Assign siswa to this menu
     */
    public function assignSiswa($siswaIds, $personalData = [])
    {
        $records = [];
        foreach ($siswaIds as $siswaId) {
            $records[$siswaId] = [
                'personal_data' => isset($personalData[$siswaId]) 
                    ? json_encode($personalData[$siswaId]) 
                    : null,
            ];
        }

        $this->siswaAssigned()->syncWithoutDetaching($records);
    }

    /**
     * Remove siswa from this menu
     */
    public function removeSiswa($siswaIds)
    {
        // Delete from custom_menu_siswa pivot table
        CustomMenuSiswa::where('custom_menu_id', $this->id)
            ->whereIn('siswa_id', $siswaIds)
            ->delete();
    }

    /**
     * Get unread count for specific siswa
     */
    public function getUnreadCount($siswaId)
    {
        return $this->menuSiswa()
            ->where('siswa_id', $siswaId)
            ->where('is_read', false)
            ->count();
    }

    /**
     * Get total assigned siswa count
     */
    public function getTotalSiswaAttribute()
    {
        return $this->siswaAssigned()->count();
    }

    /**
     * Get total read count
     */
    public function getTotalReadAttribute()
    {
        return $this->menuSiswa()->where('is_read', true)->count();
    }

    /**
     * Get display label for content type
     */
    public function getContentTypeLabel()
    {
        return $this->content_type === 'general' 
            ? 'Informasi Umum' 
            : 'Informasi Personal';
    }

    /**
     * Get badge color for group
     */
    public function getGroupBadgeColor()
    {
        $colors = [
            'akademik' => 'primary',
            'administrasi' => 'info',
            'hotspot' => 'success',
            'umum' => 'secondary',
        ];

        return $colors[$this->menu_group] ?? 'secondary';
    }

    /**
     * Get custom fields as array
     * Returns array of field definitions with label, type, key, etc
     */
    public function getCustomFieldsArray()
    {
        if (empty($this->custom_fields)) {
            return [];
        }

        // If already an array, return it
        if (is_array($this->custom_fields)) {
            return $this->custom_fields;
        }

        // If JSON string, decode it
        if (is_string($this->custom_fields)) {
            return json_decode($this->custom_fields, true) ?? [];
        }

        return [];
    }
}
