<?php

use App\Http\Controllers\Pdf\QrCodeController;
use App\Livewire\Auth\Login;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/sign-in');
Route::get('/sign-in', Login::class)->name('login');

Route::middleware('auth')->group(function () {
    Route::get('/home', App\Livewire\Home::class)->name('home');
    Route::get('/logout', function () {
        Illuminate\Support\Facades\Auth::logout();
        return redirect('/');
    })->name('logout');

    Route::get('/profile/pdf/qr-code', [QrCodeController::class, 'employee'])->name('pdf.empqrcode');

    // Employee modules

    Route::prefix('employee')->group(function () {
        Route::get('/time-entries', App\Livewire\Employee\TimeEntries::class)->name('employee.time-entries');
        Route::get('/dtr-report', App\Livewire\Employee\DtrReport::class)->name('employee.dtr-report');
    });

    // Admin Coord modules

    Route::prefix('admin')->group(function () {
        Route::get('/official-time', App\Livewire\AdminCoord\OfficialTime::class)->name('admin.official-time');

        Route::get('/event-calendar', App\Livewire\AdminCoord\EventCalendar::class)->name('admin.event-calendar');
        Route::get('/event-calendar/{mov}/view-mov', App\Http\Controllers\Pdf\ShowMovController::class)->name('admin.pdf.view-mov');

        Route::get('/daily-time-records', App\Livewire\AdminCoord\DailyTimeRecords::class)->name('admin.dtr.index');
        Route::get('/daily-time-records/{hris_number}/time-entries', App\Livewire\AdminCoord\EmployeeTimeEntries::class)->name('admin.dtr.emp-time-entries');
        Route::get('/daily-time-records/{hris_number}/dtr-report', [App\Http\Controllers\Pdf\DtrReportController::class, 'individual'])->name('admin.dtr.emp-dtr-report');
        // Route::get('/dtr-report', App\Livewire\Employee\DtrReport::class)->name('employee.dtr-report');
    });

    // HR Admin modules

    Route::prefix('hr-admin')->group(function () {
        Route::get('/master-list', App\Livewire\HrAdmin\EmployeeMasterlist::class)->name('hr-admin.master-list');
        // Route::get('/dtr-report', App\Livewire\Employee\DtrReport::class)->name('employee.dtr-report');
    });

    // Route::get('/user/identity-photo', App\Livewire\Profile\IdentityPhoto::class)->name('user.photo');
});
