<?php

use Illuminate\Support\Facades\Route;

Route::get('/', App\Livewire\LandingPage::class)->name('dashboard')->middleware('auth');

Route::group(['prefix' => 'auth'], function () {
    Route::get('login', \App\Livewire\Auth\Login::class)->name('login')->middleware('guest');
    Route::get('forgot-password', \App\Livewire\Auth\ForgotPassword::class)->name('forgot-password')->middleware('guest');
    Route::get('reset-password', \App\Livewire\Auth\ResetPassword::class)->name('reset-password')->middleware('guest');
    Route::get('2fa', \App\Livewire\Auth\TwoFactorsAuthentication::class)->name('2fa')->middleware('guest');
    Route::get('logout', \App\Livewire\Auth\Logout::class)->name('logout')->middleware('auth');
});
