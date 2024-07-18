<?php

use App\Http\Controllers\Pdf\QrCodeController;
use App\Livewire\Auth\Login;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/sign-in');
Route::get('/sign-in', Login::class)->name('login');

Route::middleware('auth')->group(function() {
    Route::get('/home', App\Livewire\Home::class)->name('home');
    Route::get('/logout', function(){
        Illuminate\Support\Facades\Auth::logout();
        return redirect('/');
    })->name('logout');

    Route::get('/profile/pdf/qr-code', [QrCodeController::class, 'employee'])->name('pdf.empqrcode');

    // Route::get('/user/identity-photo', App\Livewire\Profile\IdentityPhoto::class)->name('user.photo');
});
