<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use App\Models\Notification;
use Carbon\Carbon;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {

        View::composer(['layout.nav', 'layout.notification'], function ($view) {
            if (Auth::check()) {
                $userId = Auth::id();

                // Lấy 10 thông báo chưa đọc mới nhất
                $unreadNotifications = Notification::where('user_id', $userId)
                    ->whereNull('read_at')
                    ->orderBy('created_at', 'desc')
                    ->take(10)
                    ->get();

                // Đếm tổng số thông báo chưa đọc
                $unreadCount = Notification::where('user_id', $userId)
                    ->whereNull('read_at')
                    ->count();

                $view->with([
                    'notifications' => $unreadNotifications,
                    'unreadCount' => $unreadCount
                ]);
            } else {
                // Nếu chưa đăng nhập, trả về giá trị mặc định để tránh lỗi biến undefined
                $view->with([
                    'notifications' => collect(),
                    'unreadCount' => 0
                ]);
            }
        });
        // View Composer cho file layout thông báo
        View::composer('layout.notification', function ($view) {
            if (Auth::check()) {
                $unreadNotifications = Notification::where('user_id', Auth::id())
                    ->whereNull('read_at')
                    ->orderBy('created_at', 'desc')
                    ->take(10) // Giới hạn 10 cái mới nhất
                    ->get();

                $unreadCount = Notification::where('user_id', Auth::id())
                    ->whereNull('read_at')
                    ->count();

                $view->with([
                    'notifications' => $unreadNotifications,
                    'unreadCount' => $unreadCount
                ]);
            }
        });

        Paginator::useBootstrapFive();
    }
}