<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class Handler extends ExceptionHandler
{
    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->renderable(function (NotFoundHttpException $e) {
            return response()->view('errors.404', [], 404);
        });

        $this->renderable(function (BadRequestHttpException $e) {
            return response()->view('errors.400', [], 400);
        });

        $this->renderable(function (UnauthorizedHttpException $e) {
            return response()->view('errors.401', [], 401);
        });

        $this->renderable(function (AccessDeniedHttpException $e) {
            return response()->view('errors.403', [], 403);
        });
    }
} 