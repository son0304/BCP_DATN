<?php

use App\Http\Controllers\Web\CourtController;
use App\Http\Controllers\Web\HomeController;
use App\Http\Controllers\Web\ReviewController;
use App\Http\Controllers\Web\UserController;
use App\Http\Controllers\Web\AuthController;
use Illuminate\Support\Facades\Route;

// Auth Routes
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Email verification routes
Route::get('/verify-email/{token}', [AuthController::class, 'verifyEmail'])->name('verify.email');
Route::post('/resend-verification', [AuthController::class, 'resendVerification'])->name('resend.verification');

// Main Routes
Route::get('/', function () {
    return view('app');
});
Route::get('/', [HomeController::class, 'index'])-> name('home.index');
Route::get('/courts', [CourtController::class, 'index'])-> name('courts.index');
Route::get('/users', [UserController::class, 'index'])-> name('users.index');
Route::get('/reviews', [ReviewController::class, 'index'])-> name('reivews.index');
