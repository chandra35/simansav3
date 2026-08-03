<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class PollingTarget extends Model
{
    use HasUuid;

    protected $fillable = ['polling_id', 'audience_type', 'scope_type', 'scope_value'];

    public function polling() { return $this->belongsTo(Polling::class); }
}
