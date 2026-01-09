@extends('app')
@section('content')
    <style>
        .bg-soft-success {
            background-color: #d1fae5;
            color: #065f46;
        }

        .bg-soft-danger {
            background-color: #fee2e2;
            color: #991b1b;
        }

        .bg-soft-warning {
            background-color: #fef3c7;
            color: #92400e;
        }

        .bg-soft-primary {
            background-color: #e0e7ff;
            color: #3730a3;
        }

        .voucher-code {
            font-family: 'Monaco', monospace;
            background: #f8fafc;
            padding: 5px 10px;
            border-radius: 6px;
            font-weight: 700;
            border: 1px dashed #cbd5e1;
            color: #2563eb;
        }
    </style>

    <div class="container-fluid py-4">
        <!-- Bộ lọc nhanh -->
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 1rem;">
            <div class="card-body">
                <form action="{{ route('admin.promotions.index') }}" method="GET" class="row g-3">
                    <div class="col-md-3">
                        <input type="text" name="search" class="form-control" placeholder="Mã voucher hoặc tên sân..."
                            value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <select name="status" class="form-select">
                            <option value="">-- Trạng thái --</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Đang chạy</option>
                            <option value="disabled" {{ request('status') == 'disabled' ? 'selected' : '' }}>Tạm tắt
                            </option>
                            <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>Hết hạn</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="target_user_type" class="form-select">
                            <option value="">-- Đối tượng --</option>
                            <option value="all" {{ request('target_user_type') == 'all' ? 'selected' : '' }}>Tất cả khách
                            </option>
                            <option value="new_user" {{ request('target_user_type') == 'new_user' ? 'selected' : '' }}>Người
                                mới</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary px-4">Lọc dữ liệu</button>
                        <a href="{{ route('admin.promotions.index') }}" class="btn btn-light border">Xóa lọc</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="card border-0 shadow-sm" style="border-radius: 1rem;">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom-0">
                <h5 class="fw-bold mb-0 text-primary"><i class="fas fa-ticket-alt me-2"></i>Quản lý Voucher Hệ Thống</h5>
                <a href="{{ route('admin.promotions.create') }}" class="btn btn-primary px-4 shadow-sm rounded-pill"><i
                        class="fas fa-plus me-2"></i>Tạo mới</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light text-muted uppercase small font-weight-bold">
                            <tr>
                                <th class="ps-4">Mã Voucher</th>
                                <th>Mức Giảm</th>
                                <th>Lượt Dùng</th>
                                <th>Hiệu Lực</th>
                                <th>Phạm Vi / Người Tạo</th>
                                <th>Trạng Thái</th>
                                <th class="text-end pe-4">Thao Tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($promotions as $p)
                                <tr>
                                    <td class="ps-4">
                                        <div class="voucher-code mb-1">{{ $p->code }}</div>
                                        <div class="small text-muted">{{ Str::limit($p->description, 25) }}</div>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark">
                                            {{ $p->type == 'percentage' ? $p->value . '%' : number_format($p->value) . '₫' }}
                                        </div>
                                        <div class="small text-muted">Đơn từ {{ number_format($p->min_order_value) }}₫
                                        </div>
                                        @if ($p->type == 'percentage' && $p->max_discount_amount)
                                            <div class="small text-danger" style="font-size: 0.75rem;">Giam tối đa:
                                                {{ number_format($p->max_discount_amount) }}₫</div>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="small mb-1">{{ $p->used_count }} / {{ $p->usage_limit ?: '∞' }}</div>
                                        <div class="progress" style="height: 5px; width: 80px;">
                                            @php $percent = $p->usage_limit > 0 ? ($p->used_count / $p->usage_limit) * 100 : 0; @endphp
                                            <div class="progress-bar bg-primary" style="width: {{ min($percent, 100) }}%">
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="small fw-bold">{{ $p->start_at->format('d/m/Y') }}</div>
                                        <div class="small text-muted">{{ $p->end_at->format('d/m/Y') }}</div>
                                    </td>
                                    <td>
                                        <div class="mb-1">
                                            @if ($p->venue)
                                                <span class="badge bg-light text-dark border"><i
                                                        class="fas fa-map-marker-alt"></i> {{ $p->venue->name }}</span>
                                            @else
                                                <span class="badge bg-soft-primary"><i class="fas fa-globe"></i> Toàn hệ
                                                    thống</span>
                                            @endif
                                        </div>
                                        <div class="small text-muted">Tạo bởi: <strong>{{ $p->creator->name }}</strong>
                                            ({{ $p->creator->role->name }})
                                        </div>
                                    </td>
                                    <td>
                                        @if ($p->process_status == 'disabled')
                                            <span class="badge bg-soft-danger rounded-pill px-3">Tạm tắt</span>
                                        @elseif($p->isExpired())
                                            <span class="badge bg-soft-warning rounded-pill px-3">Hết hạn</span>
                                        @elseif($p->isActive())
                                            <span class="badge bg-soft-success rounded-pill px-3">Đang chạy</span>
                                        @else
                                            <span class="badge bg-light text-muted rounded-pill px-3">Sắp tới</span>
                                        @endif
                                    </td>
                                    <td class="text-end pe-4">
                                        <div class="btn-group">
                                            <a href="{{ route('admin.promotions.edit', $p) }}"
                                                class="btn btn-sm btn-light border text-warning"><i
                                                    class="fas fa-edit"></i></a>
                                            <form action="{{ route('admin.promotions.destroy', $p) }}" method="POST"
                                                class="d-inline">
                                                @csrf @method('DELETE')
                                                <button class="btn btn-sm btn-light border text-danger"
                                                    onclick="return confirm('Xác nhận xóa?')"><i
                                                        class="fas fa-trash"></i></button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted">Chưa có mã giảm giá nào.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="p-3">{{ $promotions->links() }}</div>
            </div>
        </div>
    </div>
@endsection
