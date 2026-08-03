<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class PollingAnswer extends Model
{
    use HasUuid;

    protected $fillable = ['polling_response_id', 'polling_question_id', 'answer_text'];

    public function response() { return $this->belongsTo(PollingResponse::class, 'polling_response_id'); }
    public function question() { return $this->belongsTo(PollingQuestion::class, 'polling_question_id'); }
    public function options()
    {
        return $this->belongsToMany(PollingOption::class, 'polling_answer_options', 'polling_answer_id', 'polling_option_id');
    }
}
