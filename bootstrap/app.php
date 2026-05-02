<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Trust proxy headers (needed for correct https URL generation behind ngrok/reverse proxies).
        $middleware->trustProxies(at: '*');

        $middleware->alias([
            'admin' => \App\Http\Middleware\EnsureUserIsAdmin::class,
            'prevent.cache' => \App\Http\Middleware\PreventCacheForAuthPages::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, \Illuminate\Http\Request $request) {
            if (! $request->expectsJson()) {
                return redirect()->route('landing');
            }
        });

        // 419 Page Expired — redirect back with the email so the user doesn't lose context
        $exceptions->render(function (\Illuminate\Session\TokenMismatchException $e, \Illuminate\Http\Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Session expired. Please refresh and try again.'], 419);
            }
            // If on set-password page, redirect back with email preserved
            if (str_contains($request->path(), 'set-password')) {
                $email = $request->input('email', $request->query('email', ''));
                return redirect()->route('auth.set-password', $email ? ['email' => $email] : [])
                    ->with('error', 'Your session expired. Please try again.');
            }
            // For login page, redirect to landing
            if (str_contains($request->path(), 'login') || $request->path() === '/') {
                return redirect()->route('landing')
                    ->with('error', 'Your session expired. Please try again.');
            }
            // Default: redirect back
            return redirect()->back()->with('error', 'Your session expired. Please try again.');
        });
    })->create();
