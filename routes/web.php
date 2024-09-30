<?php

use App\Livewire\Auth\Login;
use App\Livewire\Auth\ResetPassword;
use Illuminate\Support\Facades\Mail;
use App\Livewire\Auth\ChangePassword;
use App\Livewire\Auth\ForgotPassword;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DtrTestController;
use App\Http\Controllers\TestingController;
use App\Http\Controllers\Testing2Controller;
use App\Http\Controllers\Pdf\QrCodeController;

Route::redirect('/', '/sign-in');
// Route::get('/testing', TestingController::class);
// Route::get('/testing2', Testing2Controller::class);
// Route::get('/test-dtr', DtrTestController::class);

Route::get('/notification', function () {
    $user = \App\Models\User::where('hris_number', '211515')->first();
    $event = \App\Models\Event::find(1);

    return new \App\Mail\Event\EvaluationResult($event, 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nulla eu neque in ante ullamcorper eleifend ac eu orci. Nulla augue nisi, suscipit sit amet eros in, condimentum ornare felis. Cras id convallis justo. Quisque vitae lectus purus. Donec vestibulum enim in maximus malesuada. Aenean nec ligula feugiat, eleifend urna sed, bibendum felis.');

    // return Mail::to($user)->send(new \App\Mail\Event\Application($event));

    // return (new \App\Notifications\EventRequestApplication([
    //     'evaluation_result' => 'disapproved',
    //     'date' => now(),
    //     'event' => 'Official Leave',
    //     'note' => 'test'
    // ]))->toMail($user);
});


Route::get('/sign-in', Login::class)->middleware('guest')->name('login');
Route::get('/forgot-password', ForgotPassword::class)->middleware('guest')->name('forgot-password');
Route::get('/reset-password/{token}', ResetPassword::class)->middleware('guest')->name('forgot-password.reset');

Route::middleware('auth')->group(function () {
    Route::get('/calendar/test', App\Livewire\AdminCoord\Events\Index::class)->name('calendar.test');
    Route::get('/home', App\Livewire\Home::class)->middleware('checkpassword')->name('home');
    Route::get('/auth/change-default-password', ChangePassword::class)->name('auth.change-password');
    Route::get('/logout', function () {
        Illuminate\Support\Facades\Auth::logout();
        return redirect('/');
    })->name('logout');

    Route::get('/profile/pdf/{employee}/qr-code', [QrCodeController::class, 'employee'])->name('pdf.empqrcode');
    Route::get('/bulk/qr-code', [QrCodeController::class, 'bulk'])->name('pdf.bulkqrcode');

    // Employee modules

    Route::prefix('employee')->group(function () {
        Route::get('/time-entries', App\Livewire\Employee\TimeEntries::class)->name('employee.time-entries');
        Route::get('/dtr-report', App\Livewire\Employee\DtrReport::class)->name('employee.dtr-report');
        Route::get('/work-from-home', App\Livewire\Employee\WorkFromHome::class)->middleware('haswfhsched')->name('employee.work-from-home');
        Route::get('/profile/identity-photo', App\Livewire\Profile\IdentityPhoto::class)->name('employee.identity-photo');
        Route::get('/profile/electronic-signature', App\Livewire\Profile\ElectronicSignature::class)->name('employee.electronic-signature');
        Route::get('/profile/qr-code', App\Livewire\Profile\QrCode::class)->name('employee.qr-code');

        Route::get('/leave-flexible-schedule-application', App\Livewire\Employee\LeaveFlexiApplication\Index::class)->name('employee.leave-flexible-schedule-application.index');
    });

    // Admin Coord modules

    Route::prefix('admin')->middleware(['checkrole:admincoord,centeradmincoord,groupadmincoord', 'checkcenter:PASIG'])->group(function () {
        Route::get('/official-time', App\Livewire\AdminCoord\OfficialTime::class)->name('admin.official-time');

        // Route::get('/event-calendar', App\Livewire\AdminCoord\Events\Index::class)->name('admin.event-calendar');
        // Route::get('/event-calendar', App\Livewire\AdminCoord\EventCalendar::class)->name('admin.event-calendar');
        Route::get('/event-calendar', App\Livewire\AdminCoord\Events\Index::class)->name('admin.event-calendar');
        Route::get('/event-calendar/{mov}/view-mov', App\Http\Controllers\Pdf\ShowMovController::class)->name('admin.pdf.view-mov');

        Route::get('/daily-time-records', App\Livewire\AdminCoord\DailyTimeRecords::class)->name('admin.dtr.index');
        Route::get('/daily-time-records/{hris_number}/time-entries', App\Livewire\AdminCoord\EmployeeTimeEntries::class)->name('admin.dtr.emp-time-entries');
        Route::get('/daily-time-records/employee/{hris_number}/dtr-report', [App\Http\Controllers\Pdf\DtrReportController::class, 'individual'])->name('admin.dtr.emp-dtr-report');
        Route::get('/daily-time-records/bulk/{department}/dtr-report', [App\Http\Controllers\Pdf\DtrReportController::class, 'bulk'])->name('admin.dtr.bulk-dtr-report');
        Route::get('/daily-time-records/{mov}/view-mov', App\Http\Controllers\Pdf\ShowMovController::class)->name('admin.dtr.pdf.view-mov');
        // Route::get('/dtr-report', App\Livewire\Employee\DtrReport::class)->name('employee.dtr-report');
    });

    // HR Admin modules

    Route::prefix('hr-admin')->middleware(['checkrole:hradmin', 'checkcenter:PASIG'])->group(function () {
        Route::get('/master-list', App\Livewire\HrAdmin\EmployeeMasterlist::class)->name('hr-admin.master-list');
        Route::get('/dtr-report', App\Livewire\HrAdmin\GenerateDtrReport::class)->name('hr-admin.generate-dtr-report.index');
        Route::get('/dtr-report/employee/{hris_number}/dtr-report', [App\Http\Controllers\Pdf\DtrReportController::class, 'individual'])->name('hradmin.dtr.emp-dtr-report');
        Route::get('/dtr-report/bulk/{department}/dtr-report', [App\Http\Controllers\Pdf\DtrReportController::class, 'bulk'])->name('hradmin.dtr.bulk-dtr-report');
        Route::get('/official-time/change-requests', App\Livewire\HrAdmin\OfficialTimeChangeRequest\Index::class)->name('hr-admin.official-time-change-req.index');
        Route::get('/events', App\Livewire\HrAdmin\Events\Index::class)->name('hr-admin.events.index');
        Route::get('/time-entries', App\Livewire\HrAdmin\TimeEntries::class)->name('hr-admin.time-entries.index');
        // Route::get('/dtr-report', App\Livewire\Employee\DtrReport::class)->name('employee.dtr-report');
    });

    Route::prefix('dapcc')->middleware('checkcenter:DAPCC')->group(function () {
        // DAPCC HR Admin modules
        Route::prefix('hr-admin')->middleware('checkrole:hradmin')->group(function () {
            Route::get('/master-list', App\Livewire\Dapcc\HrAdmin\EmployeeMasterlist::class)->name('dapcc.hr-admin.master-list');
            Route::get('/events', App\Livewire\Dapcc\HrAdmin\Events\Index::class)->name('dapcc.hr-admin.events');
            Route::get('/dtr-report', App\Livewire\Dapcc\HrAdmin\GenerateDtrReports::class)->name('dapcc.hr-admin.generate-dtr-report');
            Route::get('/dtr-report/employee/{hris_number}/dtr-report', [App\Http\Controllers\Pdf\DtrReportController::class, 'dapcc_individual'])->name('dapcc.hradmin.dtr.emp-dtr-report');
            Route::get('/dtr-report/bulk/{department}/dtr-report', [App\Http\Controllers\Pdf\DtrReportController::class, 'dapcc_bulk'])->name('dapcc.hradmin.dtr.bulk-dtr-report');
            Route::get('/time-entries', App\Livewire\HrAdmin\TimeEntries::class)->name('dapcc.hr-admin.time-entries.index');
        });

        // DAPCC Admin Coordinator
        Route::prefix('admin')->middleware('checkrole:admincoord,centeradmincoord,groupadmincoord')->group(function () {
            Route::get('/event-calendar', App\Livewire\Dapcc\AdminCoord\Events\Index::class)->name('dapcc.admin.event-calendar');
            Route::get('/daily-time-records', App\Livewire\Dapcc\AdminCoord\DailyTimeRecords::class)->name('dapcc.admin.dtr.index');
            // Route::get('/daily-time-records/{hris_number}/time-entries', App\Livewire\AdminCoord\EmployeeTimeEntries::class)->name('admin.dtr.emp-time-entries');
            Route::get('/daily-time-records/employee/{hris_number}/dtr-report', [App\Http\Controllers\Pdf\DtrReportController::class, 'dapcc_individual'])->name('dapcc.admin.dtr.emp-dtr-report');
            Route::get('/daily-time-records/bulk/{department}/dtr-report', [App\Http\Controllers\Pdf\DtrReportController::class, 'dapcc_bulk'])->name('dapcc.admin.dtr.bulk-dtr-report');
        });
    });

    // Route::get('/user/identity-photo', App\Livewire\Profile\IdentityPhoto::class)->name('user.photo');
});
