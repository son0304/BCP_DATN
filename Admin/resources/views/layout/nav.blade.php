<div class="navbar-custom">
    <ul class="list-unstyled topnav-menu float-right mb-0">
        <!-- Search (luôn hiện nếu muốn) -->
        <li class="d-none d-sm-block">
            <form class="app-search">
                <div class="app-search-box">
                    <div class="input-group">
                        <input type="text" class="form-control" placeholder="Search...">
                        <div class="input-group-append">
                            <button class="btn" type="submit">
                                <i class="fe-search"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </li>

        @auth
            <!-- Notifications -->
            <li class="dropdown notification-list">
                <a class="nav-link dropdown-toggle waves-effect waves-light" data-toggle="dropdown" href="#"
                    role="button" aria-haspopup="false" aria-expanded="false">
                    <i class="fe-bell noti-icon"></i>
                    <span class="badge badge-danger rounded-circle noti-icon-badge">4</span>
                </a>
                <div class="dropdown-menu dropdown-menu-right dropdown-lg">
                    <!-- notification content -->
                </div>
            </li>

            <!-- User Profile -->
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
                    <a href="javascript:void(0);" class="dropdown-item notify-item">
                        <i class="remixicon-account-circle-line"></i>
                        <span>My Account</span>
                    </a>
                    <a href="javascript:void(0);" class="dropdown-item notify-item">
                        <i class="remixicon-settings-3-line"></i>
                        <span>Settings</span>
                    </a>
                    <a href="javascript:void(0);" class="dropdown-item notify-item">
                        <i class="remixicon-wallet-line"></i>
                        <span>My Wallet <span class="badge badge-success float-right">3</span></span>
                    </a>
                    <a href="javascript:void(0);" class="dropdown-item notify-item">
                        <i class="remixicon-lock-line"></i>
                        <span>Lock Screen</span>
                    </a>
                    <div class="dropdown-divider"></div>
                    <a href="javascript:void(0);" class="dropdown-item notify-item"
                        onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        <i class="remixicon-logout-box-line"></i>
                        <span>Logout</span>
                    </a>
                </div>
            </li>

            <!-- Logout Form -->
            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                @csrf
            </form>
        @endauth

        @guest
            <!-- Nút đăng nhập -->
            <li class="nav-item">
                <a class="btn" href="{{ route('login') }}"
                    style="background-color: #10B981; color: #FFFFFF; transition: all 0.2s;"
                    onmouseover="this.style.backgroundColor='#0F9A72';" onmouseout="this.style.backgroundColor='#10B981';">
                    <i class="mdi mdi-login me-2"></i> Đăng nhập
                </a>
            </li>
        @endguest
    </ul>

    <!-- Logo -->
    <div class="logo-box">
        <a href="{{ route('home.index') }}" class="logo text-center">
            <span class="logo-lg">
                <img src="{{ asset('template/assets/images/logo.png') }}" alt="" height="64">
            </span>
            <span class="logo-sm">
                <img src="{{ asset('template/assets/images/logo.png') }}" alt="" height="24">
            </span>
        </a>
    </div>

    <ul class="list-unstyled topnav-menu topnav-menu-left m-0">
        <li>
            <button class="button-menu-mobile waves-effect waves-light">
                <i class="fe-menu"></i>
            </button>
        </li>
    </ul>
</div>
