<?php

use App\Http\Middleware\CheckPermission;
use App\Http\Middleware\EnsureEventAccess;
use App\Http\Middleware\EnsureTenantAccess;
use App\Http\Middleware\ForceJson;
use App\Http\Middleware\LogApiRequest;
use App\Http\Middleware\SecurityHeaders;
use App\Support\ApiResponse;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->api(prepend: [
            ForceJson::class,
            SecurityHeaders::class,
        ]);

        $middleware->alias([
            'tenant.access' => EnsureTenantAccess::class,
            'event.access' => EnsureEventAccess::class,
            'check.permission' => CheckPermission::class,
            'log.api' => LogApiRequest::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (AuthenticationException $exception, $request) {
            if ($request->expectsJson()) {
                return ApiResponse::unauthorized();
            }
        });

        $exceptions->render(function (AuthorizationException $exception, $request) {
            if ($request->expectsJson()) {
                return ApiResponse::forbidden($exception->getMessage() ?: 'Forbidden.');
            }
        });

        $exceptions->render(function (ModelNotFoundException|NotFoundHttpException $exception, $request) {
            if ($request->expectsJson()) {
                return ApiResponse::notFound();
            }
        });
    })->create();
