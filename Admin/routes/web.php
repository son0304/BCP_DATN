<?php

use App\Http\Controllers\Web\CourtController;
use App\Http\Controllers\Web\HomeController;
use App\Http\Controllers\Web\ReviewController;
use App\Http\Controllers\Web\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\BookingController;

Route::get('/', function () {
    return view('app');
});
Route::get('/', [HomeController::class, 'index'])-> name('home.index');
Route::get('/courts', [CourtController::class, 'index'])-> name('courts.index');
Route::get('/users', [UserController::class, 'index'])-> name('users.index');
Route::get('/reviews', [ReviewController::class, 'index'])-> name('reivews.index');




Route::prefix('admin')->middleware(['auth'])->group(function () {
    Route::get('/bookings', [BookingController::class, 'index'])->name('admin.bookings.index');
    Route::get('/bookings/{id}', [BookingController::class, 'show'])->name('admin.bookings.show');
    Route::get('/bookings/{id}/edit', [BookingController::class, 'edit'])->name('admin.bookings.edit');
    Route::put('/bookings/{id}', [BookingController::class, 'update'])->name('admin.bookings.update');
});
