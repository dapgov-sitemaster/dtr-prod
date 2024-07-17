<?php

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

    // Route::get('/user/identity-photo', App\Livewire\Profile\IdentityPhoto::class)->name('user.photo');
});
