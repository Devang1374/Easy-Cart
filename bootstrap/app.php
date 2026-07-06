<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

use App\Http\Middleware\AdminMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //$middleware->trustProxies();    

        $middleware->validateCsrfTokens(except: [
            'livewire/*',
        ]);

        $middleware->alias([
            'admin' => AdminMiddleware::class
        ]);

         $middleware->redirectTo(
            guests: '/login',  // Where to send logged-out users
            users: 'user/homePage'     // Where to send logged-in users (Change this to your page!)
        );
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
    })->create();
