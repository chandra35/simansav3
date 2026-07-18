<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmisStudentSync extends Model
{
    use HasUuid;

    protected $fillable = [
        'institution_id',
        'status',
        'total_pages',
        'processed_pages',
        'total_students',
        'matched_students',
        'different_students',
        'error_message',
        'synced_by',
        'started_at',
        'finished_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'synced_by');
    }

    public function snapshots(): HasMany
    {
        return $this->hasMany(EmisStudentSnapshot::class, 'sync_id');
    }
}
