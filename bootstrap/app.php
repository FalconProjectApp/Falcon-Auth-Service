<?php

declare(strict_types = 1);

use App\Http\Middleware\AppMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
        using: require base_path('routes/routesConfig.php'),
    )
    ->withMiddleware(new AppMiddleware())
    ->withEvents(discover: [
        __DIR__ . '/../app/Domain/Listeners',
    ])
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
