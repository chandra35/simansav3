<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Traits\HasUuid;

class CustomMenuSiswa extends Model
{
    use HasUuid;

    protected $table = 'custom_menu_siswa';

    protected $fillable = [
        'custom_menu_id',
        'siswa_id',
        'personal_data',
        'is_read',
        'read_at',
    ];

    protected $casts = [
        'personal_data' => 'array',
        'is_read' => 'boolean',
        'read_at' => 'datetime',
    ];

    /**
     * Relationship: Pivot belongs to custom menu
     */
    public function customMenu()
    {
        return $this->belongsTo(CustomMenu::class, 'custom_menu_id');
    }

    /**
     * Relationship: Pivot belongs to siswa
     */
    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }

    /**
     * Mark as read
     */
    public function markAsRead()
    {
        if (!$this->is_read) {
            $this->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
        }
    }

    /**
     * Get decrypted personal data value
     */
    public function getDecryptedData($key)
    {
        if (!$this->personal_data || !isset($this->personal_data[$key])) {
            return null;
        }

        $value = $this->personal_data[$key];

        // Try to decrypt if it looks like encrypted data
        try {
            if (is_string($value) && Str::startsWith($value, 'eyJpdiI6')) {
                return decrypt($value);
            }
        } catch (\Exception $e) {
            // Not encrypted or decryption failed, return as is
        }

        return $value;
    }
}
