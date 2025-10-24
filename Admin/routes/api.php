<?php

use App\Http\Controllers\Api\VenueApiController;
use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;

// API Route cho đăng ký
Route::post('/auth/register', [AuthController::class, 'register']);

