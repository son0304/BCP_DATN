<?php

use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\DistrictApiController;
use App\Http\Controllers\Api\ImageApiController;
use App\Http\Controllers\Api\ProvinceApiController;
use App\Http\Controllers\Api\TicketApiController;
use App\Http\Controllers\Api\TimeSlotApiController;
use App\Http\Controllers\Api\VenueApiController;
use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthApiController::class, 'register']);
Route::post('/login', [AuthApiController::class, 'login']);
Route::post('/logout', [AuthApiController::class, 'logout']);
Route::post('/verify-email', [AuthApiController::class, 'verifyEmail']);





Route::get('/venues', [VenueApiController::class, 'index']);
Route::post('/venues', [VenueApiController::class, 'store']);
Route::get('/venue/{id}', [VenueApiController::class, 'show']);

// Route::get('/courts', [CourtApiController::class, 'index']);
// Route::get('/court/{id}', [CourtApiController::class, 'show']);


Route::get('/tickets', [TicketApiController::class, 'index']);
Route::get('/ticket/{id}', [TicketApiController::class, 'show']);
Route::post('/tickets', [TicketApiController::class, 'store']);

Route::get('/ticket', [TicketApiController::class, 'store']);

Route::post('/upload', [ImageApiController::class, 'store']);


Route::get('/time_slots', [TimeSlotApiController::class, 'index']);


Route::get('/provinces', [ProvinceApiController::class, 'index']);
Route::get('/province/{id}', [ProvinceApiController::class, 'show']);

Route::get('/districts', [DistrictApiController::class, 'index']);
Route::get('/district/{id}', [DistrictApiController::class, 'show']);