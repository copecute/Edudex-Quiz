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

// chuyển hướng từ trang chủ vào trang đăng nhập khi chưa đăng nhập
Route::get('/', function () {
    if (Auth::check()) {
        return view('dashboard.index');
    }
    return redirect()->route('login');
});

// các route xác thực
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
    
    // quản lý tài khoản
    Route::resource('accounts', AccountController::class);
    
    // import/export tài khoản
    Route::get('accounts/tools/import-export', [AccountController::class, 'importExportTools'])
        ->name('accounts.tools');
    Route::get('accounts-export', [AccountController::class, 'export'])
        ->name('accounts.export');
    Route::post('accounts-import', [AccountController::class, 'import'])
        ->name('accounts.import');
    Route::get('accounts-template', [AccountController::class, 'downloadTemplate'])
        ->name('accounts.template');
    // các route profile
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

    // các route test với phân quyền
    Route::get('/test/admin', [TestController::class, 'admin'])
        ->middleware('admin')
        ->name('test.admin');

    Route::get('/test/teacher', [TestController::class, 'teacher'])
        ->middleware('teacher')
        ->name('test.teacher');

    Route::get('/test/staff', [TestController::class, 'staff'])
        ->name('test.staff'); // tất cả user đều có thể truy cập

    // các route facility
    Route::resource('facilities', FacilityController::class);
    Route::put('facilities/{facility}/toggle-status', [FacilityController::class, 'toggleStatus'])
        ->name('facilities.toggle-status');
    Route::get('facilities-export', [FacilityController::class, 'export'])
        ->name('facilities.export');
    Route::post('facilities-import', [FacilityController::class, 'import'])
        ->name('facilities.import');
    Route::get('facilities-template', [FacilityController::class, 'downloadTemplate'])
        ->name('facilities.template');

    // các route room
    Route::resource('rooms', RoomController::class);
    Route::put('rooms/{room}/toggle-status', [RoomController::class, 'toggleStatus'])
        ->name('rooms.toggle-status');
    Route::get('rooms-export', [RoomController::class, 'export'])
        ->name('rooms.export');
    Route::post('rooms-import', [RoomController::class, 'import'])
        ->name('rooms.import');
    Route::get('rooms-template', [RoomController::class, 'downloadTemplate'])
        ->name('rooms.template');

    // import/export facility
    Route::get('facilities/tools/import-export', [FacilityController::class, 'importExportTools'])
        ->name('facilities.tools');

    // import/export room  
    Route::get('rooms/tools/import-export', [RoomController::class, 'importExportTools'])
        ->name('rooms.tools');

    // các route account
    Route::put('accounts/{account}/toggle-status', [AccountController::class, 'toggleStatus'])
        ->name('accounts.toggle-status');

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
            
    // các route major
    Route::resource('majors', MajorController::class);
    Route::get('majors-export', [MajorController::class, 'export'])->name('majors.export');
    Route::post('majors-import', [MajorController::class, 'import'])->name('majors.import');
    Route::get('majors-template', [MajorController::class, 'downloadTemplate'])->name('majors.template');
    Route::get('majors/tools/import-export', [MajorController::class, 'importExportTools'])->name('majors.tools');

    // các route subject
    Route::resource('subjects', SubjectController::class);
    Route::get('subjects-export', [SubjectController::class, 'export'])->name('subjects.export');
    Route::post('subjects-import', [SubjectController::class, 'import'])->name('subjects.import');
    Route::get('subjects-template', [SubjectController::class, 'downloadTemplate'])->name('subjects.template');
    Route::get('subjects/tools/import-export', [SubjectController::class, 'importExportTools'])->name('subjects.tools');

    // các route question
    Route::get('questions/tags-by-subject', [QuestionController::class, 'getTagsBySubject'])->name('questions.tags');
    Route::get('questions/tools/import-export', [QuestionController::class, 'importExportTools'])->name('questions.tools');
    Route::get('questions-export', [QuestionController::class, 'export'])->name('questions.export');
    Route::post('questions-import', [QuestionController::class, 'import'])->name('questions.import');
    Route::get('questions-template', [QuestionController::class, 'downloadTemplate'])->name('questions.template');
    Route::resource('questions', QuestionController::class);

    // các route exam
    Route::resource('exams', ExamController::class);
    Route::get('exams/tools/import-export', [ExamController::class, 'importExportTools'])->name('exams.tools');
    Route::get('exams-export', [ExamController::class, 'export'])->name('exams.export');
    Route::post('exams-import', [ExamController::class, 'import'])->name('exams.import');
    Route::get('exams-template', [ExamController::class, 'downloadTemplate'])->name('exams.template');
    Route::get('/exams/by-subject/{subject}', [ExamController::class, 'getBySubject'])
        ->name('exams.by-subject');

    // các route thí sinh trong kỳ thi
    Route::prefix('exam-periods/{examPeriod}/students')
        ->name('students.')
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
    Route::prefix('exam-periods/{examPeriod}/proctors')->group(function () {
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
    });

    // các route phòng thi trong kỳ thi
    Route::prefix('exam-periods/{examPeriod}/rooms')->name('exam-period-rooms.')->group(function () {
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
    Route::prefix('exam-periods/{examPeriod}/assignment')->name('exam-periods.assignment.')->group(function () {
        // phân công ca thi cho môn thi
        Route::get('/subjects', [ExamPeriodAssignmentController::class, 'subjects'])->name('subjects');
        Route::post('/subjects', [ExamPeriodAssignmentController::class, 'assignSubjects'])->name('subjects.store');
        
        // Tự động phân công
        Route::get('/auto', [ExamPeriodAssignmentController::class, 'autoAssignmentForm'])->name('auto');
        Route::post('/auto', [ExamPeriodAssignmentController::class, 'autoAssign'])->name('auto.store');

        // phân công phòng thi cho ca thi
        Route::get('/rooms', [ExamPeriodAssignmentController::class, 'rooms'])->name('rooms');
        Route::post('/rooms', [ExamPeriodAssignmentController::class, 'assignRooms'])->name('rooms.store');
        Route::get('/rooms/export', [ExamPeriodAssignmentController::class, 'exportRoomAssignments'])->name('rooms.export');
        Route::post('/students', [ExamPeriodAssignmentController::class, 'assignStudents'])->name('students');
    });

    Route::get('/exam-periods/{examPeriod}/results', [ExamResultController::class, 'index'])
        ->name('exam-periods.results');
    // Thêm route cho phúc khảo
    Route::post('/exam-periods/{examPeriod}/results/{result}/review', [ExamResultController::class, 'review'])
        ->name('exam-periods.results.review');
    // Thêm route xuất kết quả
    Route::get('/exam-periods/{examPeriod}/results/export', [ExamResultController::class, 'export'])
        ->name('exam-periods.results.export');

    Route::delete('/exam-periods/{examPeriod}/assignment/clear', [ExamPeriodAssignmentController::class, 'clear'])
        ->name('exam-periods.assignment.clear');
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

    // thay thế route exam-shifts cũ bằng nested route
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

    //  route exam-period-subjects
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
    // các route xem trước trang lỗi
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
