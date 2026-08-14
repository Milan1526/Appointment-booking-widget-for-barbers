<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('booking');
});

Route::get('/zakazivanje', function () {
    return view('booking');
})->name('booking');
