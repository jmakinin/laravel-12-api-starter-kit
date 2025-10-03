<?php

use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Application;
use Illuminate\Database\QueryException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__ . '/../routes/api.php',
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'abilities' => \Laravel\Sanctum\Http\Middleware\CheckAbilities::class,
            'ability' => \Laravel\Sanctum\Http\Middleware\CheckForAnyAbility::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->renderable(function (Exception $e) {

            Log::debug($e instanceof \Throwable ? $e->getTraceAsString() : ('error has an error'));

            if ($e instanceof ValidationException) {
                return response()->json([
                    'status' => 'Error',
                    'message' => $e->errors()
                ], 422);
            }
            if ($e instanceof AuthenticationException) {
                return response()->json([
                    'status' => 'Error',
                    'message' => 'Unauthenticated',
                ], 401);
            }
            if ($e instanceof NotFoundHttpException) {
                return response()->json([
                    'status' => 'Error',
                    'message' => $e->getMessage() ? $e->getMessage() : 'Resource not found.'
                ], 404);
            }
            if ($e instanceof UnauthorizedException || $e instanceof AccessDeniedHttpException) {
                return response()->json([
                    'status' => 'Error',
                    'message' => 'Unauthorised for resource.'
                ], 403);
            }
            if ($e instanceof RouteNotFoundException) {
                return response()->json([
                    'status' => 'Error',
                    'message' => 'Unauthenticated, please log in'
                ], 401);
            }
            if ($e instanceof MethodNotAllowedHttpException || $e instanceof MethodNotAllowedException) {
                return response()->json([
                    'status' => 'Error',
                    'message' => 'Method Not Allowed'
                ], 401);
            }
            if ($e instanceof QueryException) {
                return response()->json([
                    'status' => 'Error',
                    'message' => 'No matching resource'
                ], 404);
            }
            if (app()->environment('production')) {
                return response()->json([
                    'status' => 'Error',
                    'message' => 'An unexpected error occurred. Please try again later.'
                ], 500);
            } else {
                return response()->json([
                    'status' => 'Error',
                    'message' => 'Unexpected error occurred: ' . $e->getMessage(),
                    'exception' => get_class($e)
                ], 500);
            }
        });
    })->create();
