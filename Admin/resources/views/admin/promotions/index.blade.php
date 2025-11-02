@extends('app')
@section('content')

    <style>
        :root {
            --primary-color: #348738;
            --accent-color: #f97316;
            --card-radius: 12px;
            --card-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f9fa;
        }

        .card {
            border-radius: var(--card-radius);
            box-shadow: var(--card-shadow);
            border: none;
        }

        .card-header {
            background-color: #fff;
            border-bottom: 1px solid #e5e5e5;
            font-weight: 600;
        }

        .table th,
        .table td {
            vertical-align: middle !important;
        }

        .badge-voucher {
            font-size: 0.85rem;
            padding: 0.35em 0.65em;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .btn-sm i {
            pointer-events: none;
        }

        .search-filter-container {
            margin-bottom: 20px;
            background-color: #f1f6f1;
            padding: 15px 20px;
            border-radius: 10px;
        }

        .alert-info {
            background-color: #e9f7ef;
            border-color: #c6e0c3;
            color: #2d6a4f;
        }

        .table-hover tbody tr:hover {
            background-color: #f1f6f1;
        }

        .btn-action-group .btn {
            margin-right: 5px;
        }

        .voucher-code {
            font-family: 'Courier New', monospace;
            font-weight: bold;
            color: var(--primary-color);
        }
    </style>

    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">

                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3 class="card-title mb-0">Quản lý Voucher</h3>
                        <a href="{{ route('admin.promotions.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i> Tạo voucher mới
                        </a>
                    </div>

                    <!-- Search & Filter -->
                    <div class="card-body">
                        <!-- Filter container -->
                        <div class="p-3 mb-4 rounded-3" style="background-color: #f8f9fa;">
                            <form method="GET" action="{{ route('admin.promotions.index') }}">
                                <div class="row g-3">

                                    <!-- Search -->
                                    <div class="col-md-3">
                                        <label for="search" class="form-label fw-semibold">Tìm kiếm</label>
                                        <input type="text" id="search" name="search" value="{{ request('search') }}"
                                            class="form-control" placeholder="Mã voucher...">
                                    </div>

                                    <!-- Type -->
                                    <div class="col-md-3">
                                        <label for="type" class="form-label fw-semibold">Loại</label>
                                        <select id="type" name="type" class="form-control">
                                            <option value="">Tất cả loại</option>
                                            <option value="%" {{ request('type') == '%' ? 'selected' : '' }}>Phần trăm (%)</option>
                                            <option value="VND" {{ request('type') == 'VND' ? 'selected' : '' }}>Tiền mặt (VND)</option>
                                        </select>
                                    </div>

                                    <!-- Status -->
                                    <div class="col-md-3">
                                        <label for="status" class="form-label fw-semibold">Trạng thái</label>
                                        <select id="status" name="status" class="form-control">
                                            <option value="">Tất cả trạng thái</option>
                                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Đang hoạt động</option>
                                            <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>Đã hết hạn</option>
                                        </select>
                                    </div>

                                    <!-- Buttons -->
                                    <div class="col-md-3 d-flex flex-column justify-content-end">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-search me-1"></i> Tìm kiếm
                                        </button>
                                    </div>

                                </div>
                            </form>
                        </div>

                        <!-- Alerts -->
                        @if (session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        @if (session('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        <!-- Promotions Table -->
                        <div class="table-responsive">
                            <table class="table table-hover table-bordered align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Mã voucher</th>
                                        <th>Giá trị</th>
                                        <th>Loại</th>
                                        <th>Ngày bắt đầu</th>
                                        <th>Ngày kết thúc</th>
                                        <th>Sử dụng</th>
                                        <th>Trạng thái</th>
                                        <th>Người tạo</th>
                                        <th>Ngày tạo</th>
                                        <th>Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($promotions as $promotion)
                                        <tr>
                                            <td>{{ $promotion->id }}</td>
                                            <td>
                                                <span class="voucher-code">{{ $promotion->code }}</span>
                                            </td>
                                            <td>
                                                <strong>
                                                    @if($promotion->type == '%')
                                                        {{ number_format($promotion->value, 0) }}%
                                                    @else
                                                        {{ number_format($promotion->value, 0) }}₫
                                                    @endif
                                                </strong>
                                            </td>
                                            <td>
                                                @if($promotion->type == '%')
                                                    <span class="badge bg-info">Phần trăm</span>
                                                @else
                                                    <span class="badge bg-warning">Tiền mặt</span>
                                                @endif
                                            </td>
                                            <td>{{ \Carbon\Carbon::parse($promotion->start_at, 'UTC')->setTimezone('Asia/Ho_Chi_Minh')->format('d/m/Y H:i') }}</td>
                                            <td>{{ \Carbon\Carbon::parse($promotion->end_at, 'UTC')->setTimezone('Asia/Ho_Chi_Minh')->format('d/m/Y H:i') }}</td>
                                            <td>
                                                {{ $promotion->used_count }} / 
                                                @if($promotion->usage_limit == 0)
                                                    <span class="text-muted">Không giới hạn</span>
                                                @else
                                                    {{ $promotion->usage_limit }}
                                                @endif
                                            </td>
                                            <td>
                                                @php
                                                    // So sánh trực tiếp với datetime từ database
                                                    $now = now();
                                                    $startAt = \Carbon\Carbon::parse($promotion->start_at);
                                                    $endAt = \Carbon\Carbon::parse($promotion->end_at);
                                                    
                                                    // Logic đơn giản: chỉ kiểm tra thời gian
                                                    $isNotStarted = $startAt->gt($now);  // start_at > now
                                                    $isExpired = $endAt->lt($now);      // end_at < now
                                                @endphp
                                                @if($isNotStarted)
                                                    <span class="badge bg-info">Chưa hoạt động</span>
                                                @elseif($isExpired)
                                                    <span class="badge bg-danger">Hết hạn</span>
                                                @else
                                                    <span class="badge bg-success">Đang hoạt động</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($promotion->creator)
                                                    <span class="badge bg-secondary">{{ $promotion->creator->name }}</span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>{{ $promotion->created_at->format('d/m/Y H:i') }}</td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('admin.promotions.show', $promotion) }}"
                                                        class="btn btn-info btn-sm" title="Xem chi tiết">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('admin.promotions.edit', $promotion) }}"
                                                        class="btn btn-warning btn-sm" title="Sửa">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <form method="POST" action="{{ route('admin.promotions.destroy', $promotion) }}"
                                                        class="d-inline"
                                                        onsubmit="return confirm('Bạn có chắc chắn muốn xóa voucher này?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger btn-sm" title="Xóa">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="11" class="text-center py-3">Không có voucher nào</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-center mt-3">
                            {{ $promotions->appends(request()->query())->links() }}
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </div>

@endsection

