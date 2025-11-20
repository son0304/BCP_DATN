<?php

use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\DistrictApiController;
use App\Http\Controllers\Api\ImageApiController;
use App\Http\Controllers\Api\PromotionApiController;
use App\Http\Controllers\Api\ProvinceApiController;
use App\Http\Controllers\Api\ReviewApiController;
use App\Http\Controllers\Api\TicketApiController;
use App\Http\Controllers\Api\TimeSlotApiController;
use App\Http\Controllers\Api\VenueApiController;
use App\Http\Controllers\Api\PaymentApiController;
use App\Http\Controllers\Web\LocationController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// ==================== PUBLIC ROUTES ====================
// Những route này không cần token
Route::post('/register', [AuthApiController::class, 'register']);
Route::post('/login', [AuthApiController::class, 'login']);
Route::post('/verify-email', [AuthApiController::class, 'verifyEmail']);

Route::get('/venues', [VenueApiController::class, 'index']);
Route::get('/venue/{id}', [VenueApiController::class, 'show']);

Route::get('/time_slots', [TimeSlotApiController::class, 'index']);

Route::get('/provinces', [ProvinceApiController::class, 'index']);
Route::get('/province/{id}', [ProvinceApiController::class, 'show']);

Route::get('/districts', [DistrictApiController::class, 'index']);
Route::get('/district/{id}', [DistrictApiController::class, 'show']);
Route::get('/districts/{province}', [LocationController::class, 'getDistrictsByProvince']);
Route::get('/ticket/{id}', [TicketApiController::class, 'show']);
Route::get('/tickets', [TicketApiController::class, 'index']);


Route::post('/payment/momo', [PaymentApiController::class, 'paymentMomo']);
Route::post('/payment/momo/ipn', [PaymentApiController::class, 'ipn']);
Route::get('/payment/check-status/{id}', [PaymentApiController::class, 'checkTransactionStatus']);


Route::get('/promotions', [PromotionApiController::class, 'index']);

Route::apiResource('reviews', ReviewApiController::class)
    ->only(['index', 'show']);

// ==================== PROTECTED ROUTES ====================
// Những route này cần JWT token
Route::middleware(['jwt.auth'])->group(function () {

    // Logout
    Route::post('/logout', [AuthApiController::class, 'logout']);

    // Tickets
    Route::get('/tickets', [TicketApiController::class, 'index']);
    // Route::get('/ticket/{id}', [TicketApiController::class, 'show']);
    Route::post('/tickets', [TicketApiController::class, 'store']);

    // Venue (create)
    Route::post('/venues', [VenueApiController::class, 'store']);

    // Upload image
    Route::post('/upload', [ImageApiController::class, 'store']);

    // Reviews (protected actions)
    Route::apiResource('reviews', ReviewApiController::class)
        ->only(['store', 'update', 'destroy']);
});