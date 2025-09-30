<?php

use Illuminate\Support\Facades\Route;

Route::get('/', App\Livewire\LandingPage::class)->name('landing');
