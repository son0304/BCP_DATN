@extends('app')
@section('content')
<style>
    .voucher-code {
        font-family: 'Monaco', 'Consolas', monospace;
        background: #eef2ff;
        padding: 4px 8px;
        border-radius: 4px;
        font-weight: 700;
        color: #4f46e5;
        border: 1px dashed #6366f1;
        display: inline-block;
    }

    .progress-thin {
        height: 6px;
        width: 120px;
        background-color: #e9ecef;
        border-radius: 10px;
    }

    .bg-soft-primary {
        background-color: #e0e7ff;
        color: #3730a3;
    }

    .filter-label {
        font-size: 0.7rem;
        text-transform: uppercase;
        font-weight: 800;
        color: #6c757d;
        margin-bottom: 0.25rem;
        display: block;
    }
</style>

<div class="container-fluid py-4">
    {{-- Bộ lọc nâng cao --}}
    <div class="card border-0 shadow-sm mb-4 rounded-4">
        <div class="card-body p-4">
            <form action="{{ route('admin.promotions.index') }}" method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="filter-label">Tìm kiếm</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i
                                class="fas fa-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control border-start-0 ps-0"
                            placeholder="Mã voucher hoặc mô tả..." value="{{ request('search') }}">
                    </div>
                </div>

                <div class="col-md-2">
                    <label class="filter-label">Trạng thái xử lý</label>
                    <select name="status" class="form-select">
                        <option value="">Tất cả</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Đang bật</option>
                        <option value="disabled" {{ request('status') == 'disabled' ? 'selected' : '' }}>Đang tắt
                        </option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="filter-label">Hiệu lực thời gian</label>
                    <select name="time_status" class="form-select">
                        <option value="">Tất cả</option>
                        <option value="active" {{ request('time_status') == 'active' ? 'selected' : '' }}>Đang diễn ra
                        </option>
                        <option value="expired" {{ request('time_status') == 'expired' ? 'selected' : '' }}>Đã hết hạn
                        </option>
                        <option value="upcoming" {{ request('time_status') == 'upcoming' ? 'selected' : '' }}>Sắp diễn
                            ra</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="filter-label">Đối tượng</label>
                    <select name="target_user_type" class="form-select">
                        <option value="">Tất cả khách</option>
                        <option value="all" {{ request('target_user_type') == 'all' ? 'selected' : '' }}>Mọi người
                            dùng</option>
                        <option value="new_user" {{ request('target_user_type') == 'new_user' ? 'selected' : '' }}>Chỉ
                            người mới</option>
                    </select>
                </div>

                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary px-4 w-100 fw-bold">
                        <i class="fas fa-filter me-2"></i>Lọc dữ liệu
                    </button>
                    <a href="{{ route('admin.promotions.index') }}" class="btn btn-light border px-3">
                        <i class="fas fa-undo"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <div>
                <h5 class="fw-bold mb-0 text-dark">Quản Lý Voucher Hệ Thống</h5>
                <p class="text-muted small mb-0">Danh sách các mã ưu đãi do bạn trực tiếp quản lý</p>
            </div>
            <a href="{{ route('admin.promotions.create') }}"
                class="btn btn-primary text-white fw-bold px-4 rounded-pill shadow-sm">
                <i class="fas fa-plus me-2"></i>Tạo mã mới
            </a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-muted small font-weight-bold">
                        <tr>
                            <th class="ps-4">Mã & Mô tả</th>
                            <th>Giá trị ưu đãi</th>
                            <th>Lượt sử dụng</th>
                            <th>Thời gian hiệu lực</th>
                            <th>Phạm vi áp dụng</th>
                            <th>Trạng thái</th>
                            <th class="text-end pe-4">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($promotions as $p)
                        <tr>
                            <td class="ps-4">
                                <div class="voucher-code mb-1">{{ $p->code }}</div>
                                <div class="small text-muted" style="max-width: 200px;">
                                    {{ Str::limit($p->description, 40) }}
                                </div>
                            </td>
                            <td>
                                <div class="fw-bold text-dark">
                                    {{ $p->type == 'percentage' ? number_format($p->value) . '%' : number_format($p->value) . '₫' }}
                                </div>
                                <div class="small text-muted">Đơn từ: {{ number_format($p->min_order_value) }}₫
                                </div>
                            </td>
                            <td>
                                @if ($p->usage_limit < 0)
                                    <span class="badge bg-info bg-opacity-10 text-primary border border-info">∞ Vô
                                    hạn</span>
                                    <div class="small text-muted mt-1">Dùng: {{ $p->used_count }}</div>
                                    @else
                                    @php
                                    $percent =
                                    $p->usage_limit > 0 ? ($p->used_count / $p->usage_limit) * 100 : 0;
                                    $color =
                                    $percent >= 100
                                    ? 'bg-danger'
                                    : ($percent > 80
                                    ? 'bg-warning'
                                    : 'bg-success');
                                    @endphp
                                    <div class="d-flex justify-content-between small mb-1" style="width: 120px;">
                                        <span>{{ $p->used_count }}/{{ $p->usage_limit }}</span>
                                        <span class="text-muted">{{ round($percent) }}%</span>
                                    </div>
                                    <div class="progress progress-thin">
                                        <div class="progress-bar {{ $color }}"
                                            style="width: {{ $percent }}%"></div>
                                    </div>
                                    @endif
                            </td>
                            <td>
                                <div class="small fw-bold text-dark">{{ $p->start_at->format('d/m/Y H:i') }}</div>
                                <div class="small text-muted">đến {{ $p->end_at->format('d/m/Y H:i') }}</div>
                            </td>
                            <td>
                                @if ($p->venue)
                                <span class="badge bg-light text-dark border">📍
                                    {{ Str::limit($p->venue->name, 15) }}</span>
                                @else
                                <span class="badge bg-soft-primary border border-primary-subtle">🌐 Hệ
                                    thống</span>
                                @endif
                                <div class="small mt-1 text-muted">
                                    {{ $p->target_user_type == 'new_user' ? '🆕 Khách mới' : '👥 Tất cả' }}
                                </div>
                            </td>
                            <td>
                                @if ($p->process_status == 'disabled')
                                <span class="badge bg-secondary rounded-pill">Đã tắt</span>
                                @elseif($p->isExpired())
                                <span class="badge bg-warning text-dark rounded-pill">Hết hạn</span>
                                @elseif($p->start_at > now())
                                <span class="badge bg-info text-white rounded-pill">Sắp tới</span>
                                @else
                                <span class="badge bg-success rounded-pill">Đang chạy</span>
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                <div class="d-inline-flex gap-2">
                                    {{-- Edit --}}
                                    <a href="{{ route('admin.promotions.edit', $p) }}"
                                        class="btn btn-sm btn-light border"
                                        title="Chỉnh sửa">
                                        <i class="far fa-edit text-primary"></i>
                                    </a>

                                    {{-- Delete --}}
                                    <form action="{{ route('admin.promotions.destroy', $p) }}"
                                        method="POST"
                                        onsubmit="return confirm('Bạn có chắc muốn xóa mã này không?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="btn btn-sm btn-light border"
                                            title="Xóa">
                                            <i class="far fa-trash-alt text-danger"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="fas fa-ticket-alt fa-3x mb-3 opacity-25"></i>
                                <p>Không tìm thấy mã giảm giá nào phù hợp.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-4 py-3 border-top">
                {{ $promotions->links() }}
            </div>
        </div>
    </div>
</div>
@endsection