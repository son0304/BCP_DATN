@extends('app')

@section('content')
<div class="container-fluid py-4">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 fw-bold">Quản lý thương hiệu sân</h1>
            <p class="text-muted mb-0">Danh sách tất cả các sân trong hệ thống.</p>
        </div>
        <a href="{{ route('admin.brand.create') }}" class="btn btn-primary shadow-sm px-4">
            <i class="fas fa-plus me-2"></i> Thêm sân mới
        </a>
    </div>

    {{-- Thông báo thành công --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Danh sách sân --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 60px;">ID</th>
                            <th>Tên sân</th>
                            <th>Chủ sở hữu</th>
                            <th>Địa điểm</th>
                            <th class="text-center" style="width: 140px;">Giờ mở cửa</th>
                            <th class="text-center" style="width: 140px;">Giờ đóng cửa</th>
                            <th class="text-center" style="width: 140px;">Trạng thái</th>
                            <th class="text-center" style="width: 180px;">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($venues as $venue)
                        <tr>
                            <td class="fw-semibold">{{ $venue->id }}</td>
                            <td>
                                <strong class="text-dark d-block">{{ $venue->name }}</strong>   
                            </td>
                            <td>{{ $venue->owner->name ?? 'N/A' }}</td>
                            <td>{{ $venue->province->name ?? 'N/A' }}</td>

                            {{-- Giờ mở cửa --}}
                            <td class="text-center">
                                @if($venue->start_time)
                                    <span class="badge bg-info-subtle border border-info-subtle text-info-emphasis rounded-pill px-3 py-2">
                                        {{ \Carbon\Carbon::parse($venue->start_time)->format('H:i') }}
                                    </span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>

                            {{-- Giờ đóng cửa --}}
                            <td class="text-center">
                                @if($venue->end_time)
                                    <span class="badge bg-warning-subtle border border-warning-subtle text-warning-emphasis rounded-pill px-3 py-2">
                                        {{ \Carbon\Carbon::parse($venue->end_time)->format('H:i') }}
                                    </span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>

                            {{-- Trạng thái --}}
                            <td class="text-center">
                                @if($venue->is_active)
                                    <span class="badge bg-success-subtle border border-success-subtle text-success-emphasis rounded-pill px-3 py-2">
                                        Hoạt động
                                    </span>
                                @else
                                    <span class="badge bg-danger-subtle border border-danger-subtle text-danger-emphasis rounded-pill px-3 py-2">
                                        Đã khóa
                                    </span>
                                @endif
                            </td>

                            {{-- Hành động --}}
                            <td class="text-end">
                                <a href="{{ route('admin.brand.edit', $venue->id) }}" class="btn btn-sm btn-outline-secondary me-2">
                                    <i class="fas fa-edit"></i> Sửa
                                </a>
                                <form action="{{ route('admin.brand.destroy', $venue->id) }}" method="POST" class="d-inline" 
                                    onsubmit="return confirm('Bạn có chắc chắn muốn XÓA VĨNH VIỄN sân này? Hành động này không thể hoàn tác!')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="fas fa-trash"></i> Xóa
                                    </button>
                                </form>
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
