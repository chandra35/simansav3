<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class PollingOption extends Model
{
    use HasUuid;

    protected $fillable = ['polling_question_id', 'label', 'sort_order'];

    public function question() { return $this->belongsTo(PollingQuestion::class, 'polling_question_id'); }
}
