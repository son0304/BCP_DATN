<nav class="sidebar sidebar-offcanvas" id="sidebar">
  <ul class="nav">
    {{-- Thông tin người quản trị --}}
    <li class="nav-item nav-profile">
      <a href="#" class="nav-link">
        <div class="nav-profile-image">
          <img src="{{ asset('dist/assets/images/faces/face1.jpg') }}" alt="profile" />
          <span class="login-status online"></span>
        </div>
        <div class="nav-profile-text d-flex flex-column">
          <span class="font-weight-bold mb-2">David Grey. H</span>
          <span class="text-secondary text-small">Project Manager</span>
        </div>
        <i class="mdi mdi-bookmark-check text-success nav-profile-badge"></i>
      </a>
    </li>

    {{-- Trang tổng quan --}}
    <li class="nav-item">
      <a class="nav-link" href="{{ route('admin.home.index') }}">
        <span class="menu-title">Thống kê</span>
        <i class="mdi mdi-home menu-icon"></i>
      </a>
    </li>

    {{-- Quản lý thương hiệu sân --}}
    <li class="nav-item">
      <a class="nav-link" href="{{ route('admin.brand.index') }}">
        <span class="menu-title">Thương hiệu sân</span>
        <i class="mdi mdi-store menu-icon"></i>
      </a>
    </li>

    {{-- Quản lý sân --}}
    <li class="nav-item">
      <a class="nav-link" href="{{ route('admin.courts.index') }}">
        <span class="menu-title">Danh sách sân</span>
        <i class="mdi mdi-tennis menu-icon"></i>
      </a>
    </li>

    {{-- Quản lý người dùng --}}
    <li class="nav-item">
      <a class="nav-link" href="{{ route('admin.users.index') }}">
        <span class="menu-title">Người dùng</span>
        <i class="mdi mdi-account menu-icon"></i>
      </a>
    </li>

    {{-- Quản lý đánh giá --}}
    <li class="nav-item">
      <a class="nav-link" href="{{ route('admin.reviews.index') }}">
        <span class="menu-title">Đánh giá</span>
        <i class="mdi mdi-star menu-icon"></i>
      </a>
    </li>

    {{-- Quản lý đơn đặt --}}
    <li class="nav-item">
      <a class="nav-link" href="{{ route('admin.bookings.index') }}">
        <span class="menu-title">Đơn đặt sân</span>
        <i class="mdi mdi-calendar-check menu-icon"></i>
      </a>
    </li>

    {{-- Tài liệu --}}
    <li class="nav-item">
      <a class="nav-link" href="docs/documentation.html" target="_blank">
        <span class="menu-title">Tài liệu hướng dẫn</span>
        <i class="mdi mdi-file-document-box menu-icon"></i>
      </a>
    </li>
  </ul>
</nav>
