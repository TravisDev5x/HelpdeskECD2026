<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Exceptions\UnauthorizedException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->trustProxies(at: '*');

        $middleware->append(\App\Http\Middleware\SecurityHeaders::class);

        $middleware->authenticateSessions();

        $middleware->web(append: [
            \App\Http\Middleware\SessionInactivity::class,
        ]);

        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            'password.expiry' => \App\Http\Middleware\CheckPasswordExpiry::class,
        ]);

        $middleware->redirectGuestsTo(fn () => route('login'));
        $middleware->redirectUsersTo('/admin/profile');
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->dontFlash([
            'current_password',
            'password',
            'password_confirmation',
            'token',
            'api_key',
        ]);

        $exceptions->renderable(function (UnauthorizedException $e, $request) {
            if ($request->expectsJson() || ! Auth::check()) {
                return null;
            }

            return redirect()
                ->route('profile')
                ->with('error', 'No tienes permiso para acceder a esa sección.');
        });
    })
    ->create();
