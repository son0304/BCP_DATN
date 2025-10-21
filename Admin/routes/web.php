<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\{
    AvailabilityController,
    HomeController,
    CourtController,
    ReviewController,
    UserController,
    BrandController,
    BookingController,
    TicketController
};

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [HomeController::class, 'index'])->name('home.index');

    // Quản lý Địa điểm (Venues)
    Route::resource('brand', BrandController::class);

    // Quản lý Sân (Courts) & Lịch (Availabilities)
    Route::resource('courts', CourtController::class);
    Route::post('/courts/{court}/availabilities/update', [AvailabilityController::class, 'updateAll'])
        ->name('courts.updateAvailabilities');

    // Quản lý Booking & Ticket
    Route::resource('bookings', BookingController::class);
    Route::resource('tickets', TicketController::class);
    Route::patch('/tickets/{ticket}/status', [TicketController::class, 'updateStatus'])->name('tickets.updateStatus');

    // Quản lý Người dùng & Đánh giá
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/reviews', [ReviewController::class, 'index'])->name('reviews.index');
});