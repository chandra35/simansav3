<?php

namespace App\Traits;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

trait HasActivityLog
{
    public static function bootHasActivityLog()
    {
        static::created(function ($model) {
            static::logActivity('create', $model, null, $model->toArray());
        });

        static::updated(function ($model) {
            $original = $model->getOriginal();
            $changes = $model->getChanges();
            
            if (!empty($changes)) {
                static::logActivity('update', $model, $original, $changes);
            }
        });

        static::deleted(function ($model) {
            static::logActivity('delete', $model, $model->toArray(), null);
        });
    }

    protected static function logActivity(string $type, $model, $old = null, $new = null)
    {
        if (!Auth::check()) {
            return;
        }

        $description = static::getActivityDescription($type, $model);

        ActivityLog::create([
            'user_id' => Auth::id(),
            'activity_type' => $type,
            'model_type' => get_class($model),
            'model_id' => $model->getKey(),
            'description' => $description,
            'properties' => [
                'old' => $old,
                'new' => $new,
            ],
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }

    protected static function getActivityDescription(string $type, $model): string
    {
        $modelName = class_basename(get_class($model));
        $identifier = $model->name ?? $model->nama_lengkap ?? $model->username ?? $model->getKey();

        switch ($type) {
            case 'create':
                return "Membuat {$modelName} baru: {$identifier}";
            case 'update':
                return "Mengubah {$modelName}: {$identifier}";
            case 'delete':
                return "Menghapus {$modelName}: {$identifier}";
            default:
                return "Aktivitas {$type} pada {$modelName}: {$identifier}";
        }
    }

    public static function logCustomActivity(string $type, string $description, $model = null)
    {
        if (!Auth::check()) {
            return;
        }

        ActivityLog::create([
            'user_id' => Auth::id(),
            'activity_type' => $type,
            'model_type' => $model ? get_class($model) : null,
            'model_id' => $model ? $model->getKey() : null,
            'description' => $description,
            'properties' => null,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }
}