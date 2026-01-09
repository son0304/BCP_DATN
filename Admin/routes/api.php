<?php

use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BannerApiController;
use App\Http\Controllers\Api\CourtApiController;
use App\Http\Controllers\Api\DistrictApiController;
use App\Http\Controllers\Api\ImageApiController;
use App\Http\Controllers\Api\MerchantApiController;
use App\Http\Controllers\Api\PromotionApiController;
use App\Http\Controllers\Api\ProvinceApiController;
use App\Http\Controllers\Api\ReviewApiController;
use App\Http\Controllers\Api\TicketApiController;
use App\Http\Controllers\Api\TimeSlotApiController;
use App\Http\Controllers\Api\VenueApiController;
use App\Http\Controllers\Api\PaymentApiController;
use App\Http\Controllers\Api\ServiceApiController;
use App\Http\Controllers\Api\TransactionApiController;
use App\Http\Controllers\Api\WalletApiController;
use App\Http\Controllers\Web\LocationController;
use App\Http\Controllers\Api\VenueTypeApiController;
use App\Http\Controllers\Api\ChatApiController;
use App\Http\Controllers\Api\CommentApiController;
use App\Http\Controllers\Api\PostApiController;
use App\Http\Controllers\Api\TagApiController;
use App\Models\District;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// ==================== PUBLIC ROUTES ====================
// Những route này không cần token
Route::post('/register', [AuthApiController::class, 'register']);
Route::post('/login', [AuthApiController::class, 'login']);
Route::post('/verify-email', [AuthApiController::class, 'verifyEmail']);
Route::get('/user', [AuthApiController::class, 'showuer']);
Route::get('/transaction', [TransactionApiController::class, 'index']);

Route::get('/banners', [BannerApiController::class, 'index']);
Route::get('/promoted-venues', [VenueApiController::class, 'promotedVenues']);

Route::get('/locations/districts/{province_id}', function ($province_id) {
    return response()->json(
        District::where('province_id', $province_id)->orderBy('name')->get()
    );
})->name('locations.districts');

Route::get('/venues', [VenueApiController::class, 'index']);
Route::post('/venues', [VenueApiController::class, 'store']);
Route::get('/venue/{id}', [VenueApiController::class, 'show']);

Route::get('/venueType', [VenueTypeApiController::class, 'getVenueType']);

Route::get('/court/{id}', [CourtApiController::class, 'show']);
Route::get('/services/{id}', [ServiceApiController::class, 'getServiceByVenue']);

Route::get('/time_slots', [TimeSlotApiController::class, 'index']);

Route::get('/provinces', [ProvinceApiController::class, 'index']);
Route::get('/province/{id}', [ProvinceApiController::class, 'show']);

Route::get('/districts', [DistrictApiController::class, 'index']);
Route::get('/district/{id}', [DistrictApiController::class, 'show']);
Route::get('/districts/{province}', [LocationController::class, 'getDistrictsByProvince']);
Route::get('/ticket/{id}', [TicketApiController::class, 'show']);
Route::get('/tickets', [TicketApiController::class, 'index']);

Route::post('/payment/momo', [PaymentApiController::class, 'paymentMomo']);
Route::post('/payment/momo/ipn', [PaymentApiController::class, 'ipn']);
Route::get('/user', [AuthApiController::class, 'index']);

Route::get('/payment/check-status/{id}', [PaymentApiController::class, 'checkTransactionStatus']);
// Route::get('/merchant', [MerchantApiController::class, 'index']);

Route::get('/posts', [PostApiController::class, 'index']);
Route::get('/tags', [TagApiController::class, 'index']);


Route::apiResource('reviews', ReviewApiController::class)
    ->only(['index', 'show']);

Route::get('/venue_types', [VenueTypeApiController::class, 'index']);

Route::get('/posts/{postId}/comments', [CommentApiController::class, 'index']);

// ==================== PROTECTED ROUTES ====================
// Những route này cần JWT token
Route::middleware(['jwt.auth'])->group(function () {

    // Logout
    Route::post('/logout', [AuthApiController::class, 'logout']);
    Route::get('/promotions', [PromotionApiController::class, 'index']);

    Route::prefix('chats')->group(function () {
        // Gửi tin nhắn mới (POST: /api/chats/send/{otherUserId})
        Route::post('/send/{otherUserId}', [ChatApiController::class, 'sendMessage']);
        // Lấy lịch sử tin nhắn (GET: /api/chats/{otherUserId}/messages)
        Route::get('/{otherUserId}/messages', [ChatApiController::class, 'getMessages']);
    });

    Route::get('/wallet', [WalletApiController::class, 'myWallet']);
    Route::get('/merchant', [MerchantApiController::class, 'index']);
    Route::post('/merchant/{id}', [MerchantApiController::class, 'updateMerchant']);


    // Tickets
    Route::get('/tickets', [TicketApiController::class, 'index']);
    // Route::get('/ticket/{id}', [TicketApiController::class, 'show']);
    Route::post('/tickets', [TicketApiController::class, 'store']);
    Route::delete('/item/{id}', [TicketApiController::class, 'destroyItem']);
    Route::delete('/ticket/{id}', [TicketApiController::class, 'destroyTicket']);
    // Venue (create)
    Route::post('/venues', [VenueApiController::class, 'store']);

    // Upload image
    Route::post('/upload', [ImageApiController::class, 'store']);
    //Reviews
    Route::post('/reviews', [ReviewApiController::class, 'store']);
    Route::post('/reviews/{id}', [ReviewApiController::class, 'update']);
    Route::delete('/reviews/{id}', [ReviewApiController::class, 'destroy']);

    Route::post('/comments', [CommentApiController::class, 'store']);
    Route::delete('/comments/{id}', [CommentApiController::class, 'destroy']);

    // Reviews (protected actions)
    Route::apiResource('reviews', ReviewApiController::class)
        ->only(['store', 'update', 'destroy']);
    // Payment

    Route::post('/payment/wallet', [PaymentApiController::class, 'paymentWallet']);

    Route::post('/posts', [PostApiController::class, 'store']);
});