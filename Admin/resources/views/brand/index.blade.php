@extends('app')

@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Quản lý thương hiệu sân</h1>
            <p class="text-muted mb-0">Danh sách tất cả các sân trong hệ thống.</p>
        </div>
        <a href="{{ route('brand.create') }}" class="btn btn-primary shadow-sm">
            <i class="fas fa-plus me-1"></i> Thêm sân mới
        </a>
    </div>
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">Tên sân</th>
                            <th scope="col">Chủ sở hữu</th>
                            <th scope="col">Địa điểm</th>
                            <th scope="col" class="text-center">Trạng thái</th>
                            <th scope="col" class="text-end">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($venues as $venue)
                        <tr>
                            <td>{{ $venue->id }}</td>
                            <td>
                                <strong class="d-block text-black">{{ $venue->name }}</strong>
                                <small class="text-muted">{{ $venue->address_detail ?? 'Chưa có địa chỉ chi tiết' }}</small>
                            </td>
                            <td>{{ $venue->owner->name ?? 'N/A' }}</td>
                            <td>{{ $venue->province->name ?? 'N/A' }}</td>
                            <td class="text-center">
                                @if($venue->is_active)
                                    <span class="badge bg-success-subtle border border-success-subtle text-success-emphasis rounded-pill">Hoạt động</span>
                                @else
                                    <span class="badge bg-danger-subtle border border-danger-subtle text-danger-emphasis rounded-pill">Đã khóa</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('brand.edit', $venue->id) }}" class="btn btn-sm btn-outline-secondary text-black" title="Sửa">
                                    <i class="fas fa-edit"></i> Sửa
                                </a>
                                <form action="{{ route('brand.destroy', $venue->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn XÓA VĨNH VIỄN sân này? Hành động này không thể hoàn tác!')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Xóa vĩnh viễn">
                                        <i class="fas fa-trash"></i> Xóa
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
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
        @if ($venues->hasPages())
        <div class="card-footer bg-white">
            {{ $venues->links() }}
        </div>
        @endif
    </div>
</div>
@endsection