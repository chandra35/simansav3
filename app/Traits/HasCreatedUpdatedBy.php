<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;

trait HasCreatedUpdatedBy
{
    /**
     * Boot the trait
     */
    protected static function bootHasCreatedUpdatedBy()
    {
        // Set created_by when creating
        static::creating(function ($model) {
            if (Auth::check() && !$model->created_by) {
                $model->created_by = Auth::id();
            }
        });

        // Set updated_by when updating
        static::updating(function ($model) {
            if (Auth::check()) {
                $model->updated_by = Auth::id();
            }
        });
    }

    /**
     * Get the user who created this record
     */
    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    /**
     * Get the user who last updated this record
     */
    public function updater()
    {
        return $this->belongsTo(\App\Models\User::class, 'updated_by');
    }
}
