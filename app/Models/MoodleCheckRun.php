<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MoodleCheckRun extends Model
{
    protected $fillable = ['moodle_integration_id', 'checked_by', 'subject', 'status', 'summary', 'error', 'checked_at'];
    protected $casts = ['summary' => 'array', 'checked_at' => 'datetime'];

    public function integration() { return $this->belongsTo(MoodleIntegration::class, 'moodle_integration_id'); }
    public function results() { return $this->hasMany(MoodleCheckResult::class); }
    public function user() { return $this->belongsTo(User::class, 'checked_by'); }
}
