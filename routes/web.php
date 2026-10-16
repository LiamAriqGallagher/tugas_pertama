<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ContohController;
use App\Http\Controllers\HomeController;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/cart', [HomeController::class, 'cart'])->name('cart');

Route::get('/products', [HomeController::class, 'products'])->name('products');

Route::get('/checkout', [HomeController::class, 'checkout'])->name('checkout');

Route::get('/coba', [ContohController::class, 'coba']);

Route::get('/contoh', [ContohController::class, 'index']);
Route::resource('products-resource', ProductController::class);