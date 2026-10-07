<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: [__DIR__.'/../routes/web.php', __DIR__.'/../routes/fitur-baru.php'],
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withProviders([
        \App\Providers\NotifikasiServiceProvider::class,
    ])
    ->withMiddleware(function (Middleware $middleware) {
        // Di Vercel isi TRUSTED_PROXIES=* agar alamat https & IP pengunjung terbaca benar. Di laptop biarkan kosong.
        if (env('TRUSTED_PROXIES')) $middleware->trustProxies(at: env('TRUSTED_PROXIES'));
        $middleware->append(\App\Http\Middleware\HeaderKeamanan::class);
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
        ]);
        $middleware->redirectGuestsTo(fn () => route('login'));
        $middleware->redirectUsersTo(fn ($request) => route($request->user()->dashboardRoute()));
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
