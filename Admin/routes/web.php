<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\{
    HomeController,
    CourtController,
    ReviewController,
    UserController,
    BrandController,
    BookingController
};

Route::prefix('admin')->name('admin.')->group(function () {
    // Trang dashboard admin
    Route::get('/', [HomeController::class, 'index'])->name('home.index');

    // Quản lý sân
    Route::resource('courts', CourtController::class);

    // Quản lý người dùng
    Route::get('/users', [UserController::class, 'index'])->name('users.index');

    // Quản lý đánh giá
    Route::get('/reviews', [ReviewController::class, 'index'])->name('reviews.index');

    // Quản lý thương hiệu sân (venue)
    Route::resource('brand', BrandController::class)->parameter('brand', 'venue');

    // Quản lý đơn đặt (booking)
    Route::resource('bookings', BookingController::class);
});
