@extends('app')

@section('content')
    @php
        $minReserve = 300000;
        $currentBalance = $wallet->balance ?? 0;
        $maxWithdrawable = max(0, $currentBalance - $minReserve);
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

        /* Tối ưu Full màn hình */
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

        /* --- ICON PHÂN LOẠI GIAO DỊCH (GIỮ TỪ BẢN CŨ) --- */
        .tr-type-icon {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.9rem;
        }
        .tr-plus { background-color: #e8fff3; color: #1cc88a; }
        .tr-minus { background-color: #fff0f0; color: #e74a3b; }
        .tr-refund { background-color: #e1f5fe; color: #03a9f4; }
        .tr-withdraw { background-color: #fff9e6; color: #f6c23e; }

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
        }
        .account-nav .nav-link.active { background: #eef2ff; color: var(--primary-color); }
        .account-nav .nav-link i { width: 20px; margin-right: 12px; }

        /* Banner Ví */
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
        .table-glass tbody td { padding: 1rem 1.5rem; border-bottom: 1px solid #f1f5f9; }

        .badge-pill {
            padding: 5px 12px;
            border-radius: 99px;
            font-size: 11px;
            font-weight: 600;
        }
        .badge-pending { background: #fef3c7; color: #92400e; }
        .badge-success { background: #dcfce7; color: #166534; }
    </style>

    <div class="account-wrapper">
        <!-- SIDEBAR -->
        <aside class="account-sidebar">
            <div class="text-center mb-4">
                <img src="{{ $user->avt ? asset($user->avt) : 'https://ui-avatars.com/api/?name=' . urlencode($user->name) }}" class="rounded-circle border border-4 border-light mb-2" width="85" height="85">
                <h6 class="fw-bold mb-0 text-dark">{{ $user->name }}</h6>
                <p class="text-muted small">ID: #USER-{{ $user->id }}</p>
            </div>

            <nav class="account-nav nav flex-column" id="accountTabs">
                <a class="nav-link active" data-bs-toggle="pill" href="#pills-finance">
                    <i class="fas fa-wallet"></i> Tài chính & Ví
                </a>
                <a class="nav-link" data-bs-toggle="pill" href="#pills-general">
                    <i class="fas fa-user-circle"></i> Thông tin cá nhân
                </a>
                <a class="nav-link" data-bs-toggle="pill" href="#pills-venues">
                    <i class="fas fa-store"></i> Danh sách cơ sở
                </a>
                <hr class="text-muted opacity-25">
                <a href="#" class="nav-link text-danger">
                    <i class="fas fa-sign-out-alt"></i> Đăng xuất
                </a>
            </nav>
        </aside>

        <!-- MAIN CONTENT -->
        <main class="account-main">
            <div class="tab-content">

                {{-- TAB: TÀI CHÍNH (VÍ & GIAO DỊCH) --}}
                <div class="tab-pane fade show active" id="pills-finance">
                    <div class="wallet-banner d-flex justify-content-between align-items-center shadow-lg">
                        <div>
                            <span class="opacity-75 mb-1 d-block small fw-medium">SỐ DƯ HIỆN TẠI</span>
                            <h1 class="display-5 fw-bold text-white mb-2">{{ number_format($currentBalance) }} <small class="fs-4">đ</small></h1>
                            <div class="d-flex gap-4 mt-3">
                                <button class="btn btn-light fw-bold px-4 rounded-3" onclick="toggleWithdraw()">
                                    <i class="fas fa-paper-plane me-2"></i> Rút tiền
                                </button>
                                <div class="text-white-50 small">
                                    <div class="fw-bold text-white">{{ number_format($maxWithdrawable) }}đ</div>
                                    Khả dụng để rút
                                </div>
                                <div class="text-white-50 small border-start ps-4">
                                    <div class="fw-bold text-white">{{ number_format($minReserve) }}đ</div>
                                    Ký quỹ tối thiểu
                                </div>
                            </div>
                        </div>
                        <i class="fas fa-wallet fa-5x opacity-10"></i>
                    </div>

                    <div id="withdrawSection" class="card border-0 shadow-sm rounded-4 p-4 mb-4 d-none">
                        <h6 class="fw-bold mb-3"><i class="fas fa-university me-2"></i>Tạo lệnh rút tiền về ngân hàng</h6>
                        <form action="{{ route('owner.withdraw.store') }}" method="POST">
                            @csrf
                            <div class="row align-items-end">
                                <div class="col-md-6">
                                    <label class="small fw-bold text-muted mb-2">Số tiền muốn rút (Tối thiểu 50.000đ)</label>
                                    <div class="input-group">
                                        <input type="number" name="amount" class="form-control form-control-lg bg-light border-0"
                                               max="{{ $maxWithdrawable }}" min="50000" placeholder="0">
                                        <span class="input-group-text bg-light border-0 fw-bold">VNĐ</span>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <button class="btn btn-primary btn-lg w-100 fw-bold">Gửi yêu cầu</button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div class="row g-4">
                        {{-- BẢNG LỊCH SỬ VÍ VỚI ICON CŨ --}}
                        <div class="col-xl-8">
                            <h5 class="fw-bold mb-3">Biến động số dư</h5>
                            <div class="table-glass shadow-sm">
                                <table class="table mb-0 align-middle">
                                    <thead>
                                        <tr>
                                            <th>Giao dịch</th>
                                            <th>Nội dung</th>
                                            <th>Thời gian</th>
                                            <th class="text-end">Số tiền</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($wallet_log as $log)
                                        <tr>
                                            <td style="width: 160px;">
                                                <div class="d-flex align-items-center">
                                                    @switch($log->type)
                                                        @case('deposit')
                                                            <div class="tr-type-icon tr-plus me-2"><i class="fas fa-plus-circle"></i></div>
                                                            <span class="small fw-bold">Thu nhập</span>
                                                        @break
                                                        @case('payment')
                                                            <div class="tr-type-icon tr-minus me-2"><i class="fas fa-shopping-cart"></i></div>
                                                            <span class="small fw-bold">Thanh toán</span>
                                                        @break
                                                        @case('refund')
                                                            <div class="tr-type-icon tr-refund me-2"><i class="fas fa-undo"></i></div>
                                                            <span class="small fw-bold">Hoàn tiền</span>
                                                        @break
                                                        @case('withdraw')
                                                            <div class="tr-type-icon tr-withdraw me-2"><i class="fas fa-university"></i></div>
                                                            <span class="small fw-bold">Rút tiền</span>
                                                        @break
                                                        @default
                                                            <div class="tr-type-icon bg-light me-2"><i class="fas fa-exchange-alt text-muted"></i></div>
                                                            <span class="small fw-bold">Khác</span>
                                                    @endswitch
                                                </div>
                                            </td>
                                            <td>
                                                <div class="fw-medium text-dark small mb-1">{{ $log->description }}</div>
                                                <div class="text-muted" style="font-size: 10px;">Số dư cuối: {{ number_format($log->after_balance) }}đ</div>
                                            </td>
                                            <td class="small text-muted">{{ $log->created_at->format('H:i d/m/Y') }}</td>
                                            <td class="text-end fw-bold {{ $log->amount > 0 ? 'text-success' : 'text-danger' }}">
                                                {{ $log->amount > 0 ? '+' : '' }}{{ number_format($log->amount) }}đ
                                            </td>
                                        </tr>
                                        @empty
                                        <tr><td colspan="4" class="text-center py-5 text-muted">Chưa có giao dịch nào.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- BẢNG LỊCH SỬ RÚT TIỀN --}}
                        <div class="col-xl-4">
                            <h5 class="fw-bold mb-3">Lệnh rút tiền</h5>
                            <div class="table-glass shadow-sm">
                                <table class="table mb-0 align-middle">
                                    <thead>
                                        <tr>
                                            <th>Mã lệnh</th>
                                            <th>Trạng thái</th>
                                            <th class="text-end">Số tiền</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($withdraw as $item)
                                        <tr>
                                            <td>
                                                <div class="fw-bold text-dark small">#WR-{{ $item->id }}</div>
                                                <small class="text-muted" style="font-size: 10px;">{{ $item->created_at->format('d/m/Y') }}</small>
                                            </td>
                                            <td>
                                                @php
                                                    $st = $item->status;
                                                    $class = $st == 'pending' ? 'pending' : ($st == 'rejected' ? 'failed' : 'success');
                                                    $text = $st == 'pending' ? 'Chờ duyệt' : ($st == 'rejected' ? 'Từ chối' : 'Thành công');
                                                @endphp
                                                <span class="badge-pill badge-{{ $class }}">{{ $text }}</span>
                                            </td>
                                            <td class="text-end fw-bold small">{{ number_format($item->amount) }}đ</td>
                                        </tr>
                                        @empty
                                        <tr><td colspan="3" class="text-center py-4 text-muted small">Chưa có lệnh nào.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- TAB: THÔNG TIN CÁ NHÂN --}}
                <div class="tab-pane fade" id="pills-general">
                    <h4 class="fw-bold mb-4">Cài đặt tài khoản</h4>
                    <div class="card border-0 shadow-sm rounded-4 p-4">
                        <form class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">Họ và tên</label>
                                <input type="text" class="form-control" value="{{ $user->name }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">Email</label>
                                <input type="email" class="form-control bg-light" value="{{ $user->email }}" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">Số điện thoại</label>
                                <input type="text" class="form-control" value="{{ $user->phone }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">Khu vực</label>
                                <input type="text" class="form-control bg-light" value="{{ $user->district->name }}, {{ $user->province->name }}" readonly>
                            </div>
                            <div class="col-12 border-top pt-4">
                                <button type="button" class="btn btn-primary px-4 fw-bold">Cập nhật thông tin</button>
                            </div>
                        </form>
                    </div>

                    @if($user->merchantProfile)
                    <div class="mt-5">
                        <h5 class="fw-bold mb-3">Tài khoản ngân hàng thụ hưởng</h5>
                        <div class="card border-0 shadow-sm rounded-4 p-4 bg-dark text-white">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <div class="small opacity-50 mb-1 text-uppercase ls-1">Ngân hàng nhận tiền</div>
                                    <h5 class="fw-bold text-primary mb-3">{{ $user->merchantProfile->bank_name }}</h5>
                                    <div class="h5 fw-bold mb-1 ls-2">{{ $user->merchantProfile->bank_account_number }}</div>
                                    <div class="small opacity-75 text-uppercase">{{ $user->merchantProfile->bank_account_name }}</div>
                                </div>
                                <i class="fas fa-university fa-3x opacity-25"></i>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>

                {{-- TAB: DANH SÁCH CƠ SỞ --}}
                <div class="tab-pane fade" id="pills-venues">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="fw-bold mb-0">Cơ sở kinh doanh ({{ $venues->total() }})</h4>
                        <button class="btn btn-primary fw-bold rounded-pill shadow-sm"><i class="fas fa-plus me-2"></i>Thêm sân mới</button>
                    </div>
                    <div class="row g-4">
                        @foreach($venues as $venue)
                        <div class="col-md-6 col-xxl-4">
                            <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100">
                                <div class="p-4">
                                    <div class="d-flex justify-content-between mb-3 align-items-start">
                                        <div class="p-2 bg-light rounded-3"><i class="fas fa-building text-primary"></i></div>
                                        <span class="badge {{ $venue->is_active ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' }} px-3 py-2">
                                            {{ $venue->is_active ? 'Đang hoạt động' : 'Tạm dừng' }}
                                        </span>
                                    </div>
                                    <h5 class="fw-bold text-dark mb-1">{{ $venue->name }}</h5>
                                    <p class="small text-muted mb-0"><i class="fas fa-map-marker-alt me-1"></i> {{ $venue->district->name }}, {{ $venue->province->name }}</p>
                                </div>
                                <div class="bg-light p-3 border-top d-flex justify-content-between">
                                    <a href="#" class="btn btn-sm btn-white border px-3 fw-bold">Quản lý</a>
                                    <a href="#" class="btn btn-sm btn-primary px-3 fw-bold">Xem trang</a>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

            </div>
        </main>
    </div>

    <script>
        function toggleWithdraw() {
            const section = document.getElementById('withdrawSection');
            section.classList.toggle('d-none');
        }
    </script>
@endsection
