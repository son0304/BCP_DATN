@extends('app')

@section('content')
{{-- Thêm <style> để định nghĩa màu xanh lá/cam --}}
<style>
    :root {
        --bs-primary: #348738;
        --bs-primary-rgb: 52, 135, 56;
        --bs-primary-dark: #2d6a2d;
        --bs-primary-bg-subtle: #e1f3e2;
        --bs-primary-border-subtle: #d1e7dd;

        --bs-accent: #f97316; /* orange-500 */
        --bs-accent-dark: #ea580c; /* orange-600 */
    }

    /* Ghi đè nút Primary */
    .btn-primary {
        --bs-btn-bg: var(--bs-primary);
        --bs-btn-border-color: var(--bs-primary);
        --bs-btn-hover-bg: var(--bs-primary-dark);
        --bs-btn-hover-border-color: var(--bs-primary-dark);
    }
    .btn-outline-primary {
        --bs-btn-color: var(--bs-primary);
        --bs-btn-border-color: var(--bs-primary);
        --bs-btn-hover-bg: var(--bs-primary);
        --bs-btn-hover-border-color: var(--bs-primary);
        --bs-btn-hover-color: #fff;
    }
    .text-primary { color: var(--bs-primary) !important; }

    /* Nút Cam (Accent/CTA) */
    .btn-accent {
        background-color: var(--bs-accent);
        border-color: var(--bs-accent);
        color: #fff;
    }
    .btn-accent:hover {
        background-color: var(--bs-accent-dark);
        border-color: var(--bs-accent-dark);
        color: #fff;
    }

    /* Header bảng màu xanh */
    .table-primary-green {
        background-color: var(--bs-primary);
        color: #fff;
    }
</style>

<div class="container-fluid py-4">

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 py-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h4 mb-0 fw-bold">Quản lý thương hiệu sân</h1>
                    <p class="text-muted mb-0 small">Danh sách tất cả các sân trong hệ thống.</p>
                </div>
                <div>
                    {{-- Nút "Thêm mới" đã bị xóa theo yêu cầu --}}
                </div>
            </div>
        </div>

        <div class="card-body p-0">


            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-primary-green">
                        <tr>
                            <th style="width: 60px;">ID</th>
                            <th>Tên sân</th>
                            <th>Chủ sở hữu</th>
                            <th>Địa điểm</th>
                            <th class="text-center" style="width: 130px;">Giờ mở cửa</th>
                            <th class="text-center" style="width: 130px;">Giờ đóng cửa</th>
                            <th class="text-center" style="width: 140px;">Trạng thái</th>
                            <th class="text-end" style="width: 140px;">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($venues as $venue)
                        <tr>
                            <td class="fw-semibold">#{{ $venue->id }}</td>
                            <td>
                                <strong class="text-dark d-block">{{ $venue->name }}</strong>
                                <small class="text-muted">{{ $venue->phone ?? 'Chưa có SĐT' }}</small>
                            </td>
                            <td>{{ $venue->owner->name ?? 'N/A' }}</td>
                            <td>{{ $venue->province->name ?? 'N/A' }}</td>

                            <td class="text-center">
                                @if ($venue->start_time)
                                    <span class="badge bg-primary-subtle border border-primary-subtle text-primary-emphasis rounded-pill px-3 py-2">
                                        {{ \Carbon\Carbon::parse($venue->start_time)->format('H:i') }}
                                    </span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>

                            <td class="text-center">
                                @if ($venue->end_time)
                                    <span class="badge bg-warning-subtle border border-warning-subtle text-warning-emphasis rounded-pill px-3 py-2">
                                        {{ \Carbon\Carbon::parse($venue->end_time)->format('H:i') }}
                                    </span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>

                            {{-- --- ĐÃ SỬA: Chuyển lại thành Badge (nhãn) tĩnh --- --}}
                            <td class="text-center">
                                @if ($venue->is_active == 1)
                                    <span class="badge bg-success-subtle border border-success-subtle text-success-emphasis rounded-pill px-3 py-2">
                                        Hoạt động
                                    </span>
                                @else
                                    <span class="badge bg-danger-subtle border border-danger-subtle text-danger-emphasis rounded-pill px-3 py-2">
                                        Đã khóa
                                    </span>
                                @endif
                            </td>

                            {{-- Hành động (Nút "Xem" màu cam) --}}
                            <td class="text-end">
                                <a href="{{ route('admin.venues.show', $venue->id) }}" class="btn btn-sm btn-accent me-2">
                                    <i class="fas fa-eye me-1"></i> Xem
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
                                <h5 class="mb-1">Không tìm thấy thương hiệu sân nào</h5>
                                <p class="text-muted">Hãy bắt đầu bằng cách thêm một sân mới.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Phân trang --}}
        @if ($venues->hasPages())
        <div class="card-footer bg-white border-0 py-3">
            <div class="d-flex justify-content-center">
                {{ $venues->links() }}
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
@endpush
