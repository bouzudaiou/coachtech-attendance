<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminAttendanceController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AdminStaffController;
use App\Http\Controllers\StampCorrectionRequestController


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

Route::get('/admin/login',[\App\Http\Controllers\AuthController::class,'showLoginForm']);
Route::post('/admin/login',[\App\Http\Controllers\AuthController::class,'login']);
Route::get('/admin/attendance/list', [AdminAttendanceController::class, 'index']);
Route::get('/admin/attendance/{id}', [AdminAttendanceController::class, 'show']);
Route::get('/admin/staff/list',[AdminStaffController::class,'index']);
Route::get('/stamp_correction_request/list',[StampCorrectionRequestController::class,'index']);
Route::post('/stamp_correction_request/approve/{attendance_correct_request_id}',[StampCorrectionRequestController::class,'create']);

