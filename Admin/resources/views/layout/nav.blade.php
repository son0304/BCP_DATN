<div class="navbar-custom">
    <ul class="list-unstyled topnav-menu float-right mb-0">

        @auth
            <!-- 1. NOTIFICATIONS (NÚT CHUÔNG) -->
            <!-- Chú ý: Giữ nguyên các ID để JS bên file partial tìm thấy -->
            <li class="dropdown notification-list" id="notificationLi">
                <a class="nav-link waves-effect waves-light" href="javascript:void(0);" data-toggle="modal"
                    data-target="#notificationModal" role="button">

                    <i class="fe-bell noti-icon"></i>

                    <!-- Badge số lượng (JS sẽ update vào đây) -->
                    <span class="badge badge-danger rounded-circle noti-icon-badge" id="lblNotificationCount"
                        style="z-index: 1; position: relative;">0</span>

                    <!-- Hiệu ứng rung (CSS bên partial sẽ xử lý) -->
                    <span class="pulse-ring"></span>
                </a>
            </li>

            <!-- 2. USER PROFILE -->
            <li class="dropdown notification-list">
                <a class="nav-link dropdown-toggle nav-user mr-0 waves-effect waves-light" data-toggle="dropdown"
                    href="#" role="button" aria-haspopup="false" aria-expanded="false">
                    <img src="{{ Auth::user()->avt ?? asset('template/assets/images/users/avatar-1.jpg') }}"
                        alt="user-image" class="rounded-circle">
                    <span class="pro-user-name ml-1">
                        {{ Auth::user()->name }} <i class="mdi mdi-chevron-down"></i>
                    </span>
                </a>
                <div class="dropdown-menu dropdown-menu-right profile-dropdown">
                    <div class="dropdown-header noti-title">
                        <h6 class="text-overflow m-0">Welcome!</h6>
                    </div>
                    <a href="#" class="dropdown-item notify-item">
                        <i class="remixicon-account-circle-line"></i><span>My Account</span>
                    </a>
                    <a href="#" class="dropdown-item notify-item">
                        <i class="remixicon-settings-3-line"></i><span>Settings</span>
                    </a>
                    <a href="#" class="dropdown-item notify-item">
                        <i class="remixicon-wallet-line"></i>
                        <span>My Wallet <span class="badge badge-success float-right">3</span></span>
                    </a>
                    <div class="dropdown-divider"></div>
                    <a href="#" class="dropdown-item notify-item"
                        onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        <i class="remixicon-logout-box-line"></i><span>Logout</span>
                    </a>
                </div>
            </li>

            <!-- 3. SHORTCUT TO CLIENT -->
            <li class="dropdown notification-list" style="display: flex; align-items: center;">
                <a class="nav-link waves-effect waves-light" href="http://localhost:5173" target="_blank"
                    title="Chuyển sang giao diện khách hàng" style="padding: 0 12px;">
                    <i class="fe-monitor" style="font-size: 20px; color: #6C5CE7; transition: 0.2s;"></i>
                </a>
            </li>

            <!-- Logout Form -->
            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                @csrf
            </form>
        @endauth

        <!-- GUEST BUTTON -->
        @guest
            <li class="list-inline-item" style="display: flex; align-items: center; height: 70px;">
                <a class="btn" href="{{ route('login') }}"
                    style="background-color: #6C5CE7; color: #FFFFFF; padding: 8px 16px; border-radius: 6px;"
                    onmouseover="this.style.backgroundColor='#5845d4';" onmouseout="this.style.backgroundColor='#6C5CE7';">
                    <i class="mdi mdi-login me-2"></i> Đăng nhập
                </a>
            </li>
        @endguest
    </ul>

    <!-- LOGO BOX -->
    <div class="logo-box">
        <a href="{{ route('home.index') }}" class="logo text-center">
            <span class="logo-lg rounded-full ">
                <img src="{{ asset('template/assets/images/logo.png') }}" alt="Logo MyApp" width="64" height="64"
                    class="rounded-circle bg-white p-1 shadow-sm">
            </span>
            <span class="logo-sm">
                <img src="{{ asset('template/assets/images/logo.png') }}" alt="" height="32"
                    class="rounded-circle bg-white p-1 shadow-sm">
            </span>
        </a>
    </div>

    <!-- MOBILE MENU BUTTON -->
    <ul class="list-unstyled topnav-menu topnav-menu-left m-0">
        <li>
            <button class="button-menu-mobile waves-effect waves-light">
                <i class="fe-menu"></i>
            </button>
        </li>
    </ul>

</div>

<!-- ============================================================== -->
<!-- GỌI FILE NOTIFICATION VÀO ĐÂY -->
<!-- ============================================================== -->
@include('layout.notification')
