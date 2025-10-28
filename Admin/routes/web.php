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

// Email verification routes
Route::get('/verify-email/{token}', [AuthController::class, 'verifyEmail'])->name('verify.email');
Route::post('/resend-verification', [AuthController::class, 'resendVerification'])->name('resend.verification');



// ==============================
// ====== AUTHENTICATED ROUTES ======
// ==============================


Route::middleware(['auth'])->group(function () {
    Route::get('/', [HomeController::class, 'index'])->name('home.index');
    Route::get('venue', [VenueController::class, 'index'])->name('venue.index');
    Route::get('venue/{venue}', [VenueController::class, 'showVenueDetail'])
        ->name('venue.show');
    Route::get('venue/{venue}/courts', [CourtController::class, 'indexByVenue'])
        ->name('venue.courts.index');
});


// ====== ADMIN ======
Route::middleware(['role:admin'])->prefix('admin/venue')->name('admin.venue.')->group(function () {
    Route::patch('{venue}/update-status', [VenueController::class, 'updateStatus'])
        ->name('updateStatus');
});

Route::prefix('users')->name('users.')->group(function () {
    Route::get('/', [UserController::class, 'index'])->name('index');
    Route::get('/{user}/edit', [UserController::class, 'edit'])->name('edit');
    Route::put('/{user}', [UserController::class, 'update'])->name('update');
    Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');
});

Route::get('/reviews', [ReviewController::class, 'index'])->name('reviews.index');

Route::resource('bookings', BookingController::class);


// ====== VENUE OWNER ======
Route::middleware(['role:venue_owner'])->group(function () {
    Route::get('venue/create', [VenueController::class, 'create'])->name('venue.create');
    Route::post('venue', [VenueController::class, 'store'])->name('venue.store');
    Route::get('venue/{venue}/edit', [VenueController::class, 'edit'])->name('venue.edit');
    Route::put('venue/{venue}', [VenueController::class, 'update'])->name('venue.update');
    Route::delete('venue/{venue}', [VenueController::class, 'destroy'])->name('venue.destroy');

    // --- QUẢN LÝ SÂN (COURTS) ---
    // Danh sách sân theo venue

    // Form thêm sân mới (có thể nhận venue_id qua query)
    Route::get('venue/{venue}/courts/create', [CourtController::class, 'create'])->name('courts.create');
    Route::post('courts', [CourtController::class, 'store'])->name('courts.store');
    // Chi tiết sân trong venue cụ thể
    Route::get('venue/{venue}/courts/{court}', [CourtController::class, 'show'])
        ->name('venue.courts.show');
    // Form sửa sân (gắn với venue)
    Route::get('venue/{venue}/courts/{court}/edit', [CourtController::class, 'edit'])
        ->name('venue.courts.edit');
    // Cập nhật sân
    Route::put('venue/{venue}/courts/{court}', [CourtController::class, 'update'])
        ->name('venue.courts.update');
    // Xóa sân
    Route::delete('venue/{venue}/courts/{court}', [CourtController::class, 'destroy'])
        ->name('venue.courts.destroy');
    // Cập nhật khung giờ khả dụng
    Route::post('courts/{court}/availabilities/update', [AvailabilityController::class, 'updateAll'])
        ->name('courts.updateAvailabilities');


    // Đánh giá & Đơn đặt sân
    Route::get('/reviews', [ReviewController::class, 'index'])->name('reviews.index');
    Route::resource('bookings', BookingController::class);
});
