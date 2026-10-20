<?php

use App\Http\Middleware\IsAdmin;
use App\Http\Middleware\IsKasir;
use App\Http\Middleware\IsPelanggan;
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
            'IsAdmin' => IsAdmin::class,
            'IsKasir' => IsKasir::class,
            'IsPelanggan' => IsPelanggan::class,
        ]);

        $middleware->validateCsrfTokens(except: [
            'midtrans/webhook',
            'api/midtrans/webhook',
            'api/midtrans/notification',
            'midtrans/notification',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
