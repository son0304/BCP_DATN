@extends('app')
@section('title', 'Tài khoản của tôi')

@section('content')
    @php
        $minReserve = 300000;
        $currentBalance = $wallet->balance ?? 0;
        $maxWithdrawable = max(0, $currentBalance - $minReserve);
        // Lấy ảnh đại diện từ quan hệ morphMany
        $avatarUrl = $user->images->first()
            ? asset($user->images->first()->url)
            : 'https://ui-avatars.com/api/?name=' . urlencode($user->name);
    @endphp

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --sidebar-width: 280px;
            --primary-color: #4f46e5;
            --bg-body: #f9fafb;
        }

        body {
            background-color: var(--bg-body);
            font-family: 'Inter', sans-serif;
            color: #1f2937;
        }

        .account-wrapper {
            display: flex;
            min-height: 100vh;
        }

        .account-sidebar {
            width: var(--sidebar-width);
            background: #ffffff;
            border-right: 1px solid #e5e7eb;
            padding: 2rem 1.5rem;
            flex-shrink: 0;
        }

        .account-main {
            flex-grow: 1;
            padding: 2rem 3rem;
            max-width: calc(100% - var(--sidebar-width));
        }

        .account-nav .nav-link {
            display: flex;
            align-items: center;
            padding: 0.75rem 1rem;
            color: #4b5563;
            font-weight: 500;
            border-radius: 10px;
            margin-bottom: 0.5rem;
            transition: 0.2s;
            text-decoration: none;
        }

        .account-nav .nav-link.active {
            background: #eef2ff;
            color: var(--primary-color);
        }

        .account-nav .nav-link i {
            width: 20px;
            margin-right: 12px;
        }

        .wallet-banner {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            border-radius: 20px;
            padding: 2.5rem;
            color: white;
            margin-bottom: 2rem;
        }

        .merchant-img-item {
            width: 100%;
            height: 120px;
            object-fit: cover;
            border-radius: 12px;
            border: 1px solid #e5e7eb;
        }

        /* Hiệu ứng chọn ảnh */
        .avt-wrapper {
            position: relative;
            width: 100px;
            height: 100px;
        }

        .avt-preview {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 50%;
            border: 4px solid white;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .avt-change-btn {
            position: absolute;
            bottom: 0;
            end: 0;
            background: var(--primary-color);
            color: white;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            border: 2px solid white;
        }
    </style>

    <div class="account-wrapper">
        <!-- SIDEBAR -->
        <aside class="account-sidebar text-nowrap">
            <div class="text-center mb-4">
                <img src="{{ $avatarUrl }}" class="rounded-circle border border-4 border-light mb-2" width="85"
                    height="85" style="object-fit: cover">
                <h6 class="fw-bold mb-0 text-dark">{{ $user->name }}</h6>
                <p class="text-muted small">ID: #USER-{{ $user->id }}</p>
            </div>
            <nav class="account-nav nav flex-column">
                <a class="nav-link active" data-bs-toggle="pill" href="#pills-finance"><i class="fas fa-wallet"></i> Tài
                    chính & Ví</a>
                <a class="nav-link" data-bs-toggle="pill" href="#pills-general"><i class="fas fa-user-circle"></i> Thông tin
                    cá nhân</a>
                <a class="nav-link" data-bs-toggle="pill" href="#pills-venues"><i class="fas fa-store"></i> Danh sách cơ
                    sở</a>
                <hr class="text-muted opacity-25">
                <form action="{{ route('logout') }}" method="POST"> @csrf <button
                        class="nav-link text-danger border-0 bg-transparent w-100"><i class="fas fa-sign-out-alt"></i> Đăng
                        xuất</button></form>
            </nav>
        </aside>

        <!-- MAIN -->
        <main class="account-main">
            {{-- Alert thông báo --}}
            @if (session('success'))
                <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4">{{ session('success') }}</div>
            @endif
            @if ($errors->any())
                <div class="alert alert-danger border-0 shadow-sm rounded-4 mb-4">
                    @foreach ($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </div>
            @endif

            <div class="tab-content">
                {{-- TAB TÀI CHÍNH (Giữ nguyên logic của bạn) --}}
                <div class="tab-pane fade show active" id="pills-finance">
                    {{-- Nội dung Ví đã viết ở các bước trước... --}}
                </div>

                {{-- TAB THÔNG TIN CÁ NHÂN (CẬP NHẬT) --}}
                <div class="tab-pane fade" id="pills-general">
                    <h4 class="fw-bold mb-4">Cài đặt tài khoản</h4>
                    <div class="card border-0 shadow-sm rounded-4 p-4 mb-5">
                        <form action="{{ route('user.update') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="row g-4">
                                <!-- Ảnh đại diện -->
                                <div class="col-12 d-flex align-items-center gap-4 mb-3">
                                    <div class="avt-wrapper">
                                        <img id="avtPreview" src="{{ $avatarUrl }}" class="avt-preview">
                                        <label for="avtInput" class="avt-change-btn"><i class="fas fa-camera"></i></label>
                                        <input type="file" name="avt" id="avtInput" class="d-none" accept="image/*"
                                            onchange="previewImage(this)">
                                    </div>
                                    <div>
                                        <h6 class="fw-bold mb-1">Ảnh đại diện</h6>
                                        <p class="text-muted small mb-0">Hỗ trợ JPG, PNG, WebP. Tối đa 2MB.</p>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted">Họ và tên</label>
                                    <input type="text" name="name" class="form-control" value="{{ $user->name }}"
                                        required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted">Email (Bất biến)</label>
                                    <input type="email" class="form-control bg-light" value="{{ $user->email }}"
                                        readonly>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted">Số điện thoại</label>
                                    <input type="text" name="phone" class="form-control" value="{{ $user->phone }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted">Khu vực</label>
                                    <input type="text" class="form-control bg-light"
                                        value="{{ $user->district->name ?? '' }}, {{ $user->province->name ?? '' }}"
                                        readonly>
                                </div>
                                <div class="col-12 text-end">
                                    <button type="submit" class="btn btn-primary px-5 fw-bold rounded-3">Lưu thay
                                        đổi</button>
                                </div>
                            </div>
                        </form>
                    </div>

                    {{-- Thông tin Ngân hàng & Ảnh Merchant --}}
                    @if ($user->merchantProfile)
                        <h5 class="fw-bold mb-3">Thông tin định danh & Ngân hàng</h5>
                        <div class="card border-0 shadow-sm rounded-4 p-4 bg-dark text-white mb-4">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <div class="small opacity-50 mb-1 text-uppercase ls-1">Ngân hàng thụ hưởng</div>
                                    <h5 class="fw-bold text-primary mb-3">{{ $user->merchantProfile->bank_name }}</h5>
                                    <div class="h5 fw-bold mb-1 ls-2">{{ $user->merchantProfile->bank_account_number }}
                                    </div>
                                    <div class="small opacity-75 text-uppercase">
                                        {{ $user->merchantProfile->bank_account_name }}</div>
                                </div>
                                <i class="fas fa-university fa-3x opacity-25"></i>
                            </div>
                        </div>

                        <h6 class="fw-bold text-dark mb-3">Hình ảnh Merchant / Hồ sơ pháp lý</h6>
                        <div class="row g-3">
                            @foreach ($user->merchantProfile->images as $img)
                                <div class="col-6 col-md-3">
                                    <a href="{{ asset($img->url) }}" target="_blank">
                                        <img src="{{ asset($img->url) }}" class="merchant-img-item shadow-sm">
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- TAB DANH SÁCH CƠ SỞ --}}
                <div class="tab-pane fade" id="pills-venues">
                    {{-- Giữ nguyên code danh sách venues của bạn --}}
                </div>
            </div>
        </main>
    </div>

    <script>
        function previewImage(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('avtPreview').src = e.target.result;
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
@endsection
