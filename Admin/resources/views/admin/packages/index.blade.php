@extends('app')

@section('content')
    <div class="container-fluid py-4">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold text-primary"><i class="fas fa-box-open me-2"></i>Quản lý Gói Quảng Cáo (Combo)</h5>
                <button type="button" class="btn btn-primary btn-sm shadow-sm" onclick="openCreateModal()">
                    <i class="fas fa-plus me-1"></i> Tạo gói mới
                </button>
            </div>
            <div class="card-body">
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Tên gói</th>
                                <th>Quyền lợi (Combo)</th>
                                <th>Giá (VNĐ)</th>
                                <th>Thời hạn</th>
                                <th>Trạng thái</th>
                                <th class="text-end">Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($packages as $package)
                                <tr>
                                    <td>
                                        <div class="fw-bold">{{ $package->name }}</div>
                                        <small class="text-muted">{{ $package->description }}</small>
                                    </td>
                                    <td>
                                        {{-- Hiển thị các Badge quyền lợi --}}
                                        @foreach ($package->items as $item)
                                            @if ($item->type == 'top_search')
                                                <div class="badge bg-primary mb-1">
                                                    <i class="fas fa-arrow-up"></i> Top
                                                    ({{ $item->settings['point'] ?? 0 }}đ)
                                                </div><br>
                                            @elseif($item->type == 'featured')
                                                <div class="badge bg-warning text-dark mb-1">
                                                    <i class="fas fa-star"></i> Featured
                                                    ({{ $item->settings['section'] ?? '' }})
                                                </div><br>
                                            @elseif($item->type == 'banner')
                                                <div class="badge bg-info text-dark mb-1">
                                                    <i class="fas fa-image"></i> Banner
                                                    ({{ $item->settings['position'] ?? '' }})
                                                </div><br>
                                            @endif
                                        @endforeach
                                    </td>
                                    <td class="text-danger fw-bold">{{ number_format($package->price) }}đ</td>
                                    <td>{{ $package->duration_days }} ngày</td>
                                    <td>
                                        {!! $package->is_active
                                            ? '<span class="badge bg-success">Đang bán</span>'
                                            : '<span class="badge bg-secondary">Dừng bán</span>' !!}
                                    </td>
                                    <td class="text-end">
                                        {{-- Truyền dữ liệu JSON vào nút Sửa để JS đọc --}}
                                        <button class="btn btn-sm btn-outline-primary"
                                            onclick='openEditModal(@json($package))'>
                                            <i class="fas fa-edit"></i>
                                        </button>

                                        <form action="{{ route('admin.packages.destroy', $package->id) }}" method="POST"
                                            class="d-inline" onsubmit="return confirm('Xóa gói này?')">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger"><i
                                                    class="fas fa-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div> <!-- End Table Responsive -->

                {{-- Phân trang nếu có --}}
                {{-- <div class="mt-3">{{ $packages->links() }}</div> --}}

            </div>
        </div>
    </div>

    <!-- ========================================================= -->
    <!-- SHARED MODAL (DÙNG CHUNG CHO CREATE VÀ EDIT) -->
    <!-- ========================================================= -->
    <div class="modal fade" id="packageModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form id="packageForm" method="POST">
                @csrf
                {{-- Div này dùng để chứa input method PUT khi Edit --}}
                <div id="methodField"></div>

                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalTitle">Tạo Gói Combo Mới</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">

                        <!-- 1. THÔNG TIN CHUNG -->
                        <h6 class="fw-bold text-primary mb-3">I. Thông tin cơ bản</h6>
                        <div class="row mb-3">
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Tên gói quảng cáo</label>
                                <input type="text" name="name" id="pkgName" class="form-control"
                                    placeholder="VD: Gói Siêu VIP (Combo)" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Giá trọn gói (VNĐ)</label>
                                <input type="number" name="price" id="pkgPrice" class="form-control" placeholder="0"
                                    required min="0">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Thời hạn (Ngày)</label>
                                <input type="number" name="duration_days" id="pkgDuration" class="form-control"
                                    value="7" required min="1">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Mô tả ngắn</label>
                                <textarea name="description" id="pkgDesc" class="form-control" rows="2"></textarea>
                            </div>
                        </div>

                        <hr>

                        <!-- 2. CẤU HÌNH COMBO -->
                        <h6 class="fw-bold text-primary mb-3">II. Chọn quyền lợi trong gói (Combo)</h6>
                        <p class="text-muted small">Tích chọn các loại quảng cáo sẽ có trong gói này.</p>

                        <!-- Option A: Top Search -->
                        <div class="card mb-3 border-primary bg-light">
                            <div class="card-body py-2">
                                <div class="form-check form-switch">
                                    <input class="form-check-input section-toggle" type="checkbox" name="types[]"
                                        value="top_search" id="chkTop" data-target="#areaTop">
                                    <label class="form-check-label fw-bold" for="chkTop">
                                        <i class="fas fa-arrow-up text-primary me-1"></i> Đẩy Top Tìm Kiếm
                                    </label>
                                </div>
                                <!-- Khu vực nhập liệu (Ẩn hiện theo checkbox) -->
                                <div class="mt-2 ps-4 d-none" id="areaTop">
                                    <div class="row align-items-center">
                                        <div class="col-auto"><label class="col-form-label text-sm">Điểm ưu tiên:</label>
                                        </div>
                                        <div class="col">
                                            <input type="number" name="top_search_point" id="inpTopPoint"
                                                class="form-control form-control-sm" placeholder="VD: 50">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Option B: Featured -->
                        <div class="card mb-3 border-warning bg-light">
                            <div class="card-body py-2">
                                <div class="form-check form-switch">
                                    <input class="form-check-input section-toggle" type="checkbox" name="types[]"
                                        value="featured" id="chkFeatured" data-target="#areaFeatured">
                                    <label class="form-check-label fw-bold" for="chkFeatured">
                                        <i class="fas fa-star text-warning me-1"></i> Sân Nổi Bật (Featured)
                                    </label>
                                </div>
                                <div class="mt-2 ps-4 d-none" id="areaFeatured">
                                    <div class="row align-items-center">
                                        <div class="col-auto"><label class="col-form-label text-sm">Vị trí
                                                (Section):</label></div>
                                        <div class="col">
                                            <select name="featured_section" id="inpFeaturedSection"
                                                class="form-select form-select-sm">
                                                <option value="home_featured">Trang chủ - Mục Nổi bật</option>
                                                <option value="sidebar_hot">Sidebar - Mục Hot</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Option C: Banner -->
                        <div class="card mb-3 border-info bg-light">
                            <div class="card-body py-2">
                                <div class="form-check form-switch">
                                    <input class="form-check-input section-toggle" type="checkbox" name="types[]"
                                        value="banner" id="chkBanner" data-target="#areaBanner">
                                    <label class="form-check-label fw-bold" for="chkBanner">
                                        <i class="fas fa-image text-info me-1"></i> Banner Hình Ảnh
                                    </label>
                                </div>
                                <div class="mt-2 ps-4 d-none" id="areaBanner">
                                    <div class="row align-items-center">
                                        <div class="col-auto"><label class="col-form-label text-sm">Vị trí hiển
                                                thị:</label></div>
                                        <div class="col">
                                            <select name="banner_position" id="inpBannerPosition"
                                                class="form-select form-select-sm">
                                                <option value="home_slider">Slider đầu trang</option>
                                                <option value="footer_banner">Banner chân trang</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Kích hoạt -->
                        <div class="form-check form-switch mt-3">
                            <input class="form-check-input" type="checkbox" name="is_active" id="pkgActive" checked>
                            <label class="form-check-label">Đang bán (Active)</label>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                        <button type="submit" class="btn btn-primary" id="btnSubmit">Lưu Gói</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- JAVASCRIPT XỬ LÝ --}}
    <script>
        // 1. Logic ẩn hiện Input khi check vào Checkbox
        document.addEventListener('DOMContentLoaded', function() {
            const toggles = document.querySelectorAll('.section-toggle');
            toggles.forEach(toggle => {
                toggle.addEventListener('change', function() {
                    const targetId = this.getAttribute('data-target');
                    const targetDiv = document.querySelector(targetId);
                    if (this.checked) {
                        targetDiv.classList.remove('d-none');
                    } else {
                        targetDiv.classList.add('d-none');
                    }
                });
            });
        });

        // 2. Hàm Mở Modal TẠO MỚI
        function openCreateModal() {
            // Reset form
            document.getElementById('packageForm').reset();
            document.getElementById('methodField').innerHTML = ''; // Xóa method PUT
            document.getElementById('modalTitle').innerText = 'Tạo Gói Combo Mới';
            document.getElementById('btnSubmit').innerText = 'Lưu Gói';

            // Reset ẩn hiện các khu vực nhập liệu
            document.querySelectorAll('.section-toggle').forEach(el => {
                el.checked = false;
                el.dispatchEvent(new Event('change')); // Trigger event để ẩn div
            });

            // Set Action URL
            document.getElementById('packageForm').action = "{{ route('admin.packages.store') }}";

            // Mở Modal
            var myModal = new bootstrap.Modal(document.getElementById('packageModal'));
            myModal.show();
        }

        // 3. Hàm Mở Modal CHỈNH SỬA (Đổ dữ liệu)
        function openEditModal(packageData) {
            // Reset form trước
            document.getElementById('packageForm').reset();

            // Cập nhật Action URL và Method PUT
            let url = "{{ route('admin.packages.update', ':id') }}";
            url = url.replace(':id', packageData.id);
            document.getElementById('packageForm').action = url;
            document.getElementById('methodField').innerHTML = '<input type="hidden" name="_method" value="PUT">';

            // Cập nhật Title
            document.getElementById('modalTitle').innerText = 'Cập Nhật Gói: ' + packageData.name;
            document.getElementById('btnSubmit').innerText = 'Cập Nhật';

            // Đổ dữ liệu cơ bản
            document.getElementById('pkgName').value = packageData.name;
            document.getElementById('pkgPrice').value = packageData.price;
            document.getElementById('pkgDuration').value = packageData.duration_days;
            document.getElementById('pkgDesc').value = packageData.description;
            document.getElementById('pkgActive').checked = packageData.is_active == 1;

            // Reset checkbox trước khi fill
            document.querySelectorAll('.section-toggle').forEach(el => {
                el.checked = false;
                el.dispatchEvent(new Event('change'));
            });

            // Đổ dữ liệu ITEMS (Combo)
            // packageData.items được truyền từ Controller qua JSON
            if (packageData.items && packageData.items.length > 0) {
                packageData.items.forEach(item => {
                    if (item.type === 'top_search') {
                        const chk = document.getElementById('chkTop');
                        chk.checked = true;
                        chk.dispatchEvent(new Event('change')); // Hiện input
                        document.getElementById('inpTopPoint').value = item.settings.point || '';
                    } else if (item.type === 'featured') {
                        const chk = document.getElementById('chkFeatured');
                        chk.checked = true;
                        chk.dispatchEvent(new Event('change'));
                        document.getElementById('inpFeaturedSection').value = item.settings.section ||
                            'home_featured';
                    } else if (item.type === 'banner') {
                        const chk = document.getElementById('chkBanner');
                        chk.checked = true;
                        chk.dispatchEvent(new Event('change'));
                        document.getElementById('inpBannerPosition').value = item.settings.position ||
                            'home_slider';
                    }
                });
            }

            // Mở Modal
            var myModal = new bootstrap.Modal(document.getElementById('packageModal'));
            myModal.show();
        }
    </script>
@endsection
