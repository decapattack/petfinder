<?php

use App\Http\Middleware\ContentSecurityPolicy;
use App\Http\Middleware\HealthCheckToken;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            ContentSecurityPolicy::class,
        ]);

        // Alias para o middleware de proteção do health check
        $middleware->alias([
            'health.token' => HealthCheckToken::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
