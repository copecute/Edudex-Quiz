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
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\FacultyController;
use App\Http\Controllers\MajorController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\TestSessionController;
use App\Http\Controllers\TestShiftController;
use App\Http\Controllers\TestLocationController;
use App\Http\Controllers\TestRoomController;
use App\Http\Controllers\TestSessionRoomController;
use App\Http\Controllers\TestSessionSubjectController;
use App\Http\Controllers\TestShiftSubjectRoomController;
use App\Http\Controllers\TestPaperController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

// Auth routes
Route::middleware(['guest'])->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Dashboard routes
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Test routes
    Route::get('/test/admin', function () {
        return view('test.admin');
    })->middleware('role:0');

    Route::get('/test/teacher', function () {
        return view('test.teacher');
    })->middleware('role:1');

    Route::get('/test/staff', function () {
        return view('test.staff');
    })->middleware('role:2');

    // Routes quản lý user (cần quyền admin)
    Route::middleware(['role:0'])->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    });

    // Routes quản lý khoa, ngành, môn học và câu hỏi (chỉ admin mới truy cập được)
    Route::middleware(['role:0'])->group(function () {
        Route::resource('faculties', FacultyController::class);
        Route::resource('majors', MajorController::class);
        Route::resource('subjects', SubjectController::class);
        Route::resource('questions', QuestionController::class);
        Route::get('/questions/tags/{subject}', [QuestionController::class, 'getTagsBySubject']);
        Route::resource('test_sessions', TestSessionController::class);
        Route::put('/test_sessions/{testSession}/toggle-status', [TestSessionController::class, 'toggleStatus'])
            ->name('test_sessions.toggle-status');
        Route::resource('test_sessions.test_shifts', TestShiftController::class);
        Route::put(
            '/test_sessions/{test_session}/test_shifts/{test_shift}/toggle-status',
            [TestShiftController::class, 'toggleStatus']
        )
            ->name('test_sessions.test_shifts.toggle-status');
        Route::resource('test_locations', TestLocationController::class);
        Route::put(
            '/test_locations/{testLocation}/toggle-status',
            [TestLocationController::class, 'toggleStatus']
        )
            ->name('test_locations.toggle-status');
        Route::resource('test_rooms', TestRoomController::class);
        Route::put(
            '/test_rooms/{testRoom}/toggle-status',
            [TestRoomController::class, 'toggleStatus']
        )
            ->name('test_rooms.toggle-status');
        Route::get(
            '/test_sessions/{test_session}/test_shifts/{test_shift}/rooms',
            [TestSessionRoomController::class, 'index']
        )
            ->name('test_sessions.test_shifts.test_rooms.index');
        Route::put(
            '/test_sessions/{test_session}/test_shifts/{test_shift}/rooms',
            [TestSessionRoomController::class, 'update']
        )
            ->name('test_sessions.test_shifts.test_rooms.update');
        Route::get('/test_sessions/{test_session}/subjects', 
            [TestSessionSubjectController::class, 'index'])
            ->name('test_sessions.subjects.index');
        Route::post('/test_sessions/{test_session}/subjects', 
            [TestSessionSubjectController::class, 'store'])
            ->name('test_sessions.subjects.store');
        Route::delete('/test_sessions/{test_session}/subjects/{subject}', 
            [TestSessionSubjectController::class, 'destroy'])
            ->name('test_sessions.subjects.destroy');
        Route::get('test_sessions/{test_session}/test_shifts/{test_shift}/subject_rooms', [TestShiftSubjectRoomController::class, 'index'])
            ->name('test_sessions.test_shifts.subject_rooms.index');
        Route::post('test_sessions/{test_session}/test_shifts/{test_shift}/subject_rooms', [TestShiftSubjectRoomController::class, 'store'])
            ->name('test_sessions.test_shifts.subject_rooms.store');
        Route::delete('test_sessions/{test_session}/test_shifts/{test_shift}/subject_rooms/{subject_room}', [TestShiftSubjectRoomController::class, 'destroy'])
            ->name('test_sessions.test_shifts.subject_rooms.destroy');
        Route::post('/test_sessions/{test_session}/subjects/{subject}/assign_shifts', 
            [TestSessionSubjectController::class, 'assignShifts'])
            ->name('test_sessions.subjects.assign_shifts');
        Route::post('/test_sessions/{test_session}/test_shifts/{test_shift}/assign_subjects', 
            [TestShiftController::class, 'assignSubjects'])
            ->name('test_sessions.test_shifts.assign_subjects');
    });

    // Thêm routes cho test papers
    Route::resource('test_papers', TestPaperController::class);
    Route::get('/test_papers/tags/{subject}', [TestPaperController::class, 'getTagsBySubject']);
});
