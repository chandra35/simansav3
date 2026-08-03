<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class PollingQuestion extends Model
{
    use HasUuid;

    protected $fillable = ['polling_id', 'prompt', 'type', 'is_required', 'min_selections', 'max_selections', 'sort_order'];

    protected $casts = ['is_required' => 'boolean'];

    public function polling() { return $this->belongsTo(Polling::class); }
    public function options() { return $this->hasMany(PollingOption::class)->orderBy('sort_order'); }
    public function answers() { return $this->hasMany(PollingAnswer::class); }
}
