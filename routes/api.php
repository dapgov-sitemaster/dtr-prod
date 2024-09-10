<?php

use App\Http\Controllers\Api\V1\AttendanceController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\TimeEntryController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

// Route::post('/login', [AuthController::class, 'login'])->name('v1.login'); // this is for new qr scanner app, i guess....
Route::prefix('v1')->group(function () {
    Route::post('/login/new', [AuthController::class, 'login'])->name('v1.login');
    Route::post('/mvpool/login', [AuthController::class, 'v1_mvpool_login'])->name('v1.mvpool.login');
});

Route::post('/mvpool/login', [AuthController::class, 'mvpool_login'])->name('mvpool.login');

Route::post('/get_info', [AttendanceController::class, 'info'])->middleware('auth:sanctum')->name('v1.time_entry.info');

Route::post('/time_entry', [AttendanceController::class, 'old_time_capture'])->middleware('auth:sanctum')->name('old.time_entry');
Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    // Route::post('/attendance/employee-info', [AttendanceController::class, 'info']); // this is for qr scanner app, i guess....
    Route::post('/time_entry/capture', [AttendanceController::class, 'old_time_capture'])->name('v1.time_entry');


    Route::post('/attendance/time-capture', [AttendanceController::class, 'time_capture'])->name('pasig.time_entry');
    Route::post('/attendance/dapcc/time-capture', [AttendanceController::class, 'dapcc_time_entry'])->name('dapcc.time_entry');


    Route::post('/mvpool/time_entry/new', [AttendanceController::class, 'mvpool_time_entry'])->name('v1.mvpool.timeentry');
    Route::post('/mvpool/location', [AttendanceController::class, 'location'])->name('v1.location');
    Route::get('/mvpool/time_entries', [AttendanceController::class, 'mvpool_time_entries'])->name('v1.time_entries');
    // Route::get('/mvpool/time_entries', [NewAuthController::class, 'time_entries'])->middleware('auth:sanctum')->name('v1.time_entries');
    // Route::post('/mvpool/location', [NewTimeEntryController::class, 'location'])->middleware('auth:sanctum')->name('v1.location');
    // Route::post('/mvpool/time_entry/new', [NewTimeEntryController::class, 'mvpool_time_entry'])->middleware('auth:sanctum')->name('v1.timeentry');
});
