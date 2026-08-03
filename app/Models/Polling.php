<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Polling extends Model
{
    use HasUuid, SoftDeletes;

    protected $fillable = [
        'slug', 'title', 'description', 'audience', 'status', 'starts_at', 'ends_at',
        'allow_changes', 'show_results_after_submit', 'require_consent', 'consent_text',
        'reminder_interval_hours', 'published_at', 'created_by', 'updated_by',
        'tahun_pelajaran_id', 'tahun_pelajaran_snapshot', 'semester_snapshot', 'source_polling_id',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'published_at' => 'datetime',
            'allow_changes' => 'boolean',
            'show_results_after_submit' => 'boolean',
            'require_consent' => 'boolean',
        ];
    }

    public function questions()
    {
        return $this->hasMany(PollingQuestion::class)->orderBy('sort_order');
    }

    public function targets()
    {
        return $this->hasMany(PollingTarget::class);
    }

    public function responses()
    {
        return $this->hasMany(PollingResponse::class);
    }

    public function notificationStates()
    {
        return $this->hasMany(PollingNotificationState::class);
    }

    public function tahunPelajaran()
    {
        return $this->belongsTo(TahunPelajaran::class);
    }

    public function sourcePolling()
    {
        return $this->belongsTo(self::class, 'source_polling_id')->withTrashed();
    }

    public function isOpen(): bool
    {
        return $this->status === 'published'
            && $this->starts_at->lte(now())
            && $this->ends_at->gte(now());
    }

    public function getPhaseAttribute(): string
    {
        if ($this->status === 'draft') return 'draft';
        if ($this->status === 'closed' || $this->ends_at->isPast()) return 'closed';
        if ($this->starts_at->isFuture()) return 'scheduled';
        return 'open';
    }
}
