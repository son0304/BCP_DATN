<?php

use App\Http\Controllers\Api\VenueApiController;
use Illuminate\Support\Facades\Route;

Route::get('/venues', [VenueApiController::class, 'index']);