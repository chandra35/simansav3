<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MoodleCheckResult extends Model
{
    protected $fillable = ['moodle_check_run_id', 'local_id', 'identifier', 'local_name', 'local_group', 'moodle_name', 'moodle_email', 'status', 'payload', 'resolution', 'resolution_note', 'verified_by', 'verified_at'];
    protected $casts = ['payload' => 'array', 'verified_at' => 'datetime'];

    public function run() { return $this->belongsTo(MoodleCheckRun::class, 'moodle_check_run_id'); }
}
