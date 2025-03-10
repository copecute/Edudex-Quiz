<?php
//                       _oo0oo_
//                      o8888888o
//                      88" . "88
//                      (| -_- |)
//                      0\  =  /0
//                    ___/`---'\___
//                  .' \\|     |// '.
//                 / \\|||  :  |||// \
//                / _||||| -:- |||||- \
//               |   | \\\  -  /// |   |
//               | \_|  ''\---/''  |_/ |
//               \  .-\__  '-'  ___/-. /
//             ___'. .'  /--.--\  `. .'___
//          ."" '<  `.___\_<|>_/___.' >' "".
//         | | :  `- \`.;`\ _ /`;.`/ - ` : | |
//         \  \ `_.   \_ __\ /__ _/   .-` /  /
//     =====`-.____`.___ \_____/___.-`___.-'=====
//                       `=---='
//
//     ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//            amen đà phật, không bao giờ BUG
//     ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Middleware\APITokenAuthentication;
use App\Http\Controllers\Api\ExamResultController;
use App\Http\Controllers\Api\ExamScheduleController;

// routes không cần xác thực

// tìm server
Route::post('/wfaE0FbQWldGoDlGFyFgKWY0MiUizH2', function() {
    return response()->json(['messages' => 'copecute is beautiful']);
});

Route::post('/login', [AuthController::class, 'login']);

// routes cần xác thực
Route::middleware(APITokenAuthentication::class)->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [AuthController::class, 'profile']);

    // nộp bài
    Route::post('/exam-periods/{examPeriod}/results', [ExamResultController::class, 'store']);
    
    // Lấy danh sách thí sinh và đề thi
    Route::get('/exam-schedule/shifts/{shift}/rooms/{room}/students', [ExamScheduleController::class, 'getStudentsByRoom'])
        ->name('api.exam-schedule.students');
    Route::get('/exam-schedule/shifts/{shift}/rooms/{room}/exam', [ExamScheduleController::class, 'exam']);
});
