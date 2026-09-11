<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MoodleCheckResult extends Model
{
    protected $fillable = ['moodle_check_run_id', 'local_id', 'identifier', 'local_name', 'local_group', 'moodle_name', 'moodle_email', 'status', 'payload'];
    protected $casts = ['payload' => 'array'];

    public function run() { return $this->belongsTo(MoodleCheckRun::class, 'moodle_check_run_id'); }
}
