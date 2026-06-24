<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');
    
use App\Http\Controllers\AdminController;
    
Route::middleware(['auth', 'admin'])->group(function(){
    Route::view('dashboard', 'dashboard')->name('dashboard');
    Route::get('product', [AdminController::class, 'product'])->name('product');
    Route::get('category', [AdminController::class, 'category'])->name('category');
});

require __DIR__.'/settings.php';

use App\Http\Controllers\UserController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('user/checkout/{order_id?}', [UserController::class, 'checkout'])->name('user/checkout');    
    Route::get('user/order', [UserController::class, 'order'])->name('user/order');    
    Route::get('user/order-success/{order_id}', [UserController::class, 'orderSuccess'])->name('user/order-success');
});


Route::get('user/cart', [UserController::class, 'cart'])->name('user/cart');
Route::get('user/homePage', [UserController::class, 'homePage'])->name('homePage');
Route::get('user/product', [UserController::class, 'product'])->name('user/product');
Route::get('user/category', [UserController::class, 'category'])->name('user/category');
Route::get('user/product/{slug}', [UserController::class, 'productDetails'])->name('user/productDetails');