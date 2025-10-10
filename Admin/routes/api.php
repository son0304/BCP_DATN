<?php

use App\Http\Controllers\Api\VenueApiController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\BookingController;

Route::get('/venues', [VenueApiController::class, 'index']);



Route::prefix('admin')->group(function () {
    Route::apiResource('bookings', BookingController::class);
});
