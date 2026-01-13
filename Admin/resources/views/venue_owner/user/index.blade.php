@extends('app')
@section('title', 'Tài khoản của tôi')

@section('content')
    @php
        $minReserve = 300000;
        $currentBalance = $wallet->balance ?? 0;
        $maxWithdrawable = max(0, $currentBalance - $minReserve);

        // Lấy ảnh đại diện từ morphMany (bảng images)
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

        /* Icons */
        .tr-type-icon {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.9rem;
        }

        .tr-plus {
            background: #e8fff3;
            color: #1cc88a;
        }

        .tr-minus {
            background: #fff0f0;
            color: #e74a3b;
        }

        .tr-refund {
            background: #e1f5fe;
            color: #03a9f4;
        }

        .tr-withdraw {
            background: #fff9e6;
            color: #f6c23e;
        }

        /* Navigation */
        .account-nav .nav-link {
            display: flex;
            align-items: center;
            padding: 0.75rem 1rem;
            color: #4b5563;
            font-weight: 500;
            border-radius: 10px;
            margin-bottom: 0.5rem;
            transition: 0.2s;
            border: none;
            background: transparent;
            width: 100%;
            text-align: left;
        }

        .account-nav .nav-link.active {
            background: #eef2ff;
            color: var(--primary-color);
        }

        .account-nav .nav-link i {
            width: 20px;
            margin-right: 12px;
        }

        /* Wallet & Table */
        .wallet-banner {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            border-radius: 20px;
            padding: 2.5rem;
            color: white;
            margin-bottom: 2rem;
        }

        .table-glass {
            background: white;
            border-radius: 16px;
            border: 1px solid #e5e7eb;
            overflow: hidden;
        }

        .table-glass thead th {
            background: #f8fafc;
            padding: 1rem 1.5rem;
            font-size: 0.75rem;
            color: #64748b;
        }

        .table-glass tbody td {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid #f1f5f9;
        }

        /* Avatar Preview */
        .avt-preview-container {
            position: relative;
            width: 100px;
            height: 100px;
        }

        .avt-img {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid white;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .avt-btn {
            position: absolute;
            bottom: 0;
            right: 0;
            background: var(--primary-color);
            color: white;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid white;
            cursor: pointer;
        }

        .merchant-img-item {
            width: 100%;
            height: 120px;
            object-fit: cover;
            border-radius: 12px;
            border: 1px solid #e5e7eb;
        }
    </style>

    <div class="account-wrapper">
        <!-- SIDEBAR -->
        <aside class="account-sidebar">
            <div class="text-center mb-4">
                <img src="{{ $avatarUrl }}" class="rounded-circle border border-4 border-light mb-2 shadow-sm"
                    width="85" height="85" style="object-fit: cover">
                <h6 class="fw-bold mb-0 text-dark">{{ $user->name }}</h6>
                <p class="text-muted small">ID: #USER-{{ $user->id }}</p>
            </div>

            <nav class="account-nav nav flex-column" id="accountTabs">
                <button class="nav-link active" data-bs-toggle="pill" data-bs-target="#pills-finance"><i
                        class="fas fa-wallet"></i> Tài chính & Ví</button>
                <button class="nav-link" data-bs-toggle="pill" data-bs-target="#pills-general"><i
                        class="fas fa-user-circle"></i> Thông tin cá nhân</button>
                <button class="nav-link" data-bs-toggle="pill" data-bs-target="#pills-venues"><i class="fas fa-store"></i>
                    Danh sách cơ sở</button>
                <hr class="text-muted opacity-25">
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="nav-link text-danger"><i class="fas fa-sign-out-alt"></i> Đăng
                        xuất</button>
                </form>
            </nav>
        </aside>

        <!-- MAIN -->
        <main class="account-main">
            @if (session('success'))
                <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger border-0 shadow-sm rounded-4 mb-4">{{ session('error') }}</div>
            @endif

            <div class="tab-content">
                {{-- TAB: FINANCE --}}
                <div class="tab-pane fade show active" id="pills-finance">
                    <div class="wallet-banner d-flex justify-content-between align-items-center shadow-lg">
                        <div>
                            <span class="opacity-75 mb-1 d-block small fw-medium">SỐ DƯ HIỆN TẠI</span>
                            <h1 class="display-5 fw-bold text-white mb-2">{{ number_format($currentBalance) }} <small
                                    class="fs-4">đ</small></h1>
                            <div class="d-flex gap-4 mt-3">
                                <button class="btn btn-light fw-bold px-4 rounded-3" onclick="toggleWithdraw()"><i
                                        class="fas fa-paper-plane me-2 text-primary"></i> Rút tiền</button>
                                <div class="text-white-50 small">
                                    <div class="fw-bold text-white">{{ number_format($maxWithdrawable) }}đ</div> Khả dụng
                                </div>
                                <div class="text-white-50 small border-start ps-4">
                                    <div class="fw-bold text-white">{{ number_format($minReserve) }}đ</div> Ký quỹ
                                </div>
                            </div>
                        </div>
                        <i class="fas fa-wallet fa-5x opacity-10"></i>
                    </div>

                    {{-- Form Rút tiền --}}
                    <div id="withdrawSection" class="card border-0 shadow-sm rounded-4 p-4 mb-4 d-none">
                        <h6 class="fw-bold mb-3"><i class="fas fa-university me-2 text-primary"></i>Tạo lệnh rút tiền về
                            ngân hàng</h6>
                        <form action="{{ route('owner.withdraw.store') }}" method="POST">
                            @csrf
                            <div class="row align-items-end">
                                <div class="col-md-6">
                                    <label class="small fw-bold text-muted mb-2">Số tiền muốn rút (Min 50.000đ)</label>
                                    <div class="input-group">
                                        <input type="number" name="amount"
                                            class="form-control form-control-lg bg-light border-0"
                                            max="{{ $maxWithdrawable }}" min="50000" required>
                                        <span class="input-group-text bg-light border-0 fw-bold">VNĐ</span>
                                    </div>
                                </div>
                                <div class="col-md-3"><button class="btn btn-primary btn-lg w-100 fw-bold">Gửi yêu
                                        cầu</button></div>
                            </div>
                        </form>
                    </div>

                    {{-- Log giao dịch --}}
                    <div class="row g-4">
                        <div class="col-xl-8">
                            <h5 class="fw-bold mb-3">Biến động số dư</h5>
                            <div class="table-glass shadow-sm">
                                <table class="table mb-0 align-middle">
                                    <thead>
                                        <tr>
                                            <th>Loại</th>
                                            <th>Nội dung</th>
                                            <th>Thời gian</th>
                                            <th class="text-end">Số tiền</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($wallet_log as $log)
                                            <tr>
                                                <td>
                                                    @php
                                                        $types = [
                                                            'deposit' => ['tr-plus', 'fa-plus-circle', 'Thu'],
                                                            'payment' => ['tr-minus', 'fa-shopping-cart', 'Chi'],
                                                            'refund' => ['tr-refund', 'fa-undo', 'Hoàn'],
                                                            'withdraw' => ['tr-withdraw', 'fa-university', 'Rút'],
                                                        ];
                                                        $curr = $types[$log->type] ?? [
                                                            'bg-light',
                                                            'fa-exchange-alt',
                                                            'Khác',
                                                        ];
                                                    @endphp
                                                    <div class="d-flex align-items-center">
                                                        <div class="tr-type-icon {{ $curr[0] }} me-2"><i
                                                                class="fas {{ $curr[1] }}"></i></div>
                                                        <span class="small fw-bold">{{ $curr[2] }}</span>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="small fw-medium">{{ $log->description }}</div>
                                                </td>
                                                <td class="small text-muted">{{ $log->created_at->format('H:i d/m/Y') }}
                                                </td>
                                                <td
                                                    class="text-end fw-bold {{ $log->amount > 0 ? 'text-success' : 'text-danger' }}">
                                                    {{ $log->amount > 0 ? '+' : '' }}{{ number_format($log->amount) }}đ
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-center py-5 text-muted">Chưa có giao dịch.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="col-xl-4">
                            <h5 class="fw-bold mb-3">Lệnh rút tiền</h5>
                            <div class="table-glass shadow-sm small">
                                <table class="table mb-0 align-middle">
                                    <tbody>
                                        @forelse($withdraw as $item)
                                            <tr>
                                                <td><b>#WR-{{ $item->id }}</b><br><small
                                                        class="text-muted">{{ $item->created_at->format('d/m/Y') }}</small>
                                                </td>
                                                <td><span
                                                        class="badge-pill {{ $item->status == 'pending' ? 'badge-pending' : ($item->status == 'approved' ? 'badge-success' : 'bg-danger-subtle text-danger') }}">
                                                        {{ $item->status == 'pending' ? 'Chờ duyệt' : ($item->status == 'approved' ? 'Thành công' : 'Từ chối') }}
                                                    </span></td>
                                                <td class="text-end fw-bold">{{ number_format($item->amount) }}đ</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="3" class="text-center py-4 text-muted">Trống.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- TAB: GENERAL --}}
                <div class="tab-pane fade" id="pills-general">
                    <h4 class="fw-bold mb-4">Cài đặt tài khoản</h4>
                    <div class="card border-0 shadow-sm rounded-4 p-4 mb-5">
                        <form action="{{ route('owner.user.update') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="row g-4">
                                <div class="col-12 d-flex align-items-center gap-4 mb-2">
                                    <div class="avt-preview-container">
                                        <img id="previewAvt" src="{{ $avatarUrl }}" class="avt-img">
                                        <label for="avtFile" class="avt-btn shadow-sm"><i
                                                class="fas fa-camera"></i></label>
                                        <input type="file" name="avt" id="avtFile" class="d-none"
                                            accept="image/*" onchange="previewImage(this)">
                                    </div>
                                    <div>
                                        <h6 class="fw-bold mb-1">Ảnh đại diện</h6>
                                        <p class="text-muted small mb-0">Hỗ trợ định dạng JPG, PNG, WebP.</p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">Họ và tên</label>
                                    <input type="text" name="name" class="form-control"
                                        value="{{ $user->name }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted">Email (Bất biến)</label>
                                    <input type="email" class="form-control bg-light" value="{{ $user->email }}"
                                        readonly>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">Số điện thoại</label>
                                    <input type="text" name="phone" class="form-control"
                                        value="{{ $user->phone }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted">Khu vực</label>
                                    <input type="text" class="form-control bg-light"
                                        value="{{ $user->district->name ?? '' }}, {{ $user->province->name ?? '' }}"
                                        readonly>
                                </div>
                                <div class="col-12 text-end pt-3">
                                    <button type="submit" class="btn btn-primary px-5 fw-bold shadow-sm rounded-3">Cập
                                        nhật hồ sơ</button>
                                </div>
                            </div>
                        </form>
                    </div>

                    {{-- Merchant Bank & Images --}}
                    @if ($user->merchantProfile)
                        <div class="mt-5">
                            <h5 class="fw-bold mb-3">Hồ sơ định danh & Ngân hàng</h5>
                            <div class="card border-0 shadow-sm rounded-4 p-4 bg-dark text-white mb-4">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <div class="small opacity-50 mb-1 text-uppercase">Ngân hàng nhận tiền</div>
                                        <h5 class="fw-bold text-primary mb-3">{{ $user->merchantProfile->bank_name }}</h5>
                                        <div class="h5 text-white fw-bold mb-1 ls-2">
                                            {{ $user->merchantProfile->bank_account_number }}</div>
                                        <div class="small opacity-75">{{ $user->merchantProfile->bank_account_name }}
                                        </div>
                                    </div>
                                    <i class="fas fa-university fa-3x opacity-25"></i>
                                </div>
                            </div>
                            <h6 class="fw-bold text-dark mb-3">Hình ảnh Merchant / Định danh</h6>
                            <div class="row g-3">
                                @foreach ($user->merchantProfile->images as $img)
                                    <div class="col-6 col-md-4 col-lg-3">
                                        <a href="{{ asset($img->url) }}" target="_blank">
                                            <img src="{{ asset($img->url) }}" class="merchant-img-item"
                                                alt="Merchant Image">
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                {{-- TAB: VENUES --}}
                <div class="tab-pane fade" id="pills-venues">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="fw-bold mb-0">Danh sách cơ sở ({{ $venues->total() }})</h4>
                        <a href="#" class="btn btn-primary fw-bold rounded-pill px-4 shadow-sm"><i
                                class="fas fa-plus me-2"></i>Thêm sân mới</a>
                    </div>
                    <div class="row g-4">
                        @foreach ($venues as $venue)
                            <div class="col-md-6 col-xxl-4">
                                <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100">
                                    <div class="p-4">
                                        <div class="d-flex justify-content-between mb-3 align-items-start">
                                            <div class="p-2 bg-light rounded-3 text-primary"><i
                                                    class="fas fa-building"></i></div>
                                            <span
                                                class="badge {{ $venue->is_active ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' }} px-3 py-2">
                                                {{ $venue->is_active ? 'Đang hoạt động' : 'Tạm dừng' }}
                                            </span>
                                        </div>
                                        <h6 class="fw-bold text-dark mb-1">{{ $venue->name }}</h6>
                                        <p class="small text-muted mb-0"><i class="fas fa-map-marker-alt me-1"></i>
                                            {{ $venue->district->name ?? '' }}, {{ $venue->province->name ?? '' }}</p>
                                    </div>
                                    <div class="bg-light p-3 border-top d-flex justify-content-between">
                                        <a href="#" class="btn btn-sm btn-white border px-3 fw-bold">Quản lý</a>
                                        <a href="#" class="btn btn-sm btn-primary px-3 fw-bold">Xem trang</a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-4">{{ $venues->links('pagination::bootstrap-5') }}</div>
                </div>
            </div>
        </main>
    </div>

    <script>
        function toggleWithdraw() {
            document.getElementById('withdrawSection').classList.toggle('d-none');
        }

        function previewImage(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('previewAvt').src = e.target.result;
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
@endsection
