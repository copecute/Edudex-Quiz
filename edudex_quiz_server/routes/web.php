<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use App\Http\Controllers\TestController;
use App\Http\Controllers\FacilityController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\FacultyController;
use App\Http\Controllers\MajorController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\ExamController;
use App\Http\Controllers\ExamPeriodController;
use App\Http\Controllers\ExamShiftController;
use App\Http\Controllers\ExamPeriodSubjectController;
use App\Http\Controllers\ExamPeriodSubjectStudentController;
use App\Http\Controllers\ExamPeriodProctorController;
use App\Http\Controllers\ExamPeriodRoomController;
use App\Http\Controllers\ExamPeriodAssignmentController;
use App\Http\Controllers\ExamPeriodStudentController;
use App\Http\Controllers\ExamResultController;
use App\Http\Controllers\EdudexFileController;
use App\Http\Controllers\ExamReportController;

// chuyển hướng từ trang chủ vào trang đăng nhập khi chưa đăng nhập
Route::get('/', function () {
    if (Auth::check()) {
        return view('dashboard.index');
    }
    return redirect()->route('login');
});

// các route authentication
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);
});

Route::middleware('auth')->group(function () {

    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');


    // trang dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // đọc file .edudex
    Route::middleware(['auth'])->group(function () {
        Route::get('/edudex-files', [EdudexFileController::class, 'index'])
            ->name('edudex-files.index');
        Route::post('/edudex-files/read', [EdudexFileController::class, 'read'])->name('edudex-files.read');
    });
    

    // quản lý tài khoản
    Route::middleware(['auth', 'admin'])->group(function () {
        Route::resource('accounts', AccountController::class);
        Route::get('accounts/tools/import-export', [AccountController::class, 'importExportTools'])->name('accounts.tools');
        Route::get('accounts-export', [AccountController::class, 'export'])->name('accounts.export');
        Route::post('accounts-import', [AccountController::class, 'import'])->name('accounts.import');
        Route::get('accounts-template', [AccountController::class, 'downloadTemplate'])->name('accounts.template');
        Route::put('{account}/toggle-status', [AccountController::class, 'toggleStatus'])
            ->name('accounts.toggle-status');
    });


    // các route profile
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');


    // các route test phân quyền
    Route::get('/test/admin', [TestController::class, 'admin'])
        ->middleware('admin')
        ->name('test.admin');

    Route::get('/test/teacher', [TestController::class, 'teacher'])
        ->middleware('teacher')
        ->name('test.teacher');

    Route::get('/test/staff', [TestController::class, 'staff'])
        ->name('test.staff'); // tất cả user đều có thể truy cập


    // các route cơ sở
    Route::middleware(['auth', 'admin'])->group(function() {
        Route::resource('facilities', FacilityController::class);
        Route::put('facilities/{facility}/toggle-status', [FacilityController::class, 'toggleStatus'])
            ->name('facilities.toggle-status');
        Route::get('facilities-export', [FacilityController::class, 'export'])
            ->name('facilities.export');
        Route::post('facilities-import', [FacilityController::class, 'import'])
            ->name('facilities.import');
        Route::get('facilities-template', [FacilityController::class, 'downloadTemplate'])
            ->name('facilities.template');
        Route::get('facilities/tools/import-export', [FacilityController::class, 'importExportTools'])
            ->name('facilities.tools');
    });


    // các route phòng
    Route::middleware(['auth', 'admin'])->group(function() {
        Route::resource('rooms', RoomController::class);
        Route::put('rooms/{room}/toggle-status', [RoomController::class, 'toggleStatus'])
            ->name('rooms.toggle-status');
        Route::get('rooms-export', [RoomController::class, 'export'])
            ->name('rooms.export');
        Route::post('rooms-import', [RoomController::class, 'import'])
            ->name('rooms.import');
        Route::get('rooms-template', [RoomController::class, 'downloadTemplate'])
            ->name('rooms.template');
        Route::get('rooms/tools/import-export', [RoomController::class, 'importExportTools'])
            ->name('rooms.tools');
    });

    
    // các route khoa
    Route::middleware(['auth', 'admin'])->group(function() {
        Route::resource('faculties', FacultyController::class);
        Route::put('faculties/{faculty}/toggle-status', [FacultyController::class, 'toggleStatus'])
            ->name('faculties.toggle-status');
        Route::get('faculties-export', [FacultyController::class, 'export'])
            ->name('faculties.export');
        Route::post('faculties-import', [FacultyController::class, 'import'])
            ->name('faculties.import');
        Route::get('faculties-template', [FacultyController::class, 'downloadTemplate'])
            ->name('faculties.template');
        Route::get('faculties/tools/import-export', [FacultyController::class, 'importExportTools'])
            ->name('faculties.tools');
    });

    // các route chuyên ngành
    Route::middleware(['auth', 'admin'])->group(function() {
        Route::resource('majors', MajorController::class);
        Route::get('majors-export', [MajorController::class, 'export'])->name('majors.export');
        Route::post('majors-import', [MajorController::class, 'import'])->name('majors.import');
        Route::get('majors-template', [MajorController::class, 'downloadTemplate'])->name('majors.template');
        Route::get('majors/tools/import-export', [MajorController::class, 'importExportTools'])->name('majors.tools');
    });

    // các route môn học
    Route::middleware(['auth', 'admin'])->group(function() {
        Route::resource('subjects', SubjectController::class);
        Route::get('subjects-export', [SubjectController::class, 'export'])->name('subjects.export');
        Route::post('subjects-import', [SubjectController::class, 'import'])->name('subjects.import');
        Route::get('subjects-template', [SubjectController::class, 'downloadTemplate'])->name('subjects.template');
        Route::get('subjects/tools/import-export', [SubjectController::class, 'importExportTools'])->name('subjects.tools');
    });

    // các route ngân hàng câu hỏi
    Route::middleware(['auth', 'teacher'])->group(function() {
        Route::get('questions/tags-by-subject', [QuestionController::class, 'getTagsBySubject'])->name('questions.tags');
        Route::get('questions/tools/import-export', [QuestionController::class, 'importExportTools'])->name('questions.tools');
        Route::get('questions-export', [QuestionController::class, 'export'])->name('questions.export');
        Route::post('questions-import', [QuestionController::class, 'import'])->name('questions.import');
        Route::get('questions-template', [QuestionController::class, 'downloadTemplate'])->name('questions.template');
        Route::resource('questions', QuestionController::class);
    });

    // các route đề thi
    Route::middleware(['auth', 'teacher'])->group(function() {
        Route::resource('exams', ExamController::class);
        Route::get('exams/tools/import-export', [ExamController::class, 'importExportTools'])->name('exams.tools');
        Route::get('exams-export', [ExamController::class, 'export'])->name('exams.export');
        Route::post('exams-import', [ExamController::class, 'import'])->name('exams.import');
        Route::get('exams-template', [ExamController::class, 'downloadTemplate'])->name('exams.template');
        Route::get('/exams/by-subject/{subject}', [ExamController::class, 'getBySubject'])
            ->name('exams.by-subject');
    });

    // các route thí sinh trong kỳ thi
    Route::prefix('exam-periods/{examPeriod}/students')
        ->name('students.')
        ->middleware(['auth', 'admin'])
        ->group(function () {
            Route::get('/', [ExamPeriodStudentController::class, 'index'])->name('index');
            Route::get('/create', [ExamPeriodStudentController::class, 'create'])->name('create');
            Route::get('/{student}/edit', [ExamPeriodStudentController::class, 'edit'])->name('edit');
            Route::put('/{student}', [ExamPeriodStudentController::class, 'update'])->name('update');
            Route::post('/', [ExamPeriodStudentController::class, 'store'])->name('store');
            Route::delete('/', [ExamPeriodStudentController::class, 'destroy'])->name('destroy');
            Route::delete('/multiple', [ExamPeriodStudentController::class, 'destroyMultiple'])->name('destroy-multiple');
            Route::get('/tools', [ExamPeriodStudentController::class, 'importExportTools'])->name('tools');
            Route::get('/template', [ExamPeriodStudentController::class, 'downloadTemplate'])->name('template');
            Route::post('/import', [ExamPeriodStudentController::class, 'import'])->name('import');
            Route::get('/export', [ExamPeriodStudentController::class, 'export'])->name('export');
        });

    // các route cán bộ coi thi trong kỳ thi
    Route::prefix('exam-periods/{examPeriod}/proctors')->middleware(['auth', 'admin'])->group(function () {
        Route::get('/', [ExamPeriodProctorController::class, 'index'])
            ->name('exam-period-proctors.index');
        Route::get('/assign', [ExamPeriodProctorController::class, 'assign'])
            ->name('exam-period-proctors.assign');
        Route::post('/', [ExamPeriodProctorController::class, 'store'])
            ->name('exam-period-proctors.store');
        Route::delete('/{proctor}', [ExamPeriodProctorController::class, 'destroy'])
            ->name('exam-period-proctors.destroy');
        // các route import/export
        Route::get('/tools', [ExamPeriodProctorController::class, 'importExportTools'])
            ->name('exam-period-proctors.tools');
        Route::post('/import', [ExamPeriodProctorController::class, 'import'])
            ->name('exam-period-proctors.import');
        Route::get('/export', [ExamPeriodProctorController::class, 'export'])
            ->name('exam-period-proctors.export');
        Route::get('/template', [ExamPeriodProctorController::class, 'downloadTemplate'])
            ->name('exam-period-proctors.template');
        Route::delete('/multiple', [ExamPeriodProctorController::class, 'destroyMultiple'])
            ->name('exam-period-proctors.destroy-multiple');
    });

    // các route phòng thi trong kỳ thi
    Route::prefix('exam-periods/{examPeriod}/rooms')->name('exam-period-rooms.')->middleware(['auth', 'admin'])->group(function () {
        Route::get('/', [ExamPeriodRoomController::class, 'index'])->name('index');
        Route::get('/assign', [ExamPeriodRoomController::class, 'assign'])->name('assign');
        Route::post('/', [ExamPeriodRoomController::class, 'store'])->name('store');
        Route::delete('/', [ExamPeriodRoomController::class, 'destroy'])->name('destroy');
        Route::delete('/multiple', [ExamPeriodRoomController::class, 'destroyMultiple'])->name('destroy-multiple');
        Route::get('/tools', [ExamPeriodRoomController::class, 'importExportTools'])->name('tools');
        Route::get('/template', [ExamPeriodRoomController::class, 'downloadTemplate'])->name('template');
        Route::post('/import', [ExamPeriodRoomController::class, 'import'])->name('import');
        Route::get('/export', [ExamPeriodRoomController::class, 'export'])->name('export');
    });

    // phân công kỳ thi
    Route::prefix('exam-periods/{examPeriod}/assignment')->name('exam-periods.assignment.')->middleware(['auth', 'admin'])->group(function () {
        // phân công ca thi cho môn thi
        Route::get('/subjects', [ExamPeriodAssignmentController::class, 'subjects'])->name('subjects');
        Route::post('/subjects', [ExamPeriodAssignmentController::class, 'assignSubjects'])->name('subjects.store');

        // Tự động phân công
        Route::get('/auto', [ExamPeriodAssignmentController::class, 'autoAssignmentForm'])->name('auto');
        Route::post('/auto', [ExamPeriodAssignmentController::class, 'autoAssign'])->name('auto.store');

        // xóa phân công
        Route::delete('/clear', [ExamPeriodAssignmentController::class, 'clear'])
            ->name('clear');

        // phân công phòng thi cho ca thi
        Route::get('/rooms', [ExamPeriodAssignmentController::class, 'rooms'])->name('rooms');
        Route::post('/rooms', [ExamPeriodAssignmentController::class, 'assignRooms'])->name('rooms.store');
        Route::get('/rooms/export', [ExamPeriodAssignmentController::class, 'exportRoomAssignments'])->name('rooms.export');
        Route::post('/students', [ExamPeriodAssignmentController::class, 'assignStudents'])->name('students');
    });

    // xuất danh sách thí sinh trong ca thi (đặt ngoài này để user có thể truy cập)
    Route::get('/exam-shifts/{shift}/rooms/{room}/export-students', [ExamShiftController::class, 'exportStudents'])
        ->name('exam-shifts.export-students')
        ->middleware(['auth']);

    // kết quả thi
    Route::get('/exam-periods/{examPeriod}/results', [ExamResultController::class, 'index'])
        ->name('exam-periods.results')
        ->middleware(['auth', 'admin']);
    // route cho phúc khảo
    Route::post('/exam-periods/{examPeriod}/results/{result}/review', [ExamResultController::class, 'review'])
        ->name('exam-periods.results.review')
        ->middleware(['auth', 'admin']);
    // route xuất kết quả
    Route::get('/exam-periods/{examPeriod}/results/export', [ExamResultController::class, 'export'])
        ->name('exam-periods.results.export')
        ->middleware(['auth', 'admin']);

    // thêm routes cho báo cáo kết quả thi
    Route::prefix('exam-periods/{examPeriod}/reports')->name('exam-reports.')->group(function() {
        Route::get('/', [ExamReportController::class, 'index'])->name('index');
        Route::get('/by-subject', [ExamReportController::class, 'bySubject'])->name('by-subject');
        Route::get('/by-room', [ExamReportController::class, 'byRoom'])->name('by-room');
        Route::get('/student-ranking', [ExamReportController::class, 'studentRanking'])->name('ranking');
        Route::get('/completion-rate', [ExamReportController::class, 'completionRate'])->name('completion');
        Route::get('/export/{type}', [ExamReportController::class, 'export'])->name('export');
    });
});

// quản lý kỳ thi
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/exam-periods/tools', [ExamPeriodController::class, 'importExportTools'])->name('exam-periods.tools');
    Route::get('/exam-periods/template', [ExamPeriodController::class, 'downloadTemplate'])->name('exam-periods.template');
    Route::post('/exam-periods/import', [ExamPeriodController::class, 'import'])->name('exam-periods.import');
    Route::get('/exam-periods/export', [ExamPeriodController::class, 'export'])->name('exam-periods.export');
    Route::put('/exam-periods/{examPeriod}/toggle-status', [ExamPeriodController::class, 'toggleStatus'])->name('exam-periods.toggle-status');
    Route::get('/exam-periods/{examPeriod}/dashboard', [ExamPeriodController::class, 'dashboard'])->name('exam-periods.dashboard');
    Route::resource('exam-periods', ExamPeriodController::class);

    // ca thi
    Route::prefix('exam-periods/{examPeriod}/shifts')->group(function () {
        Route::get('/', [ExamShiftController::class, 'index'])
            ->name('exam-shifts.index');
        Route::get('/create', [ExamShiftController::class, 'create'])
            ->name('exam-shifts.create');
        Route::post('/', [ExamShiftController::class, 'store'])
            ->name('exam-shifts.store');
        Route::get('/{examShift}/edit', [ExamShiftController::class, 'edit'])
            ->name('exam-shifts.edit');
        Route::put('/{examShift}', [ExamShiftController::class, 'update'])
            ->name('exam-shifts.update');
        Route::delete('/{examShift}', [ExamShiftController::class, 'destroy'])
            ->name('exam-shifts.destroy');
        Route::put('/{examShift}/toggle-status', [ExamShiftController::class, 'toggleStatus'])
            ->name('exam-shifts.toggle-status');
    });

    // route môn thi - kỳ thi
    Route::prefix('exam-periods/{examPeriod}/subjects')->group(function () {
        Route::get('/', [ExamPeriodSubjectController::class, 'index'])
            ->name('exam-period-subjects.index');
        Route::get('/create', [ExamPeriodSubjectController::class, 'create'])
            ->name('exam-period-subjects.create');
        Route::post('/', [ExamPeriodSubjectController::class, 'store'])
            ->name('exam-period-subjects.store');
        Route::get('/{examPeriodSubject}/edit', [ExamPeriodSubjectController::class, 'edit'])
            ->name('exam-period-subjects.edit');
        Route::put('/{examPeriodSubject}', [ExamPeriodSubjectController::class, 'update'])
            ->name('exam-period-subjects.update');
        Route::delete('/{examPeriodSubject}', [ExamPeriodSubjectController::class, 'destroy'])
            ->name('exam-period-subjects.destroy');
    });
});


// route này để xử lý avatar
Route::get('storage/avatars/{filename}', function ($filename) {
    $path = storage_path('app/public/avatars/' . $filename);

    if (!File::exists($path)) {
        abort(404);
    }

    return response()->file($path);
})->where('filename', '.*');

if (app()->environment('local')) {
    // các route xem trang lỗi trước khi deploy
    Route::get('/400', function () {
        return response()->view('errors.400', [], 400);
    });

    Route::get('/401', function () {
        return response()->view('errors.401', [], 401);
    });

    Route::get('/403', function () {
        return response()->view('errors.403', [], 403);
    });

    Route::get('/404', function () {
        return response()->view('errors.404', [], 404);
    });
}