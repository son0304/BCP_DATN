<?php

use App\Http\Controllers\Api\CourtApiController;
use App\Http\Controllers\Api\TicketApiController;
use App\Http\Controllers\Api\TimeSlotApiController;
use App\Http\Controllers\Api\VenueApiController;
use Illuminate\Support\Facades\Route;

Route::get('/venues', [VenueApiController::class, 'index']);
Route::post('/venues', [VenueApiController::class, 'store']);
Route::get('/venue/{id}', [VenueApiController::class, 'show']);

Route::get('/courts', [CourtApiController::class, 'index']);
Route::get('/court/{id}', [CourtApiController::class, 'show']);


Route::get('/tickets', [TicketApiController::class, 'index']);
Route::get('/ticket/{id}', [TicketApiController::class, 'show']);
Route::post('/tickets', [TicketApiController::class, 'store']);

Route::get('/ticket', [TicketApiController::class, 'store']);



Route::get('/time_slots', [TimeSlotApiController::class, 'index']);