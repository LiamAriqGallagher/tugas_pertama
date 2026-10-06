<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    // return view('welcome');
    return ('ini route utama');
});

Route::get('/products', function () {
    return ('ini route products');
});

Route::get('/cart', function () {
    return ('ini route cart');
});

Route::get('/checkout', function () {
    return ('ini route checkout');
});