<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MoodleSyncRun extends Model
{
    protected $fillable = ['moodle_integration_id', 'started_by', 'type', 'status', 'summary', 'error', 'started_at', 'finished_at'];
    protected $casts = ['summary' => 'array', 'started_at' => 'datetime', 'finished_at' => 'datetime'];

    public function integration() { return $this->belongsTo(MoodleIntegration::class, 'moodle_integration_id'); }
    public function items() { return $this->hasMany(MoodleSyncItem::class); }
}
