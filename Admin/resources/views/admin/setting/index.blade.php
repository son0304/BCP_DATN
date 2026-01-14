@extends('app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h4 fw-bold text-dark mb-0">Quản lý Banner</h2>
        <button class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#addBannerModal">
            <i class="fas fa-plus me-1"></i> Thêm mới
        </button>
    </div>

    @if (session('success'))
    <div class="alert alert-success border-0 shadow-sm alert-dismissible fade show">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle border-0 mb-0">
                <thead class="table-light text-uppercase">
                    <tr>
                        <th class="text-center" style="width: 150px;">Ảnh</th>
                        <th>Tiêu đề & Link</th>
                        <th class="text-center">Vị trí</th>
                        <th class="text-center">Trạng thái</th>
                        <th class="text-center">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($banners as $banner)
                    @php
                    $img = $banner->images->first();
                    $displayUrl =
                    $img && $img->url
                    ? (str_starts_with($img->url, 'http')
                    ? $img->url
                    : asset('storage/' . $img->url))
                    : 'https://placehold.co/600x300?text=No+Image';
                    @endphp
                    <tr>
                        <td class="text-center">
                            <img src="{{ $displayUrl }}" class="rounded shadow-sm border"
                                style="width: 120px; height: 60px; object-fit: cover;">
                        </td>
                        <td>
                            <div class="fw-bold text-dark">{{ $banner->title }}</div>
                            <div class="small text-muted text-truncate" style="max-width: 250px;">
                                {{ $banner->target_url ?: 'Không có link' }}
                            </div>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-info text-white">{{ $banner->position }}</span>
                            <div class="small text-muted mt-1">Ưu tiên: {{ $banner->priority }}</div>
                        </td>
                        <td class="text-center">
                            <span class="badge {{ $banner->is_active ? 'bg-success' : 'bg-secondary' }}">
                                {{ $banner->is_active ? 'Hiển thị' : 'Tạm ẩn' }}
                            </span>
                        </td>
                        <td class="text-center">
                            <div class="btn-group shadow-sm">
                                <button class="btn btn-white btn-sm border" data-bs-toggle="modal"
                                    data-bs-target="#editBanner{{ $banner->id }}">
                                    <i class="fas fa-edit text-primary"></i>
                                </button>
                                <form action="{{ route('admin.settings.banners.destroy', $banner->id) }}"
                                    method="POST" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-white btn-sm border text-danger"
                                        onclick="return confirm('Xác nhận xóa banner này?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>

                    {{-- MODAL EDIT --}}
                    <div class="modal fade" id="editBanner{{ $banner->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog">
                            <form action="{{ route('admin.settings.banners.update', $banner->id) }}" method="POST"
                                enctype="multipart/form-data">
                                @csrf @method('PUT')
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title fw-bold">Chỉnh sửa Banner #{{ $banner->id }}</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        {{-- Preview Ảnh --}}
                                        <div class="mb-3 text-center">
                                            <label class="form-label fw-bold small d-block text-start">Hình ảnh hiện
                                                tại</label>
                                            <img src="{{ $displayUrl }}" id="preview-edit-{{ $banner->id }}"
                                                class="rounded border shadow-sm mb-2"
                                                style="max-width: 100%; height: 120px; object-fit: cover;">
                                            <input type="file" name="image" class="form-control"
                                                onchange="previewImage(this, 'preview-edit-{{ $banner->id }}')">
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label fw-bold small">Tiêu đề</label>
                                            <input type="text" name="title"
                                                class="form-control @error('title') is-invalid @enderror"
                                                value="{{ old('title', $banner->title) }}">
                                        </div>

                                        {{-- THÊM TRƯỜNG POSITION VÀO ĐÂY --}}
                                        <div class="mb-3">
                                            <label class="form-label fw-bold small">Vị trí hiển thị</label>
                                            <select name="position"
                                                class="form-select @error('position') is-invalid @enderror">
                                                <option value="home_hero"
                                                    {{ $banner->position == 'home_hero' ? 'selected' : '' }}>Trang
                                                    chủ (Chính)</option>
                                                <option value="popup"
                                                    {{ $banner->position == 'popup' ? 'selected' : '' }}>Popup
                                                    quảng cáo</option>
                                                <option value="list_sidebar"
                                                    {{ $banner->position == 'list_sidebar' ? 'selected' : '' }}>Cột
                                                    bên (Sidebar)</option>
                                            </select>
                                        </div>

                                        <div class="row">
                                            <div class="col-6 mb-3">
                                                <label class="form-label fw-bold small">Trạng thái</label>
                                                <select name="is_active" class="form-select">
                                                    <option value="1"
                                                        {{ $banner->is_active == 1 ? 'selected' : '' }}>Hiển thị
                                                    </option>
                                                    <option value="0"
                                                        {{ $banner->is_active == 0 ? 'selected' : '' }}>Tạm ẩn
                                                    </option>
                                                </select>
                                            </div>
                                            <div class="col-6 mb-3">
                                                <label class="form-label fw-bold small">Thứ tự ưu tiên</label>
                                                <input type="number" name="priority" class="form-control"
                                                    value="{{ $banner->priority }}">
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-6 mb-3">
                                                <label class="form-label fw-bold small">Ngày bắt đầu</label>
                                                <input type="date"
                                                    name="start_date"
                                                    class="form-control start-date"
                                                    value="{{ \Carbon\Carbon::parse($banner->start_date)->format('Y-m-d') }}"
                                                    min="{{ date('Y-m-d') }}">
                                            </div>
                                            <div class="col-6 mb-3">
                                                <label class="form-label fw-bold small">Ngày kết thúc</label>
                                                <input type="date"
                                                    name="end_date"
                                                    class="form-control end-date"
                                                    value="{{ \Carbon\Carbon::parse($banner->end_date)->format('Y-m-d') }}"
                                                    min="{{ date('Y-m-d') }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary"
                                            data-bs-dismiss="modal">Hủy</button>
                                        <button type="submit" class="btn btn-primary px-4">Lưu cập nhật</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- MODAL ADD (Tách ra file hoặc để dưới đây) --}}
@include('admin.setting.modals_add')
@endsection

@section('scripts')
<script>
    // Hàm review ảnh trước khi upload
    function previewImage(input, previewId) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById(previewId).src = e.target.result;
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    // Tự động mở lại Modal nếu có lỗi validate
    @if($errors -> any())
    var myModal = new bootstrap.Modal(document.getElementById('addBannerModal'));
    myModal.show();
    @endif


    const addEnd = document.getElementById('add_end_date');
    if (addEnd) {
        const today = new Date().toISOString().split('T')[0];
        addEnd.min = today;
    }

    /* ========= EDIT BANNER ========= */
    document.querySelectorAll('.modal').forEach(modal => {
        const start = modal.querySelector('.start-date');
        const end = modal.querySelector('.end-date');
        if (!start || !end) return;

        const today = new Date().toISOString().split('T')[0];

        function syncMin() {
            // min = ngày lớn hơn giữa today và start_date
            const minDate = start.value > today ? start.value : today;
            end.min = minDate;

            if (end.value && end.value < minDate) {
                end.value = '';
            }
        }

        syncMin();

        start.addEventListener('change', syncMin);
    });
</script>
@endsection