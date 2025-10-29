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

// Auth
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
Route::get('/reviews', [ReviewController::class, 'index'])->name('reviews.index');


// ==============================
// ====== ADMIN ROUTES ======
// ==============================
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {

    // Dashboard
    Route::get('/', [HomeController::class, 'dashboard'])->name('dashboard');

    // --- USERS MANAGEMENT ---
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::get('create', [UserController::class, 'create'])->name('create'); // <-- Đặt trước
        Route::post('/', [UserController::class, 'store'])->name('store');
        Route::get('{user}', [UserController::class, 'show'])->name('show');
        Route::get('{user}/edit', [UserController::class, 'edit'])->name('edit');
        Route::put('{user}', [UserController::class, 'update'])->name('update');
        Route::delete('{user}', [UserController::class, 'destroy'])->name('destroy');
        Route::post('{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('toggle-status');
    });

    // --- VENUES MANAGEMENT (Admin chỉ xem & update trạng thái, không tạo/xóa sân) ---
    Route::prefix('venues')->name('venues.')->group(function () {
        Route::get('/', [VenueController::class, 'index'])->name('index');
        Route::get('{venue}', [VenueController::class, 'showVenueDetail'])->name('show');
        Route::patch('{venue}/update-status', [VenueController::class, 'updateStatus'])->name('update-status');

        // --- COURTS NESTED UNDER VENUE ---
        Route::prefix('{venue}/courts')->name('courts.')->group(function () {
            Route::get('create', [CourtController::class, 'create'])->name('create');
            Route::post('/', [CourtController::class, 'store'])->name('store');
            Route::get('{court}', [CourtController::class, 'show'])->name('show');
        });
    });

    // --- REVIEWS MANAGEMENT (Admin xem tất cả) ---
    Route::prefix('reviews')->name('reviews.')->group(function () {
        Route::get('/', [ReviewController::class, 'index'])->name('index');
        Route::get('{review}', [ReviewController::class, 'show'])->name('show');
        Route::delete('{review}', [ReviewController::class, 'destroy'])->name('destroy');
    });
});


// ==============================
// ====== VENUE OWNER ROUTES ======
// ==============================
Route::middleware(['auth', 'role:venue_owner'])->prefix('owner')->name('owner.')->group(function () {

    // Dashboard
    Route::get('/', [HomeController::class, 'dashboard'])->name('dashboard');

    // --- VENUES CRUD (Owner chỉ thao tác với sân của mình) ---
    Route::prefix('venues')->name('venues.')->group(function () {
        Route::get('/', [VenueController::class, 'index'])->name('index'); // danh sách venues của owner
        Route::get('create', [VenueController::class, 'create'])->name('create');
        Route::post('/', [VenueController::class, 'store'])->name('store');
        Route::get('{venue}/edit', [VenueController::class, 'edit'])->name('edit');
        Route::put('{venue}', [VenueController::class, 'update'])->name('update');
        Route::delete('{venue}', [VenueController::class, 'destroy'])->name('destroy');
        Route::get('{venue}', [VenueController::class, 'showVenueDetail'])->name('show');

        // --- COURTS NESTED UNDER VENUE ---
        Route::prefix('{venue}/courts')->name('courts.')->group(function () {
            Route::get('create', [CourtController::class, 'create'])->name('create');
            Route::post('/', [CourtController::class, 'store'])->name('store');
            Route::get('{court}', [CourtController::class, 'show'])->name('show');
            Route::get('{court}/edit', [CourtController::class, 'edit'])->name('edit');
            Route::put('{court}', [CourtController::class, 'update'])->name('update');
            Route::delete('{court}', [CourtController::class, 'destroy'])->name('destroy');
        });
    });

    // --- COURT AVAILABILITIES ---
    Route::post('courts/{court}/availabilities/update', [AvailabilityController::class, 'updateAll'])
        ->name('courts.updateAvailabilities');

    // --- REVIEWS MANAGE BY OWNER ---
    Route::prefix('reviews')->name('reviews.')->group(function () {
        Route::get('/', [ReviewController::class, 'indexByOwner'])->name('index');
    });

    // --- BOOKINGS MANAGE BY OWNER ---
    Route::prefix('bookings')->name('bookings.')->group(function () {
        Route::get('/', [BookingController::class, 'index'])->name('index');
        Route::get('{booking}', [BookingController::class, 'show'])->name('show');
        Route::post('/', [BookingController::class, 'store'])->name('store');
        Route::put('{booking}', [BookingController::class, 'update'])->name('update');
        Route::delete('{booking}', [BookingController::class, 'destroy'])->name('destroy');
    });
});