<?php

use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProductController;
use Illuminate\Support\Facades\Route;

Route::get('/catalog', [ProductController::class, 'index']);
Route::post('/create-order', [OrderController::class, 'store']);




