<?php

use Illuminate\Support\Facades\Route;

Route::get('/zakazivanje', function () {
    return view('booking');
})->name('booking');
