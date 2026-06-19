<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
});


use App\Http\Controllers\AdminController;

Route::middleware(['auth', 'admin'])->group(function(){
    Route::get('category', [AdminController::class, 'category'])->name('category');
    Route::get('product', [AdminController::class, 'product'])->name('product');
});

require __DIR__.'/settings.php';
