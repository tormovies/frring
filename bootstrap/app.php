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
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'user.status' => \App\Http\Middleware\CheckUserStatus::class,
            'redirect.admin' => \App\Http\Middleware\RedirectAdminToPanel::class,
        ]);
        // На продакшене: www → без www (APP_URL=https://freeringtones.ru)
        $middleware->web(prepend: [
            \App\Http\Middleware\ForceCanonicalHost::class,
        ]);
        $middleware->web(append: [
            \App\Http\Middleware\CheckUserStatus::class,
        ]);
        
        // Настройка перенаправления для auth middleware
        $middleware->redirectGuestsTo('/login');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
