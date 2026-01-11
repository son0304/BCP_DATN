@extends('app')

@section('content')
    <div class="container-fluid py-4">
        <!-- TIÊU ĐỀ TRANG -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="h4 mb-1 fw-bold text-dark">Cấu hình Giao diện & Quảng cáo</h2>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0" style="background: transparent; padding: 0;">
                        <li class="breadcrumb-item"><a href="#">Admin</a></li>
                        <li class="breadcrumb-item active">Cài đặt Website</li>
                    </ol>
                </nav>
            </div>
            <div class="text-end">
                <div class="badge bg-white text-dark shadow-sm p-2 px-3 border">
                    <i class="far fa-calendar-alt me-1 text-success"></i> {{ now()->format('d/m/Y') }}
                </div>
            </div>
        </div>

        <!-- THÔNG BÁO -->
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
                <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- TABS ĐIỀU HƯỚNG -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white pt-3 border-bottom-0">
                <ul class="nav nav-tabs card-header-tabs" id="settingTab" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link active fw-bold" data-bs-toggle="tab" data-bs-target="#tab-banner"
                            type="button">
                            <i class="fas fa-images me-2 text-primary"></i>Quản lý Banners
                        </button>
                    </li>
                </ul>
            </div>

            <div class="card-body p-4">
                <div class="tab-content">

                    <!-- TAB 1: QUẢN LÝ BANNER -->
                    <div class="tab-pane fade show active" id="tab-banner">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0 fw-bold">Danh sách Banner Slider</h5>
                            <button class="btn btn-success btn-sm px-3 shadow-sm" data-bs-toggle="modal"
                                data-bs-target="#addBannerModal">
                                <i class="fas fa-plus me-1"></i> Thêm mới
                            </button>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover align-middle border">
                                <thead class="table-light">
                                    <tr>
                                        <th class="text-center" style="width: 160px;">Ảnh</th>
                                        <th>Tiêu đề & Link</th>
                                        <th class="text-center">Vị trí</th>
                                        <th class="text-center">Trạng thái</th>
                                        <th class="text-center">Hành động</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($banners as $banner)
                                        @php
                                            // Lấy ảnh đầu tiên từ collection images
                                            $firstImage = $banner->images->first();
                                            $displayUrl = 'https://placehold.co/600x300?text=No+Image';

                                            if ($firstImage) {
                                                // Kiểm tra nếu url đã là đường dẫn tuyệt đối (http...) thì dùng luôn,
                                                // nếu không thì mới nối với asset('storage/')
                                                $displayUrl = str_starts_with($firstImage->url, 'http')
                                                    ? $firstImage->url
                                                    : asset('storage/' . $firstImage->url);
                                            }
                                        @endphp
                                        <tr>
                                            <td class="text-center">
                                                <img src="{{ $displayUrl }}" class="rounded shadow-sm border"
                                                    style="width: 130px; height: 65px; object-fit: cover;"
                                                    onerror="this.src='https://placehold.co/600x300?text=Error+Image'">
                                            </td>
                                            <td>
                                                <div class="fw-bold text-dark">{{ $banner->title ?? 'N/A' }}</div>
                                                <div class="small text-muted">
                                                    <i
                                                        class="fas fa-link me-1"></i>{{ $banner->target_url ?? 'Không có link' }}
                                                </div>
                                                <div class="small text-muted" style="font-size: 0.75rem;">
                                                    <i class="far fa-clock me-1"></i>
                                                    {{ $banner->start_date ? $banner->start_date->format('d/m/Y') : 'N/A' }}
                                                    -
                                                    {{ $banner->end_date ? $banner->end_date->format('d/m/Y') : 'N/A' }}
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-light text-dark border px-2 py-1">
                                                    @switch($banner->position)
                                                        @case('home_hero')
                                                            Trang chủ
                                                        @break

                                                        @case('popup')
                                                            Popup
                                                        @break

                                                        @case('list_sidebar')
                                                            Sidebar
                                                        @break

                                                        @default
                                                            {{ $banner->position }}
                                                    @endswitch
                                                </span>
                                                <div class="small text-muted mt-1">Ưu tiên: {{ $banner->priority }}</div>
                                            </td>
                                            <td class="text-center">
                                                <div class="form-check form-switch d-inline-block">
                                                    <input class="form-check-input toggle-status" type="checkbox"
                                                        data-type="banner" data-id="{{ $banner->id }}"
                                                        {{ $banner->is_active ? 'checked' : '' }}>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group shadow-sm">
                                                    <button class="btn btn-white btn-sm border" data-bs-toggle="modal"
                                                        data-bs-target="#editBanner{{ $banner->id }}" title="Chỉnh sửa">
                                                        <i class="fas fa-edit text-info"></i>
                                                    </button>
                                                    <form
                                                        action="{{ route('admin.settings.banners.destroy', $banner->id) }}"
                                                        method="POST" class="d-inline">
                                                        @csrf @method('DELETE')
                                                        <button class="btn btn-white btn-sm border"
                                                            onclick="return confirm('Bạn có chắc muốn xóa banner này?')"
                                                            title="Xóa">
                                                            <i class="fas fa-trash text-danger"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>

                                        <!-- MODAL SỬA BANNER (Trong vòng lặp) -->
                                        <div class="modal fade" id="editBanner{{ $banner->id }}" tabindex="-1">
                                            <div class="modal-dialog">
                                                <form action="{{ route('admin.settings.banners.update', $banner->id) }}"
                                                    method="POST" enctype="multipart/form-data">
                                                    @csrf @method('PUT')
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Chỉnh sửa Banner #{{ $banner->id }}
                                                            </h5>
                                                            <button type="button" class="btn-close"
                                                                data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="mb-3">
                                                                <label class="form-label fw-bold">Ảnh hiện tại</label>
                                                                <div class="text-center border p-2 mb-2 rounded bg-light">
                                                                    <img src="{{ $displayUrl }}"
                                                                        class="rounded shadow-sm"
                                                                        style="max-height: 150px; max-width: 100%;">
                                                                </div>
                                                                <label class="form-label fw-bold">Thay đổi ảnh (Nếu
                                                                    muốn)</label>
                                                                <input type="file" name="image"
                                                                    class="form-control">
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label fw-bold">Tiêu đề</label>
                                                                <input type="text" name="title" class="form-control"
                                                                    value="{{ $banner->title }}">
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label fw-bold">Link đích</label>
                                                                <input type="text" name="target_url"
                                                                    class="form-control"
                                                                    value="{{ $banner->target_url }}"
                                                                    placeholder="/vi-du-link">
                                                            </div>
                                                            <div class="row">
                                                                <div class="col-6">
                                                                    <label class="form-label fw-bold">Vị trí</label>
                                                                    <select name="position" class="form-select">
                                                                        <option value="home_hero"
                                                                            {{ $banner->position == 'home_hero' ? 'selected' : '' }}>
                                                                            Trang chủ</option>
                                                                        <option value="popup"
                                                                            {{ $banner->position == 'popup' ? 'selected' : '' }}>
                                                                            Popup</option>
                                                                        <option value="list_sidebar"
                                                                            {{ $banner->position == 'list_sidebar' ? 'selected' : '' }}>
                                                                            Sidebar</option>
                                                                    </select>
                                                                </div>
                                                                <div class="col-6">
                                                                    <label class="form-label fw-bold">Thứ tự ưu
                                                                        tiên</label>
                                                                    <input type="number" name="priority"
                                                                        class="form-control"
                                                                        value="{{ $banner->priority }}">
                                                                </div>
                                                            </div>
                                                            <div class="row mt-3">
                                                                <div class="col-6">
                                                                    <label class="form-label fw-bold">Ngày bắt đầu</label>
                                                                    <input type="date" name="start_date"
                                                                        class="form-control"
                                                                        value="{{ $banner->start_date ? $banner->start_date->format('Y-m-d') : '' }}">
                                                                </div>
                                                                <div class="col-6">
                                                                    <label class="form-label fw-bold">Ngày kết thúc</label>
                                                                    <input type="date" name="end_date"
                                                                        class="form-control"
                                                                        value="{{ $banner->end_date ? $banner->end_date->format('Y-m-d') : '' }}">
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary"
                                                                data-bs-dismiss="modal">Đóng</button>
                                                            <button type="submit" class="btn btn-primary">Cập nhật
                                                                ngay</button>
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
            </div>
        </div>
    </div>

    <!-- INCLUDE MODALS THÊM MỚI -->
    @include('admin.setting.modals_add')
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            // Cấu hình AJAX CSRF
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // Xử lý nút gạt Bật/Tắt trạng thái
            $('.toggle-status').on('change', function() {
                let type = $(this).data('type');
                let id = $(this).data('id');
                let url =
                    "{{ route('admin.settings.toggle-status', ['type' => ':type', 'id' => ':id']) }}";
                url = url.replace(':type', type).replace(':id', id);

                $.ajax({
                    url: url,
                    method: "POST",
                    success: function(res) {
                        console.log("Updated status: " + res.is_active);
                        // Có thể thêm Toast thông báo tại đây
                    },
                    error: function() {
                        alert("Lỗi kết nối máy chủ!");
                    }
                });
            });
        });
    </script>
@endsection
