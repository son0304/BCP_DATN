<div class="navbar-custom">
    <ul class="list-unstyled topnav-menu float-right mb-0">

        @auth
            <!-- 1. THÔNG BÁO (NOTIFICATIONS) -->
            <li class="dropdown notification-list" id="notificationLi">
                <a class="nav-link waves-effect waves-light" href="javascript:void(0);" data-toggle="modal"
                    data-target="#notificationModal" role="button">
                    <i class="fe-bell noti-icon"></i>

                    <!-- Hiển thị Badge nếu có thông báo chưa đọc -->
                    @if (isset($unreadCount) && $unreadCount > 0)
                        <span class="badge badge-danger rounded-circle noti-icon-badge" id="lblNotificationCount">
                            {{ $unreadCount > 99 ? '99+' : $unreadCount }}
                        </span>
                        <!-- Hiệu ứng rung nháy khi có thông báo mới -->
                        <span class="pulse-ring"></span>
                    @endif
                </a>
            </li>

            <!-- 2. THÔNG TIN NGƯỜI DÙNG (USER PROFILE) -->
            <li class="dropdown notification-list">
                <a class="nav-link dropdown-toggle nav-user mr-0 waves-effect waves-light" data-toggle="dropdown"
                    href="#" role="button" aria-haspopup="false" aria-expanded="false">

                    @php
                        $userAvatar =
                            Auth::user()->images->first()->url ??
                            'https://ui-avatars.com/api/?name=' .
                                urlencode(Auth::user()->name) .
                                '&background=6C5CE7&color=fff';
                    @endphp

                    <img src="{{ $userAvatar }}" alt="user-image" class="rounded-circle" />

                    <span class="pro-user-name ml-1">
                        {{ Auth::user()->name }} <i class="mdi mdi-chevron-down"></i>
                    </span>
                </a>

                <div class="dropdown-menu dropdown-menu-right profile-dropdown">
                    <div class="dropdown-header noti-title">
                        <h6 class="text-overflow m-0">Xin chào !</h6>
                    </div>

                    <!-- Điều hướng theo Role -->
                    @if (Auth::user()->role->name === 'admin')
                        <a href="{{ route('admin.user.index') }}" class="dropdown-item notify-item">
                            <i class="fe-user"></i><span>Hồ Sơ Của Tôi</span>
                        </a>
                    @elseif (Auth::user()->role->name === 'venue_owner')
                        <a href="{{ route('owner.user.index') }}" class="dropdown-item notify-item">
                            <i class="fe-user"></i><span>Hồ Sơ Của Tôi</span>
                        </a>
                    @endif

                    <div class="dropdown-divider"></div>

                    <!-- Đăng xuất -->
                    <a href="javascript:void(0);" class="dropdown-item notify-item text-danger"
                        onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        <i class="remixicon-logout-box-line"></i><span>Đăng Xuất</span>
                    </a>
                </div>
            </li>

            <!-- 3. PHÍM TẮT SANG GIAO DIỆN KHÁCH (CLIENT SIDE) -->
            <li class="notification-list d-none d-md-inline-block">
                <a class="nav-link waves-effect waves-light" href="http://localhost:5173" target="_blank"
                    title="Chuyển sang giao diện khách hàng">
                    <i class="fe-monitor" style="font-size: 20px; color: #6C5CE7;"></i>
                </a>
            </li>

            <!-- Logout Form -->
            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                @csrf
            </form>
        @endauth

        <!-- GIAO DIỆN CHO KHÁCH (GUEST) -->
        @guest
            <li class="list-inline-item px-2" style="line-height: 70px;">
                <a class="btn btn-primary btn-sm rounded-pill" href="{{ route('login') }}"
                    style="background-color: #6C5CE7; border-color: #6C5CE7;">
                    <i class="mdi mdi-login mr-1"></i> Đăng nhập
                </a>
            </li>
        @endguest
    </ul>

    <!-- LOGO BOX -->
    <div class="logo-box">
        <a href="{{ route('home.index') }}" class="logo text-center">
            <span class="logo-lg">
                <img src="{{ asset('template/assets/images/logo.png') }}" alt="Logo" height="50">
            </span>
            <span class="logo-sm">
                <img src="{{ asset('template/assets/images/logo.png') }}" alt="Logo" height="28">
            </span>
        </a>
    </div>

    <!-- NÚT MỞ MENU TRÊN MOBILE -->
    <ul class="list-unstyled topnav-menu topnav-menu-left m-0">
        <li>
            <button class="button-menu-mobile waves-effect waves-light">
                <i class="fe-menu"></i>
            </button>
        </li>
    </ul>
</div>

<!-- ĐỂ MODAL/NOTI CONTENT Ở CUỐI CÙNG -->
@auth
    @include('layout.notification')
@endauth
