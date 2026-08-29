<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/v1/openapi.json', [App\Http\Controllers\Api\V1\OpenApiController::class, 'show'])->name('v1.openapi');

Route::prefix('v1/lms')->middleware(['auth:sanctum', 'abilities:lms:read', 'throttle:60,1'])->group(function () {
    Route::get('/students', [App\Http\Controllers\Api\V1\LmsSyncController::class, 'students']);
    Route::get('/teachers', [App\Http\Controllers\Api\V1\LmsSyncController::class, 'teachers']);
});
