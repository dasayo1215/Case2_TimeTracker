<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AttendanceController;

Route::post('/api/register', [AuthController::class, 'register']);
Route::post('/api/login', [AuthController::class, 'login']);
Route::post('/api/admin/login', [AuthController::class, 'login']);
Route::post('/api/logout', [AuthController::class, 'logout']);

// ✅ 現在ログイン中のユーザーを取得（安全版）
Route::get('/api/user', function (Request $request) {
    return response()->json(Auth::user());
})->middleware(['web', 'auth']);

// 勤怠API
Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/api/attendance/status', [AttendanceController::class, 'getStatus']);
    Route::post('/api/attendance/clock', [AttendanceController::class, 'clock']);
    Route::get('/api/attendance/list', [AttendanceController::class, 'getList']);
    Route::get('/api/attendance/detail/{id}', [AttendanceController::class, 'getDetail']);
    Route::post('/api/attendance/update-or-create/{id}', [AttendanceController::class, 'updateOrCreate']);
});
