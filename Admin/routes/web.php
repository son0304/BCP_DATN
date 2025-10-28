<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\{
    AvailabilityController,
    HomeController,
    CourtController,
    ReviewController,
    UserController,
    BookingController,
    AuthController,
    VenueController
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

// Email verification
Route::get('/verify-email/{token}', [AuthController::class, 'verifyEmail'])->name('verify.email');
Route::post('/resend-verification', [AuthController::class, 'resendVerification'])->name('resend.verification');

// Public pages
Route::get('/', [HomeController::class, 'index'])->name('home.index');
Route::get('/courts', [CourtController::class, 'index'])->name('courts.index');
Route::get('/users', [UserController::class, 'index'])->name('users.index');
Route::get('/reviews', [ReviewController::class, 'index'])->name('reviews.index');

// ==============================
// ====== AUTHENTICATED ROUTES ======
// ==============================
Route::middleware(['auth'])->group(function () {
    Route::get('venue', [VenueController::class, 'index'])->name('venue.index');
    Route::get('venue/{venue}', [VenueController::class, 'showVenueDetail'])->name('venue.show');
    Route::get('venue/{venue}/courts', [CourtController::class, 'indexByVenue'])->name('venue.courts.index');
});

// ==============================
// ====== ADMIN ROUTES ======
// ==============================
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {

    // Dashboard
    Route::get('/', [HomeController::class, 'index'])->name('home.index');

    // Manage Venues
    Route::prefix('venue')->name('venue.')->group(function () {
        Route::patch('{venue}/update-status', [VenueController::class, 'updateStatus'])->name('updateStatus');
    });

    // Manage Users
    Route::resource('users', UserController::class);
    Route::post('users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');

    // Manage Reviews
    Route::get('reviews', [ReviewController::class, 'index'])->name('reviews.index');
});

// ==============================
// ====== VENUE OWNER ROUTES ======
// ==============================
Route::middleware(['auth', 'role:venue_owner'])->group(function () {

    // Venue CRUD
    Route::prefix('venue')->name('venue.')->group(function () {
        Route::get('create', [VenueController::class, 'create'])->name('create');
        Route::post('/', [VenueController::class, 'store'])->name('store');
        Route::get('{venue}/edit', [VenueController::class, 'edit'])->name('edit');
        Route::put('{venue}', [VenueController::class, 'update'])->name('update');
        Route::delete('{venue}', [VenueController::class, 'destroy'])->name('destroy');

        // Courts CRUD nested under venue
        Route::prefix('{venue}/courts')->name('courts.')->group(function () {
            Route::get('create', [CourtController::class, 'create'])->name('create');
            Route::post('/', [CourtController::class, 'store'])->name('store');
            Route::get('{court}', [CourtController::class, 'show'])->name('show');
            Route::get('{court}/edit', [CourtController::class, 'edit'])->name('edit');
            Route::put('{court}', [CourtController::class, 'update'])->name('update');
            Route::delete('{court}', [CourtController::class, 'destroy'])->name('destroy');
        });
    });

    // Update court availabilities
    Route::post('courts/{court}/availabilities/update', [AvailabilityController::class, 'updateAll'])
        ->name('courts.updateAvailabilities');

    // Manage Reviews
    Route::get('reviews', [ReviewController::class, 'index'])->name('reviews.index');

    // Manage Bookings
    Route::resource('bookings', BookingController::class);
});