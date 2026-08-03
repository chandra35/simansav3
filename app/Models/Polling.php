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

    public static function sanitizeDescription(?string $description): string
    {
        $description = preg_replace(
            '#<(script|style|iframe|object|embed)\b[^>]*>.*?</\1>#is',
            '',
            (string) $description
        ) ?? '';
        $description = strip_tags((string) $description, '<p><br><strong><b><em><i><u><ul><ol><li><blockquote>');

        return trim(preg_replace('/<([a-z][a-z0-9]*)\b[^>]*>/i', '<$1>', $description) ?? '');
    }

    public function setDescriptionAttribute(?string $description): void
    {
        $description = self::sanitizeDescription($description);
        $this->attributes['description'] = $description !== '' ? $description : null;
    }

    public function getDescriptionHtmlAttribute(): string
    {
        $description = self::sanitizeDescription($this->description);

        return $description === strip_tags($description)
            ? nl2br(htmlspecialchars($description, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'))
            : $description;
    }

    public function getDescriptionPlainAttribute(): string
    {
        $description = preg_replace('/<\/(p|li|blockquote|ul|ol)>/i', "$0\n", self::sanitizeDescription($this->description)) ?? '';

        return trim(html_entity_decode(strip_tags($description), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
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
