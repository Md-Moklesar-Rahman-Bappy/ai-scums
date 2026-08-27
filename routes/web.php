<?php

declare(strict_types=1);

use App\Http\Controllers\AssistantController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExamController;
use App\Http\Controllers\FeeController;
use App\Http\Controllers\InstitutionController;
use App\Http\Controllers\NoticeController;
use App\Http\Controllers\RoutineController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\TenantController;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

/*
 * Named rate limiters consumed by the auth and verification routes.
 */
RateLimiter::for('login', function ($request) {
    $max = (int) config('security.auth_throttle_max', 10);

    return Limit::perMinute($max)->by($request->ip());
});

RateLimiter::for('verification', function ($request) {
    $max = (int) config('security.verification_throttle_max', 6);

    return Limit::perMinute($max)->by($request->user()?->id ?: $request->ip());
});

/*
 * Authentication (guest only).
 *
 * Rate limited to mitigate credential stuffing / brute force on the public
 * auth surface (see config/security.php).
 */
Route::middleware(['guest', 'throttle:login'])->group(function (): void {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
    Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email');
    Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
});

/*
 * Email verification (authenticated, not yet verified).
 */
Route::middleware(['auth', 'throttle:verification'])->group(function (): void {
    Route::get('/email/verify', [AuthController::class, 'showVerificationNotice'])
        ->name('verification.notice');
    Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])
        ->middleware('signed')
        ->name('verification.verify');
    Route::post('/email/verification-notification', [AuthController::class, 'resendVerification'])
        ->name('verification.send');
});

/*
 * Authenticated application routes.
 *
 * Every protected module additionally requires a verified email address.
 */
Route::middleware(['auth', 'verified'])->group(function (): void {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::post('/tenant/switch', [TenantController::class, 'switch'])->name('tenant.switch');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // AI Assistant (all authenticated users with assistant.use permission).
    Route::middleware('permission:assistant.use')->group(function (): void {
        Route::get('/assistant', [AssistantController::class, 'index'])->name('assistant.index');
        Route::post('/assistant/ask', [AssistantController::class, 'ask'])->name('assistant.ask');
        Route::post('/assistant/ask-legacy', [AssistantController::class, 'askLegacy'])->name('assistant.ask.legacy');
    });

    // AI Assistant (all authenticated users with assistant.use permission).
    Route::get('/assistant', [AssistantController::class, 'index'])->name('assistant.index');
    Route::post('/assistant/ask', [AssistantController::class, 'ask'])->name('assistant.ask');
    Route::post('/assistant/ask-legacy', [AssistantController::class, 'askLegacy'])->name('assistant.ask.legacy');

    // Institutions (super admin).
    Route::resource('institutions', InstitutionController::class);

    // Students.
    Route::resource('students', StudentController::class);
    Route::post('/students/{student}/promote', [StudentController::class, 'promote'])->name('students.promote');

    // Teachers.
    Route::resource('teachers', TeacherController::class);

    // Attendance.
    Route::get('/attendances', [AttendanceController::class, 'index'])->name('attendances.index');
    Route::get('/attendances/create', [AttendanceController::class, 'create'])->name('attendances.create');
    Route::post('/attendances', [AttendanceController::class, 'store'])->name('attendances.store');
    Route::get('/attendances/analytics', [AttendanceController::class, 'analytics'])->name('attendances.analytics');

    // Examinations.
    Route::resource('exams', ExamController::class);
    Route::get('/exams/{exam}/marks', [ExamController::class, 'marks'])->name('exams.marks');
    Route::post('/exams/{exam}/marks', [ExamController::class, 'storeMarks'])->name('exams.storeMarks');

    // Fees.
    Route::resource('fees', FeeController::class);
    Route::post('/fees/{fee}/pay', [FeeController::class, 'pay'])->name('fees.pay');

    // Notices.
    Route::resource('notices', NoticeController::class);

    // Routines (FullCalendar).
    Route::get('/routines', [RoutineController::class, 'index'])->name('routines.index');
    Route::get('/routines/events', [RoutineController::class, 'events'])->name('routines.events');
    Route::post('/routines', [RoutineController::class, 'store'])->name('routines.store');
    Route::delete('/routines/{routine}', [RoutineController::class, 'destroy'])->name('routines.destroy');
});

Route::get('/', fn () => redirect()->route(auth()->check() ? 'dashboard' : 'login'));
