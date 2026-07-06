<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');
    
use App\Http\Controllers\AdminController;
    
Route::middleware(['auth', 'admin'])->group(function(){
    Route::view('dashboard', 'dashboard')->name('dashboard');
    Route::get('product', [AdminController::class, 'product'])->name('product');
    Route::get('category', [AdminController::class, 'category'])->name('category');
    Route::get('orderPage', [AdminController::class, 'orderPage'])->name('orderPage');
    Route::get('analytics', [AdminController::class, 'analytics'])->name('analytics');
    Route::get('coupon', [AdminController::class, 'coupon'])->name('coupon');
    Route::get('banner', [AdminController::class, 'banner'])->name('banner');
});

require __DIR__.'/settings.php';

use App\Http\Controllers\UserController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('user/checkout/{order_id?}', [UserController::class, 'checkout'])->name('user/checkout');    
    Route::get('user/order', [UserController::class, 'order'])->name('user/order');    
    Route::get('user/order-success/{order_id}', [UserController::class, 'orderSuccess'])->name('user/order-success');
    });
    
    Route::get('user/wishlist', [UserController::class, 'wishlist'])->name('user/wishlist');

Route::get('user/cart', [UserController::class, 'cart'])->name('user/cart');
Route::get('user/homePage', [UserController::class, 'homePage'])->name('homePage');
Route::get('user/product/{category?}', [UserController::class, 'product'])->name('user/product');
Route::get('user/category', [UserController::class, 'category'])->name('user/category');
Route::get('user/productDetails/{slug}', [UserController::class, 'productDetails'])->name('user/productDetails');

use App\Mail\notification;
use Illuminate\Support\Facades\Mail;

use App\Services\CloudinaryService;

Route::get('/cloudinary-test', function () {

$upload = app(CloudinaryService::class)
    ->upload('test.jpg', 'easycart/products');

$imageUrl = $upload['secure_url'];
$publicId = $upload['public_id'];

});

Route::get('/test-auth', function () {
    return auth()->check() ? 'logged in' : 'not logged in';
})->middleware('web');