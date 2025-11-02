<nav class="navbar default-layout-navbar col-lg-12 col-12 p-0 fixed-top d-flex flex-row shadow-sm"
    style="background-color: #F9FAFB;">
    <div class="text-center navbar-brand-wrapper d-flex align-items-center justify-content-start px-3">
        <a class="navbar-brand brand-logo d-flex align-items-center" href="{{ route('home.index') }}">
            <img src="{{ asset('dist/assets/images/logo.png') }}" alt="logo" style="height: 50px; width: auto;" />
        </a>
    </div>

    @auth
        <div class="navbar-menu-wrapper d-flex align-items-stretch justify-content-between flex-grow-1">
            <!-- Search -->
            <div class="search-field d-none d-md-flex align-items-center">
                <form class="d-flex align-items-center h-100" action="#">
                    <div class="input-group">
                        <div class="input-group-prepend bg-transparent">
                            <i class="input-group-text border-0 mdi mdi-magnify" style="color: #10B981;"></i>
                            <!-- icon search đổi màu Primary -->
                        </div>
                        <input type="text" class="form-control bg-transparent border-0" placeholder="Tìm kiếm..."
                            style="color: #11182C;">
                    </div>
                </form>
            </div>

            <!-- Profile -->
            <ul class="navbar-nav navbar-nav-right">
                <li class="nav-item nav-profile dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center" id="profileDropdown" href="#"
                        data-bs-toggle="dropdown" aria-expanded="false" style="transition: all 0.2s;">
                        <div class="nav-profile-img position-relative">
                            <img src="{{ Auth::user()->avt ?? asset('dist/assets/images/faces/face1.jpg') }}" alt="image"
                                class="rounded-circle" style="border: 2px solid #10B981;">
                            <span class="availability-status online" style="background-color: #10B981;"></span>
                        </div>
                        <div class="nav-profile-text ms-2">
                            <p class="mb-1 fw-semibold" style="color: #11182C;">{{ Auth::user()->name }}</p>
                        </div>
                    </a>
                    <div class="dropdown-menu navbar-dropdown" aria-labelledby="profileDropdown"
                        style="border: 1px solid #E5E7EB; background-color: #FFFFFF;">
                        <div class="dropdown-divider" style="border-color: #E5E7EB;"></div>
                        <a class="dropdown-item d-flex align-items-center" href="{{ route('logout') }}"
                            onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                            style="transition: all 0.2s;">
                            <i class="mdi mdi-logout me-2 text-danger"></i> Đăng xuất
                        </a>
                    </div>
                </li>
            </ul>
        </div>
    @endauth

    @guest
        <div class="navbar-menu-wrapper d-flex align-items-center justify-content-end flex-grow-1 pe-3">
            <ul class="navbar-nav navbar-nav-right">
                <li class="nav-item">
                    <a class="btn" href="{{ route('login') }}"
                        style="background-color: #10B981; color: #FFFFFF; transition: all 0.2s;"
                        onmouseover="this.style.backgroundColor='#0F9A72';"
                        onmouseout="this.style.backgroundColor='#10B981';">
                        <i class="mdi mdi-login me-2"></i> Đăng nhập
                    </a>
                </li>
            </ul>
        </div>
    @endguest
</nav>

@auth
    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">@csrf</form>
@endauth
