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

use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\StudentAuthController;
use Illuminate\Support\Facades\Route;

// routes không cần xác thực

// tìm server
Route::post('/wfaE0FbQWldGoDlGFyFgKWY0MiUizH2', function() {
    return response()->json(['messages' => 'copecute is beautiful']);
});

Route::post('/login', [AuthApiController::class, 'login']);

// routes cần xác thực
Route::middleware('auth.api')->group(function () {
    Route::post('/logout', [AuthApiController::class, 'logout']);
    Route::get('/me', [AuthApiController::class, 'me']);
});

Route::prefix('student')->group(function () {
    Route::post('login', [StudentAuthController::class, 'login']);
    
    // Sử dụng middleware auth.student
    Route::middleware(['api', 'auth.student'])->group(function () {
        Route::get('profile', [StudentAuthController::class, 'profile']);
        Route::post('logout', [StudentAuthController::class, 'logout']);
    });
}); 