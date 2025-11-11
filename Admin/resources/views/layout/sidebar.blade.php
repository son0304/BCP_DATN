<nav class="sidebar sidebar-offcanvas" id="sidebar">
    <ul class="nav">

        {{-- Debug tạm thời --}}
        @php
        $user = auth()->user();
        @endphp
        @if($user)
        <li class="nav-item">
            <p class="text-white px-3">Logged in: {{ $user->name }} (Role: {{ $user->role->name }})</p>
        </li>
        @else
        <li class="nav-item">
            <p class="text-white px-3">Not logged in</p>
        </li>
        @endif

        {{-- Thông tin người quản trị --}}
        @if(auth()->check() && $user)
        <li class="nav-item nav-profile">
            <a href="#" class="nav-link">
                <div class="nav-profile-image">
                    <img src="{{ asset('dist/assets/images/faces/face1.jpg') }}" alt="profile" />
                    <span class="login-status online"></span>
                </div>
                <div class="nav-profile-text d-flex flex-column">
                    <span class="font-weight-bold mb-2">{{ $user->name ?? 'User' }}</span>
                    <span class="text-secondary text-small">{{ $user->role->name ?? 'User' }}</span>
                </div>
                <i class="mdi mdi-bookmark-check text-success nav-profile-badge"></i>
            </a>
        </li>

        {{-- Menu chung --}}
        <li class="nav-item">
            <a class="nav-link" href="{{ route('admin.home.index') }}">
                <span class="menu-title">Thống kê</span>
                <i class="mdi mdi-home menu-icon"></i>
            </a>
        </li>

        {{-- ====== ADMIN ONLY MENU ====== --}}
        @if(strtolower($user->role->name) === 'admin')
        <li class="nav-item">
            <a class="nav-link" href="{{ route('admin.users.index') }}">
                <span class="menu-title">Người dùng</span>
                <i class="mdi mdi-account menu-icon"></i>
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link" href="{{ route('admin.brand.index') }}">
                <span class="menu-title">Quản lý thương hiệu</span>
                <i class="mdi mdi-store menu-icon"></i>
            </a>
        </li>
        @endif

        {{-- ====== VENUE OWNER ONLY MENU ====== --}}
        @if(strtolower($user->role->name) === 'venue_owner')
        <li class="nav-item">
            <a class="nav-link" href="{{ route('admin.courts.index') }}">
                <span class="menu-title">Quản lý sân</span>
                <i class="mdi mdi-tennis menu-icon"></i>
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link" href="{{ route('admin.reviews.index') }}">
                <span class="menu-title">Đánh giá</span>
                <i class="mdi mdi-star menu-icon"></i>
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link" href="{{ route('admin.bookings.index') }}">
                <span class="menu-title">Đơn đặt sân</span>
                <i class="mdi mdi-calendar-check menu-icon"></i>
            </a>
        </li>
        @endif
        @endif

        {{-- Tài liệu --}}
        <li class="nav-item">
            <a class="nav-link" href="docs/documentation.html" target="_blank">
                <span class="menu-title">Tài liệu hướng dẫn</span>
                <i class="mdi mdi-file-document-box menu-icon"></i>
            </a>
        </li>
    </ul>
</nav>