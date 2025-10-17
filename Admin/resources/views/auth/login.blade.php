@extends('layout.auth-layout')

@section('title', 'Đăng nhập - BCP')

@section('content')
<div class="auth-header">
    <h3><i class="fas fa-sign-in-alt me-2"></i>Đăng nhập</h3>
    <p class="mb-0 mt-2">Đăng nhập để sử dụng dịch vụ</p>
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

    <form method="POST" action="{{ route('login') }}">
        @csrf
        
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

        <!-- Remember me -->
        <div class="mb-3 form-check">
            <input type="checkbox" 
                   class="form-check-input" 
                   id="remember" 
                   name="remember" 
                   {{ old('remember') ? 'checked' : '' }}>
            <label class="form-check-label" for="remember">
                Ghi nhớ đăng nhập
            </label>
        </div>

        <!-- Submit button -->
        <div class="d-grid mb-3">
            <button type="submit" class="btn btn-primary btn-auth">
                <i class="fas fa-sign-in-alt me-2"></i>Đăng nhập
            </button>
        </div>
    </form>

    <!-- Links -->
    <div class="auth-links">
        <p class="mb-2">Chưa có tài khoản?</p>
        <a href="{{ route('register') }}">
            <i class="fas fa-user-plus me-1"></i>Đăng ký ngay
        </a>
    </div>
</div>
@endsection
