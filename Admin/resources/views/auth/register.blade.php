@extends('layout.auth-layout')

@section('title', 'Đăng ký tài khoản - BCP')

@section('content')
<div class="auth-header">
    <h3><i class="fas fa-user-plus me-2"></i>Đăng ký tài khoản</h3>
    <p class="mb-0 mt-2">Tạo tài khoản mới để sử dụng dịch vụ</p>
</div>

<div class="auth-body">
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <!-- Họ tên -->
        <div class="mb-3">
            <label for="name" class="form-label">
                <i class="fas fa-user me-1"></i>Họ và tên
            </label>
            <div class="input-group">
                <span class="input-group-text">
                    <i class="fas fa-user"></i>
                </span>
                <input type="text"
                       class="form-control @error('name') is-invalid @enderror"
                       id="name"
                       name="name"
                       value="{{ old('name') }}"
                       placeholder="Nhập họ và tên"
                       required>
            </div>
            @error('name')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <!-- Email -->
        <div class="mb-3">
            <label for="email" class="form-label">
                <i class="fas fa-envelope me-1"></i>Email
            </label>
            <div class="input-group">
                <span class="input-group-text">
                    <i class="fas fa-envelope"></i>
                </span>
                <input type="email"
                       class="form-control @error('email') is-invalid @enderror"
                       id="email"
                       name="email"
                       value="{{ old('email') }}"
                       placeholder="Nhập địa chỉ email"
                       required>
            </div>
            @error('email')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <!-- Số điện thoại -->
        <div class="mb-3">
            <label for="phone" class="form-label">
                <i class="fas fa-phone me-1"></i>Số điện thoại
            </label>
            <div class="input-group">
                <span class="input-group-text">
                    <i class="fas fa-phone"></i>
                </span>
                <input type="tel"
                       class="form-control @error('phone') is-invalid @enderror"
                       id="phone"
                       name="phone"
                       value="{{ old('phone') }}"
                       placeholder="Nhập số điện thoại"
                       required>
            </div>
            @error('phone')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <!-- Mật khẩu -->
        <div class="mb-3">
            <label for="password" class="form-label">
                <i class="fas fa-lock me-1"></i>Mật khẩu
            </label>
            <div class="input-group">
                <span class="input-group-text">
                    <i class="fas fa-lock"></i>
                </span>
                <input type="password"
                       class="form-control @error('password') is-invalid @enderror"
                       id="password"
                       name="password"
                       placeholder="Nhập mật khẩu"
                       required>
            </div>
            @error('password')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <!-- Xác nhận mật khẩu -->
        <div class="mb-4">
            <label for="password_confirmation" class="form-label">
                <i class="fas fa-lock me-1"></i>Xác nhận mật khẩu
            </label>
            <div class="input-group">
                <span class="input-group-text">
                    <i class="fas fa-lock"></i>
                </span>
                <input type="password"
                       class="form-control"
                       id="password_confirmation"
                       name="password_confirmation"
                       placeholder="Nhập lại mật khẩu"
                       required>
            </div>
        </div>

        <!-- Submit button -->
        <div class="d-grid">
            <button type="submit" class="btn btn-primary btn-auth">
                <i class="fas fa-user-plus me-2"></i>Đăng ký ngay
            </button>
        </div>
    </form>

    <!-- Links -->
    <div class="auth-links">
        <p class="mb-2">Đã có tài khoản?</p>
        <a href="{{ route('login') }}">
            <i class="fas fa-sign-in-alt me-1"></i>Đăng nhập ngay
        </a>
    </div>
</div>
@endsection
