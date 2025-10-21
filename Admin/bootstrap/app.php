<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {

        // Giữ cấu hình CORS của bạn
        $middleware->api(prepend: [
            \Illuminate\Http\Middleware\HandleCors::class,
        ]);

        // Giữ middleware tự động hủy vé
        $middleware->api(append: [
            \App\Http\Middleware\AutoCancelTicketsMiddleware::class,
        ]);

        // Giữ alias middleware từ nhánh ducx
        $middleware->alias([
            'email.verified' => \App\Http\Middleware\EnsureEmailIsVerified::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->create();