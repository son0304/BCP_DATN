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
    TicketController,
    AuthController
};

// ==============================
// ====== AUTH & PUBLIC ROUTES ======
// ==============================

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
Route::get('/', [HomeController::class, 'index'])->name('home.index');
Route::get('/courts', [CourtController::class, 'index'])->name('courts.index');
Route::get('/users', [UserController::class, 'index'])->name('users.index');
Route::get('/reviews', [ReviewController::class, 'index'])->name('reviews.index');


// ==============================
// ====== ADMIN ROUTES ======
// ==============================
Route::prefix('admin')->name('admin.')->middleware(['auth'])->group(function () {
    // Dashboard - Admin và Chủ sân đều có thể truy cập
    Route::get('/', [HomeController::class, 'index'])->name('home.index');

    // ====== ADMIN ONLY ROUTES ======
    Route::middleware(['role:admin'])->group(function () {
        Route::resource('brand', BrandController::class);

        Route::get('/users', [UserController::class, 'index'])->name('users.index');

        Route::get('/reviews', [ReviewController::class, 'index'])->name('reviews.index');

        Route::resource('bookings', BookingController::class);
    });

    // ====== VENUE OWNER ROUTES ======
    Route::middleware(['role:admin,venue_owner'])->group(function () {
        Route::resource('courts', CourtController::class);
        Route::post('/courts/{court}/availabilities/update', [AvailabilityController::class, 'updateAll'])
            ->name('courts.updateAvailabilities');
    });
});
