@extends('app')
@section('content')

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
                                    <label class="form-label fw-semibold d-block mb-1">Loại</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="type[]" id="type_percent" value="%"
                                            {{ collect(request('type'))->contains('%') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="type_percent">Phần trăm (%)</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="type[]" id="type_vnd" value="VND"
                                            {{ collect(request('type'))->contains('VND') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="type_vnd">Tiền mặt (VND)</label>
                                    </div>
                                </div>

                                <!-- Status -->
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold d-block mb-1">Trạng thái</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="status[]" id="status_active" value="active"
                                            {{ collect(request('status'))->contains('active') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="status_active">Đang hoạt động</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="status[]" id="status_expired" value="expired"
                                            {{ collect(request('status'))->contains('expired') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="status_expired">Đã hết hạn</label>
                                    </div>
                                </div>

                                <!-- Buttons -->
                                <div class="col-md-3 d-flex align-items-end">
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
                                <tr class="text-center">
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
                                <tr class="text-center">
                                    <td>{{ $promotion->id }}</td>
                                    <td>
                                        <span class="voucher-code">{{ $promotion->code }}</span>
                                    </td>
                                    <td>
                                        <strong>
                                            @if($promotion->type == '%')
                                            {{ number_format($promotion->value, 0) }}%
                                            @if($promotion->max_discount_amount)
                                            <div class="text-muted small">Tối đa {{ number_format($promotion->max_discount_amount, 0) }}₫</div>
                                            @endif
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
                                        {{ $promotion->used_count }} / {{ $promotion->usage_limit }}
                                    </td>
                                    <td>
                                        @php
                                        // So sánh trực tiếp với datetime từ database
                                        $now = now();
                                        $startAt = \Carbon\Carbon::parse($promotion->start_at);
                                        $endAt = \Carbon\Carbon::parse($promotion->end_at);

                                        // Logic kiểm tra trạng thái
                                        $isNotStarted = $startAt->gt($now); // start_at > now
                                        $isExpired = $endAt->lt($now); // end_at < now
                                            $isOutOfUsage=$promotion->usage_limit > 0 && $promotion->used_count >= $promotion->usage_limit;
                                            @endphp
                                            @if($isNotStarted)
                                            <span class="badge bg-info">Chưa hoạt động</span>
                                            @elseif($isExpired)
                                            <span class="badge bg-danger">Hết hạn</span>
                                            @elseif($isOutOfUsage)
                                            <span class="badge bg-warning">Hết lượt sử dụng</span>
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
                                                class="btn btn-outline-primary btn-sm me-2" title="Xem chi tiết">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.promotions.edit', $promotion) }}"
                                                class="btn btn-outline-warning btn-sm me-2" title="Sửa">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form method="POST" action="{{ route('admin.promotions.destroy', $promotion) }}"
                                                class="d-inline"
                                                onsubmit="return confirm('Bạn có chắc chắn muốn xóa voucher này?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger btn-sm" title="Xóa">
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