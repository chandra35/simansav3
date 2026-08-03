<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class PollingResponse extends Model
{
    use HasUuid;

    protected $fillable = [
        'polling_id', 'user_id', 'respondent_type', 'respondent_id', 'respondent_name',
        'class_id', 'class_name', 'grade', 'submitted_at',
    ];

    protected $casts = ['submitted_at' => 'datetime'];

    public function polling() { return $this->belongsTo(Polling::class); }
    public function user() { return $this->belongsTo(User::class); }
    public function answers() { return $this->hasMany(PollingAnswer::class); }
}
