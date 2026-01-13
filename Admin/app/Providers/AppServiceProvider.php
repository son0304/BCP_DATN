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