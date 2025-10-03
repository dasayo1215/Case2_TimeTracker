<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::post('/api/register', [AuthController::class, 'register']);
Route::post('/api/login', [AuthController::class, 'login']);
Route::post('/api/admin/login', [AuthController::class, 'login']);
Route::post('/api/logout', [AuthController::class, 'logout']);
