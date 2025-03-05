<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Middleware\APITokenAuthentication;
use App\Http\Controllers\Api\ExamResultController;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware(APITokenAuthentication::class)->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [AuthController::class, 'profile']);

    // nộp bài
    Route::post('/exam-periods/{examPeriod}/results', [ExamResultController::class, 'store']);
});