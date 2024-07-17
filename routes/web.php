<?php

use App\Livewire\Auth\Login;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/sign-in');
Route::get('/sign-in', Login::class)->name('login');
