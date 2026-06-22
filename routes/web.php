<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified', 'admin'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
});


use App\Http\Controllers\AdminController;

Route::middleware(['auth', 'admin'])->group(function(){
    Route::get('category', [AdminController::class, 'category'])->name('category');
    Route::get('product', [AdminController::class, 'product'])->name('product');
});

require __DIR__.'/settings.php';

use App\Http\Controllers\UserController;


    Route::get('user/homePage', [UserController::class, 'homePage'])->name('homePage');
    Route::get('user/product', [UserController::class, 'product'])->name('user/product');
    Route::get('user/product/{slug}', [UserController::class, 'productDetails'])->name('user/productDetails');
    Route::get('user/category', [UserController::class, 'category'])->name('user/category');