<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class FaceEncoding extends Model
{
    use SoftDeletes;

    protected $table = 'face_encodings';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'user_id', 'user_type', 'descriptors', 'capture_angles',
        'total_captures', 'quality_score', 'is_active', 'is_verified',
        'verified_by', 'verified_at', 'last_used_at',
    ];

    protected $casts = [
        'descriptors' => 'array',
        'capture_angles' => 'array',
        'is_active' => 'boolean',
        'is_verified' => 'boolean',
        'verified_at' => 'datetime',
        'last_used_at' => 'datetime',
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

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Check if face encoding is ready for matching
     */
    public function isReady(): bool
    {
        return $this->is_active && !empty($this->descriptors) && count($this->descriptors) > 0;
    }

    /**
     * Get all active face encodings for a user type (for matching)
     */
    public static function getActiveDescriptors(string $userType = 'gtk')
    {
        return static::where('user_type', $userType)
            ->where('is_active', true)
            ->with('user:id,name')
            ->get()
            ->map(function ($face) {
                return [
                    'user_id' => $face->user_id,
                    'name' => $face->user->name,
                    'descriptors' => $face->descriptors,
                ];
            });
    }
}
