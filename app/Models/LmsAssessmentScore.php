<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LmsAssessmentScore extends Model
{
    use HasUuid;

    protected $fillable = ['siswa_id', 'external_event_id', 'assessment_type', 'assessment_title', 'subject', 'score', 'graded_at', 'payload'];
    protected function casts(): array { return ['score' => 'decimal:2', 'graded_at' => 'datetime', 'payload' => 'array']; }
    public function siswa(): BelongsTo { return $this->belongsTo(Siswa::class); }
}
