<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

// ユーザー側
use App\Http\Controllers\User\AuthController as UserAuthController;
use App\Http\Controllers\User\AttendanceController as UserAttendanceController;
use App\Http\Controllers\User\AttendanceActionController as UserAttendanceActionController;
use App\Http\Controllers\User\AttendanceStatusController as UserAttendanceStatusController;
use App\Http\Controllers\User\EmailVerificationController;

// 管理者側
use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\AttendanceController as AdminAttendanceController;
use App\Http\Controllers\Admin\AttendanceActionController as AdminAttendanceActionController;
use App\Http\Controllers\Admin\StaffController as AdminStaffController;

// 一般ユーザー認証系
Route::post('/api/register', [UserAuthController::class, 'register']);
Route::post('/api/login', [UserAuthController::class, 'login']);
Route::post('/api/logout', [UserAuthController::class, 'logout']);

// メール認証関連
Route::middleware(['web'])->group(function () {
    Route::get('/api/email/verify', [EmailVerificationController::class, 'showNotice'])
        ->name('verification.notice');

    // 🔸 再送は未ログインでも呼べるように変更
    Route::post('/api/email/verification-notification', [EmailVerificationController::class, 'resendVerificationEmail'])
        ->middleware('throttle:6,1')
        ->name('verification.send');
});

Route::get('/api/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verifyEmail'])
    ->middleware(['signed'])
    ->name('verification.verify');

// 管理者認証系
Route::post('/api/admin/login', [AdminAuthController::class, 'login']);
Route::post('/api/admin/logout', [AdminAuthController::class, 'logout']);

// 現在ログイン中のユーザーを取得
Route::get('/api/user', function () {
    return response()->json(Auth::guard('web')->user());
})->middleware(['web', 'auth:web']);

Route::get('/api/admin/user', function () {
    return response()->json(Auth::guard('admin')->user());
})->middleware(['web', 'auth:admin']);

// 勤怠API（一般ユーザー用）
Route::middleware(['web', 'auth:web'])->group(function () {

    // ---- 勤怠ステータス ----
    Route::get('/api/attendance/status', [UserAttendanceStatusController::class, 'getStatus']);

    // ---- 勤怠データ参照系（表示）----
    Route::get('/api/attendance/list', [UserAttendanceController::class, 'getList']);
    Route::get('/api/attendance/requests', [UserAttendanceController::class, 'getRequestList']);
    Route::get('/api/attendance/detail/{id}', [UserAttendanceController::class, 'getDetail'])
        ->whereNumber('id');

    // ---- 打刻・修正など操作系 ----
    Route::post('/api/attendance/clock', [UserAttendanceActionController::class, 'clock']);
    Route::post('/api/attendance/update-or-create/{id}', [UserAttendanceActionController::class, 'updateOrCreate'])
        ->whereNumber('id');
});

// 勤怠API（管理者用）
Route::prefix('api/admin')
    ->middleware(['web', 'auth:admin'])
    ->group(function () {

    // ---- 表示系 ----
    Route::get('/attendance/list', [AdminAttendanceController::class, 'getList']);
    Route::get('/attendance/requests', [AdminAttendanceController::class, 'getRequestList']);
    Route::get('/attendance/staff/{id}', [AdminAttendanceController::class, 'getListByStaff'])
        ->whereNumber('id');
    Route::get('/attendance/{id}', [AdminAttendanceController::class, 'getDetail'])
        ->whereNumber('id');
    Route::post('/attendance/approve/{id}', [AdminAttendanceController::class, 'approve'])
        ->whereNumber('id');
    Route::get('/attendance/staff/{id}/export', [AdminAttendanceController::class, 'exportCsv'])
        ->whereNumber('id');

    // ---- 操作系 ----
    Route::post('/attendance/update-or-create/{id}', [AdminAttendanceActionController::class, 'updateOrCreate'])
        ->whereNumber('id');

    // ---- スタッフ関連 ----
    Route::get('/staff/list', [AdminStaffController::class, 'getList']);
});
