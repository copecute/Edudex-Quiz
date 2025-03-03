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

// Chuyển hướng từ trang chủ vào trang đăng nhập khi chưa đăng nhập
Route::get('/', function () {
    if (Auth::check()) {
        return view('dashboard.index');
    }
    return redirect()->route('login');
});

// Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
    
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Account Management
    Route::resource('accounts', AccountController::class);
    
    // Account Import/Export
    Route::get('accounts/tools/import-export', [AccountController::class, 'importExportTools'])
        ->name('accounts.tools');
    Route::get('accounts-export', [AccountController::class, 'export'])
        ->name('accounts.export');
    Route::post('accounts-import', [AccountController::class, 'import'])
        ->name('accounts.import');
    Route::get('accounts-template', [AccountController::class, 'downloadTemplate'])
        ->name('accounts.template');
    // Profile Routes
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

    // Test Routes với phân quyền
    Route::get('/test/admin', [TestController::class, 'admin'])
        ->middleware('admin')
        ->name('test.admin');

    Route::get('/test/teacher', [TestController::class, 'teacher'])
        ->middleware('teacher')
        ->name('test.teacher');

    Route::get('/test/staff', [TestController::class, 'staff'])
        ->name('test.staff'); // Tất cả user đều có thể truy cập

    // Facility Routes
    Route::resource('facilities', FacilityController::class);
    Route::put('facilities/{facility}/toggle-status', [FacilityController::class, 'toggleStatus'])
        ->name('facilities.toggle-status');
    Route::get('facilities-export', [FacilityController::class, 'export'])
        ->name('facilities.export');
    Route::post('facilities-import', [FacilityController::class, 'import'])
        ->name('facilities.import');
    Route::get('facilities-template', [FacilityController::class, 'downloadTemplate'])
        ->name('facilities.template');

    // Room Routes
    Route::resource('rooms', RoomController::class);
    Route::put('rooms/{room}/toggle-status', [RoomController::class, 'toggleStatus'])
        ->name('rooms.toggle-status');
    Route::get('rooms-export', [RoomController::class, 'export'])
        ->name('rooms.export');
    Route::post('rooms-import', [RoomController::class, 'import'])
        ->name('rooms.import');
    Route::get('rooms-template', [RoomController::class, 'downloadTemplate'])
        ->name('rooms.template');

    // Facility Import/Export
    Route::get('facilities/tools/import-export', [FacilityController::class, 'importExportTools'])
        ->name('facilities.tools');

    // Room Import/Export  
    Route::get('rooms/tools/import-export', [RoomController::class, 'importExportTools'])
        ->name('rooms.tools');

    // Account Routes
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
            
    // Major Routes
    Route::resource('majors', MajorController::class);
    Route::get('majors-export', [MajorController::class, 'export'])->name('majors.export');
    Route::post('majors-import', [MajorController::class, 'import'])->name('majors.import');
    Route::get('majors-template', [MajorController::class, 'downloadTemplate'])->name('majors.template');
    Route::get('majors/tools/import-export', [MajorController::class, 'importExportTools'])->name('majors.tools');

    // Subject Routes
    Route::resource('subjects', SubjectController::class);
    Route::get('subjects-export', [SubjectController::class, 'export'])->name('subjects.export');
    Route::post('subjects-import', [SubjectController::class, 'import'])->name('subjects.import');
    Route::get('subjects-template', [SubjectController::class, 'downloadTemplate'])->name('subjects.template');
    Route::get('subjects/tools/import-export', [SubjectController::class, 'importExportTools'])->name('subjects.tools');

    // Question Routes
    Route::get('questions/tags-by-subject', [QuestionController::class, 'getTagsBySubject'])->name('questions.tags');
    Route::get('questions/tools/import-export', [QuestionController::class, 'importExportTools'])->name('questions.tools');
    Route::get('questions-export', [QuestionController::class, 'export'])->name('questions.export');
    Route::post('questions-import', [QuestionController::class, 'import'])->name('questions.import');
    Route::get('questions-template', [QuestionController::class, 'downloadTemplate'])->name('questions.template');
    Route::resource('questions', QuestionController::class);

    // Exam Routes
    Route::resource('exams', ExamController::class);
    Route::get('exams/tags-by-subject', [ExamController::class, 'getTagsBySubject'])->name('exams.tags');
    Route::get('exams/tools/import-export', [ExamController::class, 'importExportTools'])->name('exams.tools');
    Route::get('exams-export', [ExamController::class, 'export'])->name('exams.export');
    Route::post('exams-import', [ExamController::class, 'import'])->name('exams.import');
    Route::get('exams-template', [ExamController::class, 'downloadTemplate'])->name('exams.template');
});

// Add this route for handling avatar images
Route::get('storage/avatars/{filename}', function ($filename) {
    $path = storage_path('app/public/avatars/' . $filename);
    
    if (!File::exists($path)) {
        abort(404);
    }
    
    return response()->file($path);
})->where('filename', '.*');

if (app()->environment('local')) {
    // Error pages preview routes
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
