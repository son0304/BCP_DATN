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
            $notifications = collect([]);
            $unreadCount = 0;

            if (Auth::check()) {
                $rawNotis = Notification::where('user_id', Auth::id())
                    ->whereNull('read_at')
                    ->orderBy('created_at', 'desc')
                    ->get();

                $unreadCount = $rawNotis->count();

                $notifications = $rawNotis->map(function ($noti) {
                    // 1. Lấy dữ liệu JSON từ cột data
                    // Đảm bảo là mảng dù DB lưu chuỗi hay array
                    $data = is_array($noti->data) ? $noti->data : json_decode($noti->data, true);

                    // 2. Xác định màu sắc giao diện (Style)
                    $style = match($noti->type) {
                        'danger', 'error' => ['bg' => 'bg-soft-danger', 'icon' => 'fe-alert-circle', 'text' => 'text-danger'],
                        'warning' => ['bg' => 'bg-soft-warning', 'icon' => 'fe-alert-triangle', 'text' => 'text-warning'],
                        'success' => ['bg' => 'bg-soft-success', 'icon' => 'fe-check-circle', 'text' => 'text-success'],
                        default   => ['bg' => 'bg-soft-info',    'icon' => 'fe-bell',          'text' => 'text-primary'],
                    };

                    // 3. Xác định đường dẫn (Link)
                    // Ưu tiên link trong data, nếu không có thì về trang chủ hoặc #
                    $link = $data['link'] ?? '#';

                    return (object) [
                        'id' => $noti->id,
                        'title' => $noti->title ?? 'Thông báo hệ thống',
                        'message' => $noti->message,
                        'time' => Carbon::parse($noti->created_at)->diffForHumans(),
                        'link' => $link,
                        'style' => (object) $style, // Trả về object style để view dễ dùng
                        'type' => $noti->type
                    ];
                });
            }

            $view->with('notifications', $notifications);
            $view->with('unreadCount', $unreadCount);
        });

        Paginator::useBootstrapFive();

    }
}