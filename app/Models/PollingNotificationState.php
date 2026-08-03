<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class PollingNotificationState extends Model
{
    use HasUuid;

    protected $fillable = ['polling_id', 'user_id', 'last_prompted_at', 'snoozed_until', 'dismiss_count'];

    protected $casts = ['last_prompted_at' => 'datetime', 'snoozed_until' => 'datetime'];
}
