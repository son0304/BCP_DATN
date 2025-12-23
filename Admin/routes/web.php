<?php

// use App\Http\Controllers\ChatController as ControllersChatController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\Web\{
    AdminStatisticController,
    AvailabilityController,
    HomeController,
    CourtController,
    ReviewController,
    UserController,
    BookingController,
    AuthController,
    ChatController,
    FlashSaleCampaignController,
    FlashSaleItemController,
    OwnerStatisticController,
    VenueController,
    PromotionController,
    ServiceCategoryController,
    ServicesCategorieController,
    ServicesController,
    TransactionController

};
use App\Models\FlashSaleCampaign;

// ==============================
// ====== AUTH & PUBLIC ROUTES ======
// ==============================

//api dia chi
Route::get('/api-proxy/provinces', function () {
    $response = Http::get('https://provinces.open-api.vn/api/?depth=1');
    return $response->json();
});

Route::get('/api-proxy/districts/{code}', function ($code) {
    $response = Http::get("https://provinces.open-api.vn/api/p/{$code}?depth=2");
    return $response->json()['districts'] ?? [];
});
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

    Route::get('/', [AdminStatisticController::class, 'index'])->name('statistics.index');

    Route::prefix('chats')->name('chats.')->group(function () {
        // Danh sách các cuộc hội thoại
        Route::get('/', [ChatController::class, 'index'])->name('index');

        // Chi tiết cuộc hội thoại với một Venue Owner cụ thể
        Route::get('{otherUserId}', [ChatController::class, 'show'])->name('show');

        // Gửi tin nhắn và TẠO Conversation nếu là tin nhắn đầu tiên
        // Đã đổi {conversationId} thành {otherUserId} và sendMessage thành sendOrStartChat
        Route::post('{otherUserId}/send', [ChatController::class, 'sendOrStartChat'])->name('send');
    });

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
        Route::patch('/{id}/update-merchant', [VenueController::class, 'updateMerchant'])->name('update-merchant');



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

    // --- PROMOTIONS MANAGEMENT (Admin quản lý voucher) ---
    Route::prefix('promotions')->name('promotions.')->group(function () {
        Route::get('/', [PromotionController::class, 'index'])->name('index');
        Route::get('create', [PromotionController::class, 'create'])->name('create');
        Route::post('/', [PromotionController::class, 'store'])->name('store');
        Route::get('{promotion}', [PromotionController::class, 'show'])->name('show');
        Route::get('{promotion}/edit', [PromotionController::class, 'edit'])->name('edit');
        Route::put('{promotion}', [PromotionController::class, 'update'])->name('update');
        Route::delete('{promotion}', [PromotionController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('bookings')->name('bookings.')->group(function () {
        Route::get('/', [BookingController::class, 'booking_admin'])->name('index');
    });

    Route::prefix('transactions')->name('transactions.')->group(function () {
        Route::get('/', [TransactionController::class, 'index'])->name('index');
    });

    Route::prefix('flash-sale')->name('flash_sale_campaigns.')->group(function () {
        Route::get('/', [FlashSaleCampaignController::class, 'index'])->name('index');

        Route::get('create', [FlashSaleCampaignController::class, 'create'])->name('create');
        Route::post('store', [FlashSaleCampaignController::class, 'store'])->name('store');
    });
});


// ==============================
// ====== VENUE OWNER ROUTES ======
// ==============================
Route::middleware(['auth', 'role:venue_owner'])->prefix('owner')->name('owner.')->group(function () {

    // Dashboard
    Route::get('/', [OwnerStatisticController::class, 'index'])->name('statistics.index');

    Route::prefix('chats')->name('chats.')->group(function () {
        // Danh sách các cuộc hội thoại
        Route::get('/', [ChatController::class, 'index'])->name('index');

        // Chi tiết cuộc hội thoại với một người dùng cụ thể
        Route::get('{otherUserId}', [ChatController::class, 'show'])->name('show');

        // Gửi tin nhắn và TẠO Conversation nếu là tin nhắn đầu tiên
        // Đã đổi {conversationId} thành {otherUserId} và sendMessage thành sendOrStartChat
        Route::post('{otherUserId}/send', [ChatController::class, 'sendOrStartChat'])->name('send');
    });

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
        Route::get('/', [BookingController::class, 'booking_venue'])->name('index');
        Route::get('{booking}', [BookingController::class, 'show'])->name('show');
        Route::post('/', [BookingController::class, 'store'])->name('store');
        Route::put('{booking}', [BookingController::class, 'update'])->name('update');
        Route::delete('{booking}', [BookingController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('flash-sale')->name('flash_sale_campaigns.')->group(function () {
        Route::get('/', [FlashSaleCampaignController::class, 'index'])->name('index');
        Route::get('show/{id}', [FlashSaleCampaignController::class, 'show'])->name('show');
        Route::post('store', [FlashSaleItemController::class, 'create_flash_sale_items'])->name('store');
    });

    Route::prefix('services')->name('services.')->group(function () {
        Route::get('/', [ServicesController::class, 'index'])->name('index');
        Route::post('/', [ServicesController::class, 'store'])->name('store');
        Route::post('/categories', [ServiceCategoryController::class, 'store'])->name('categories.store');
        Route::post('update_stock', [ServicesController::class, 'update_stock'])->name('update_stock');
        Route::put('{id}', [ServicesController::class, 'update'])->name('update');
        Route::delete('{id}', [ServicesController::class, 'destroy'])->name('destroy');

    });
});