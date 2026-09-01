<?php
namespace App\Observers;
use App\Services\LmsWebhookOutboxService;
use Illuminate\Database\Eloquent\Model;
class LmsWebhookObserver { public function created(Model $model): void { $this->record($model,'created'); } public function updated(Model $model): void { $this->record($model,'updated'); } public function deleted(Model $model): void { $this->record($model,'deleted'); } private function record(Model $model,string $action): void { LmsWebhookOutboxService::record('lms.'.class_basename($model).'.'.$action,['id'=>$model->getKey(),'updated_at'=>optional($model->updated_at)->toISOString()]); } }
