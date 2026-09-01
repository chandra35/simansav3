<?php
namespace App\Services; use App\Jobs\DeliverLmsWebhook; use App\Models\LmsWebhookOutbox;
class LmsWebhookOutboxService { public static function record(string $type,array $data): void { $event=LmsWebhookOutbox::create(['event_type'=>$type,'payload'=>$data,'available_at'=>now()]); DeliverLmsWebhook::dispatch($event->id); } }
