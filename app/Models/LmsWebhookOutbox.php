<?php
namespace App\Models; use Illuminate\Database\Eloquent\Model; use Illuminate\Database\Eloquent\Concerns\HasUuids;
class LmsWebhookOutbox extends Model { use HasUuids; protected $table='lms_webhook_outbox'; protected $fillable=['event_type','payload','attempts','available_at','delivered_at','last_error']; protected function casts(): array { return ['payload'=>'array','available_at'=>'datetime','delivered_at'=>'datetime']; } }
