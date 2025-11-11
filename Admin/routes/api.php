<?php

use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\DistrictApiController;
use App\Http\Controllers\Api\ImgeApiController;
use App\Http\Controllers\Api\ProvinceApiController;
use App\Http\Controllers\Api\TicketApiController;
use App\Http\Controllers\Api\TimeSlotApiController;
use App\Http\Controllers\Api\VenueApiController;
use Illuminate\Support\Facades\Route;

// --- Public routes ---
Route::post('/register', [AuthApiController::class, 'register']);
Route::post('/login', [AuthApiController::class, 'login']);
Route::post('/verify-email', [AuthApiController::class, 'verifyEmail']);

// --- Venue routes ---
Route::get('/venues', [VenueApiController::class, 'index']);
Route::get('/venues/{id}', [VenueApiController::class, 'show']);
// Alias cho frontend gọi /venue/{id}
Route::get('/venue/{id}', [VenueApiController::class, 'show']);

// --- Ticket routes ---
Route::get('/tickets', [TicketApiController::class, 'index']);
Route::get('/tickets/{id}', [TicketApiController::class, 'show']);

// --- Time slot routes ---
Route::get('/time_slots', [TimeSlotApiController::class, 'index']);

// --- Province routes ---
Route::get('/provinces', [ProvinceApiController::class, 'index']);
Route::get('/provinces/{id}', [ProvinceApiController::class, 'show']);

// --- Protected routes (JWT auth) ---
Route::middleware(['auth:api'])->group(function () {
    Route::post('/logout', [AuthApiController::class, 'logout']);

    // Venue management
    Route::post('/venues', [VenueApiController::class, 'store']);

    // Ticket management
    Route::post('/tickets', [TicketApiController::class, 'store']);

    // Image upload
    Route::post('/upload', [ImgeApiController::class, 'store']);
});
