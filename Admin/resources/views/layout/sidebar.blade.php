<nav class="sidebar sidebar-offcanvas" id="sidebar">
    <ul class="nav">
        @if (auth()->check())
            {{-- Profile --}}
            <li class="nav-item nav-profile">
                <a href="#" class="nav-link">
                    <div class="nav-profile-image">
                        <img src="{{ asset('dist/assets/images/faces/face1.jpg') }}" alt="profile" />
                        <span class="login-status online"></span>
                    </div>
                    <div class="nav-profile-text d-flex flex-column">
                        <span class="font-weight-bold mb-2">{{ auth()->user()->name ?? 'User' }}</span>
                        <span class="text-secondary text-small">{{ auth()->user()->role->name ?? 'User' }}</span>
                    </div>
                    <i class="mdi mdi-bookmark-check text-success nav-profile-badge"></i>
                </a>
            </li>

            {{-- Menu chung --}}
            <li class="nav-item">
                @if(auth()->user()->role->name === 'admin')
                    <a class="nav-link" href="{{ route('admin.dashboard') }}">
                        <span class="menu-title">Dashboard</span>
                        <i class="mdi mdi-view-dashboard menu-icon"></i>
                    </a>
                @elseif(auth()->user()->role->name === 'venue_owner')
                    <a class="nav-link" href="{{ route('owner.dashboard') }}">
                        <span class="menu-title">Dashboard</span>
                        <i class="mdi mdi-view-dashboard menu-icon"></i>
                    </a>
                @endif
            </li>

            {{-- Admin menu --}}
            @if (auth()->user()->role->name === 'admin')
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('admin.users.index') }}">
                        <span class="menu-title">Người dùng</span>
                        <i class="mdi mdi-account menu-icon"></i>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('admin.venues.index') }}">
                        <span class="menu-title">Quản lý Thương hiệu</span>
                        <i class="mdi mdi-store menu-icon"></i>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('admin.reviews.index') }}">
                        <span class="menu-title">Đánh giá</span>
                        <i class="mdi mdi-star menu-icon"></i>
                    </a>
                </li>
            @endif

            {{-- Venue Owner menu --}}
            @if (auth()->user()->role->name === 'venue_owner')
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('owner.venues.index') }}">
                        <span class="menu-title">Thương Hiệu</span>
                        <i class="mdi mdi-store menu-icon"></i>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="{{ route('owner.reviews.index') }}">
                        <span class="menu-title">Đánh giá</span>
                        <i class="mdi mdi-star menu-icon"></i>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="{{ route('owner.bookings.index') }}">
                        <span class="menu-title">Đơn đặt sân</span>
                        <i class="mdi mdi-calendar-check menu-icon"></i>
                    </a>
                </li>
            @endif
        @endif

        {{-- Documentation --}}
        <li class="nav-item">
            <a class="nav-link" href="docs/documentation.html" target="_blank">
                <span class="menu-title">Tài liệu hướng dẫn</span>
                <i class="mdi mdi-file-document-box menu-icon"></i>
            </a>
        </li>
    </ul>
</nav>
