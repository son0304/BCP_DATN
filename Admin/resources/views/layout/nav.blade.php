<nav class="navbar default-layout-navbar col-lg-12 col-12 p-0 fixed-top d-flex flex-row shadow-sm bg-white">
    <div class="text-center navbar-brand-wrapper d-flex align-items-center justify-content-start px-3">
        <a class="navbar-brand brand-logo d-flex align-items-center" href="{{ route('home.index') }}">
            <img src="{{ asset('dist/assets/images/logo.png') }}" alt="logo" style="height: 50px; width: auto;" />
        </a>
    </div>

    @auth
    <div class="navbar-menu-wrapper d-flex align-items-stretch justify-content-between flex-grow-1">
     

        <div class="search-field d-none d-md-flex align-items-center">
            <form class="d-flex align-items-center h-100" action="#">
                <div class="input-group">
                    <div class="input-group-prepend bg-transparent">
                        <i class="input-group-text border-0 mdi mdi-magnify"></i>
                    </div>
                    <input type="text" class="form-control bg-transparent border-0" placeholder="Tìm kiếm...">
                </div>
            </form>
        </div>

        <ul class="navbar-nav navbar-nav-right">
            <li class="nav-item nav-profile dropdown">
                <a class="nav-link dropdown-toggle" id="profileDropdown" href="#" data-bs-toggle="dropdown" aria-expanded="false">
                    <div class="nav-profile-img">
                        <img src="{{ Auth::user()->avt ?? asset('dist/assets/images/faces/face1.jpg') }}" alt="image">
                        <span class="availability-status online"></span>
                    </div>
                    <div class="nav-profile-text">
                        <p class="mb-1 text-black fw-semibold">{{ Auth::user()->name }}</p>
                    </div>
                </a>
                <div class="dropdown-menu navbar-dropdown" aria-labelledby="profileDropdown">
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="{{ route('logout') }}"
                        onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        <i class="mdi mdi-logout me-2 text-primary"></i> Đăng xuất
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
                <a class="btn btn-gradient-primary" href="{{ route('login') }}">
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
