<!DOCTYPE html>
<html lang="en">

@include('layout.head')

<body>
    <div id="wrapper">
        @include('layout.nav')
        @include('layout.sidebar')
        <div class="content-page">
            <div class="py-4">
                <div class="container-fluid">
                    @yield('content')
                </div>
            </div>
            @include('layout.footer')
        </div>
    </div>
    @include('layout.right-bar')
    <div class="rightbar-overlay"></div>

    <!-- 1. Thư viện Pusher (Giữ nguyên) -->
    <script src="https://js.pusher.com/8.0/pusher.min.js"></script>

    <!-- 2. THÊM MỚI: Thư viện Laravel Echo (Bắt buộc phải có để dùng window.Echo) -->
    <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.16.1/dist/echo.iife.js"></script>

    <!-- 3. THÊM MỚI: Cấu hình kết nối Reverb (Không cần Vite) -->
    <script>
        window.Pusher = Pusher;

        window.Echo = new Echo({
            broadcaster: 'reverb',
            key: "{{ env('REVERB_APP_KEY') }}", // Lấy Key từ file .env của bạn
            wsHost: window.location.hostname, // Tự động lấy IP hoặc localhost hiện tại
            wsPort: 8080, // Cổng mặc định của Reverb
            wssPort: 443,
            forceTLS: false, // Chạy http (localhost) thì để false
            enabledTransports: ['ws', 'wss'],
            disableStats: true,
        });

        console.log("Reverb connection initialized!");
    </script>

    <!-- File script riêng của template (Giữ nguyên) -->
    @include('layout.scrip')

</body>

</html>
