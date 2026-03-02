<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            'force.password' => \App\Http\Middleware\ForcePasswordChange::class,
        ]);
        
        // Exclude ExaManmet API routes from CSRF verification
        // These are called from the Flutter mobile app (no browser session/cookies)
        $middleware->validateCsrfTokens(except: [
            'api/exam-browser/*',
        ]);
        
        // Track user activity untuk authenticated users
        $middleware->appendToGroup('web', \App\Http\Middleware\TrackUserActivity::class);
        
        // Force password change for siswa
        $middleware->appendToGroup('web', \App\Http\Middleware\ForcePasswordChange::class);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
