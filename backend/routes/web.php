<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

// ユーザー側
use App\Http\Controllers\User\AuthController as UserAuthController;
use App\Http\Controllers\User\AttendanceController as UserAttendanceController;
use App\Http\Controllers\User\AttendanceActionController as UserAttendanceActionController;
use App\Http\Controllers\User\AttendanceStatusController as UserAttendanceStatusController;

// 管理者側
use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\AttendanceController as AdminAttendanceController;
use App\Http\Controllers\Admin\AttendanceActionController as AdminAttendanceActionController;

// 一般ユーザー認証系
Route::post('/api/register', [UserAuthController::class, 'register']);
Route::post('/api/login', [UserAuthController::class, 'login']);
Route::post('/api/logout', [UserAuthController::class, 'logout']);

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

    // ---- 打刻系（出勤・退勤・休憩）----
    Route::post('/api/attendance/clock', [UserAttendanceActionController::class, 'clock']);
    Route::post('/api/attendance/update-or-create/{id}', [UserAttendanceActionController::class, 'updateOrCreate']);

    // ---- 勤怠データ参照系 ----
    Route::get('/api/attendance/list', [UserAttendanceController::class, 'getList']);
    Route::get('/api/attendance/detail/{id}', [UserAttendanceController::class, 'getDetail']);
    Route::get('/api/attendance/requests', [UserAttendanceController::class, 'getRequestList']);
});

// 勤怠API（管理者用）
Route::prefix('api/admin')
    ->middleware(['web', 'auth:admin'])
    ->group(function () {
        Route::get('/attendance/list', [AdminAttendanceController::class, 'getList']);
        Route::get('/attendance/{id}', [AdminAttendanceController::class, 'getDetail']);

        Route::post('/attendance/update-or-create/{id}', [AdminAttendanceActionController::class, 'updateOrCreate']);

        // Route::get('/attendance/staff/{id}', [AdminAttendanceController::class, 'showByStaff']);
    });
