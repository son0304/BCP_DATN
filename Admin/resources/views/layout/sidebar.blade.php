<nav class="sidebar sidebar-offcanvas" id="sidebar">
    <ul class="nav">
      <li class="nav-item nav-profile">
        <a href="#" class="nav-link">
          <div class="nav-profile-image">
              <img src="{{ asset('dist/assets/images/faces/face1.jpg') }}" alt="profile" />
              <span class="login-status online"></span>
            <!--change to offline or busy as needed-->
          </div>
          <div class="nav-profile-text d-flex flex-column">
            <span class="font-weight-bold mb-2">David Grey. H</span>
            <span class="text-secondary text-small">Project Manager</span>
          </div>
          <i class="mdi mdi-bookmark-check text-success nav-profile-badge"></i>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="{{route('home.index')}}">
          <span class="menu-title">Thống kê</span>
          <i class="mdi mdi-home menu-icon"></i>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="{{route('brand.index')}}">
          <span class="menu-title">Brand</span>
          <i class="mdi mdi-tag-outline menu-icon"></i>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="{{route('courts.index')}}">
          <span class="menu-title">Courts</span>
          <i class="mdi mdi-tennis menu-icon"></i>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="{{route('users.index')}}">
          <span class="menu-title">Users</span>
          <i class="mdi mdi-account menu-icon"></i>

        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="{{route('reivews.index')}}">
          <span class="menu-title">Reviews</span>
          <i class="mdi mdi-star menu-icon"></i>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="docs/documentation.html" target="_blank">
          <span class="menu-title">Documentation</span>
          <i class="mdi mdi-file-document-box menu-icon"></i>
        </a>
      </li>
    </ul>
  </nav>