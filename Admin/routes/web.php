<?php

use App\Http\Controllers\Web\CourtController;
use App\Http\Controllers\Web\HomeController;
use App\Http\Controllers\Web\ReviewController;
use App\Http\Controllers\Web\UserController;
use App\Http\Controllers\Web\BrandController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('app');
});
Route::get('/', [HomeController::class, 'index'])->name('home.index');
Route::resource('courts', CourtController::class);
Route::get('/users', [UserController::class, 'index'])->name('users.index');
Route::get('/reviews', [ReviewController::class, 'index'])->name('reivews.index');
Route::resource('brand', BrandController::class)->parameter('brand', 'venue');
