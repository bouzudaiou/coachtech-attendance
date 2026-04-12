<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminAttendanceController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AdminStaffController;
use App\Http\Controllers\StampCorrectionRequestController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/admin/login', [\App\Http\Controllers\AuthController::class, 'showLoginForm']);
Route::post('/admin/login', [\App\Http\Controllers\AuthController::class, 'login']);

// 管理者用ルート
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin/attendance/list', [AdminAttendanceController::class, 'index']);
    Route::get('/admin/attendance/{id}', [AdminAttendanceController::class, 'show']);
    Route::post('/admin/attendance/{id}', [AdminAttendanceController::class, 'update']);
    Route::get('/admin/staff/list', [AdminStaffController::class, 'index']);
    Route::get('/admin/attendance/staff/{id}', [AdminStaffController::class, 'show']);
    Route::get('/admin/attendance/staff/{id}/export', [AdminStaffController::class, 'export']);
    Route::post('/stamp_correction_request/approve/{attendance_correct_request_id}', [StampCorrectionRequestController::class, 'update']);
});

// 申請関連（一般・管理者共通）
Route::middleware(['auth'])->group(function () {
    Route::get('/stamp_correction_request/list', [StampCorrectionRequestController::class, 'index']);
    Route::get('/stamp_correction_request/approve/{attendance_correct_request_id}', [StampCorrectionRequestController::class, 'show']);
});

// 一般ユーザー用ルート
Route::middleware(['auth', 'verified', 'user'])->group(function () {
    Route::get('/attendance', [AttendanceController::class, 'index']);
    Route::post('/attendance', [AttendanceController::class, 'store']);
    Route::get('/attendance/list', [AttendanceController::class, 'list']);
    Route::get('/attendance/detail/{id}', [AttendanceController::class, 'show']);
    Route::post('/attendance/detail/{id}', [AttendanceController::class, 'store']);
});
