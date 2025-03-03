<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        // Xử lý tất cả exceptions cho API routes
        $this->renderable(function (Throwable $e, Request $request) {
            if ($request->is('api/*')) {
                return $this->handleApiException($e);
            }
        });
    }

    /**
     * Handle API exceptions
     */
    private function handleApiException(Throwable $e): JsonResponse
    {
        $status = $this->getHttpStatusCode($e);
        
        $response = [
            'status' => 'error',
            'message' => $this->getErrorMessage($e, $status)
        ];

        if (config('app.debug')) {
            $response['debug'] = [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ];
        }

        return response()->json($response, $status);
    }

    /**
     * Get error message based on exception type and status code
     */
    private function getErrorMessage(Throwable $e, int $status): string
    {
        return match ($status) {
            404 => 'Không tìm thấy đường dẫn yêu cầu',
            401 => 'Không có quyền truy cập',
            403 => 'Truy cập bị từ chối',
            400 => 'Yêu cầu không hợp lệ',
            default => 'Đã xảy ra lỗi, vui lòng thử lại sau'
        };
    }

    /**
     * Get HTTP status code from exception
     */
    private function getHttpStatusCode(Throwable $e): int
    {
        if (method_exists($e, 'getStatusCode')) {
            return $e->getStatusCode();
        }

        return match (get_class($e)) {
            NotFoundHttpException::class => 404,
            BadRequestHttpException::class => 400,
            UnauthorizedHttpException::class => 401,
            AccessDeniedHttpException::class => 403,
            default => 500,
        };
    }
} 