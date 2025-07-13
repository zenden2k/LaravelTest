<?php

use App\Http\Controllers\Api\ProductController;
use Illuminate\Support\Facades\Route;
Route::get('/catalog', [ProductController::class, 'index']);




