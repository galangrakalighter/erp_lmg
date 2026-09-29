<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->validateCsrfTokens(except: [
            'oauth/threads/deauthorize',
            'oauth/threads/data-deletion',
        ]);
        $middleware->appendToGroup('web', \App\Http\Middleware\EnsureAccountIsUnlocked::class);
        $middleware->appendToGroup('web', \App\Http\Middleware\LogPageVisit::class);
        $middleware->appendToGroup('web', \App\Http\Middleware\ProtectMultimediaSharedModules::class);
        $middleware->alias([
            'multimedia.client' => \App\Http\Middleware\EnsureMultimediaClientAccess::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
