<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MoodleSyncItem extends Model
{
    protected $fillable = ['moodle_sync_run_id', 'entity_type', 'local_id', 'identifier', 'action', 'status', 'moodle_id', 'message', 'payload'];
    protected $casts = ['payload' => 'array'];
    public function run() { return $this->belongsTo(MoodleSyncRun::class, 'moodle_sync_run_id'); }
}
