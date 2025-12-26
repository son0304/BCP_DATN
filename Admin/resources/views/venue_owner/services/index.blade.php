@extends('app')

@section('content')
    <div class="container-fluid py-4">

        {{-- ==================== PHẦN THÔNG BÁO (MỚI THÊM) ==================== --}}
        {{-- 1. Thông báo Thành công --}}
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show shadow-sm border-start border-success border-4" role="alert">
                <div class="d-flex align-items-center">
                    <i class="fas fa-check-circle fa-lg me-3"></i>
                    <div>
                        <h6 class="fw-bold mb-0">Thành công!</h6>
                        <small>{{ session('success') }}</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        {{-- 2. Thông báo Lỗi từ Controller --}}
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show shadow-sm border-start border-danger border-4" role="alert">
                <div class="d-flex align-items-center">
                    <i class="fas fa-exclamation-circle fa-lg me-3"></i>
                    <div>
                        <h6 class="fw-bold mb-0">Đã xảy ra lỗi!</h6>
                        <small>{{ session('error') }}</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        {{-- 3. Thông báo Lỗi Validate Form (nhập thiếu, sai định dạng...) --}}
        @if ($errors->any())
            <div class="alert alert-warning alert-dismissible fade show shadow-sm border-start border-warning border-4" role="alert">
                <div class="d-flex">
                    <i class="fas fa-exclamation-triangle fa-lg me-3 mt-1"></i>
                    <div>
                        <h6 class="fw-bold mb-1">Vui lòng kiểm tra lại dữ liệu:</h6>
                        <ul class="mb-0 ps-3 small">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        {{-- ==================== KẾT THÚC PHẦN THÔNG BÁO ==================== --}}


        <!-- === HEADER & ACTIONS === -->
        <div class="d-flex justify-content-between align-items-center mb-4 bg-white p-3 rounded shadow-sm border">
            <div>
                <h4 class="fw-bold mb-0 text-primary">Quản lý Dịch vụ</h4>
                <p class="text-muted small mb-0">Quản lý kho hàng và giá bán trên toàn hệ thống sân.</p>
            </div>
            <button type="button" class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#modalAddService">
                <i class="fas fa-plus me-1"></i> Thêm Dịch vụ Mới
            </button>
        </div>

        <div class="row g-4">
            <!-- === SIDEBAR BỘ LỌC === -->
            <div class="col-12 col-md-3 col-xl-2">
                <div class="card shadow-sm border-0 sticky-top" style="top: 20px; z-index: 99;">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h6 class="mb-0 fw-bold small text-uppercase"><i class="fas fa-filter me-1"></i> Danh mục</h6>
                    </div>
                    <div class="list-group list-group-flush">
                        <a href="{{ route('owner.services.index') }}"
                            class="list-group-item list-group-item-action {{ !request('category_id') ? 'active fw-bold' : '' }}">
                            Tất cả
                        </a>
                        @foreach ($categories as $cat)
                            <a href="{{ route('owner.services.index', ['category_id' => $cat->id]) }}"
                                class="list-group-item list-group-item-action {{ request('category_id') == $cat->id ? 'active fw-bold' : '' }}">
                                {{ $cat->name }}
                            </a>
                        @endforeach
                    </div>
                    <div class="p-3 bg-light border-top">
                        <button class="btn btn-sm btn-outline-primary w-100 bg-white" data-bs-toggle="modal"
                            data-bs-target="#modalAddCategory">
                            <i class="fas fa-plus me-1"></i> Tạo Danh mục
                        </button>
                    </div>
                </div>
            </div>

            <!-- === TABLE DANH SÁCH === -->
            <div class="col-12 col-md-9 col-xl-10">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light text-secondary">
                                    <tr>
                                        <th class="ps-4">Sản phẩm</th>
                                        <th>Danh mục</th>
                                        <th>Đang bán tại</th>
                                        <th>Giá bán (VNĐ)</th>
                                        <th class="text-end pe-4">Hành động</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($services as $service)
                                        <tr>
                                            <!-- Cột 1: Tên & Ảnh -->
                                            <td class="ps-4">
                                                <div class="d-flex align-items-center">
                                                    <img src="{{ $service->images->first()
                                                        ? asset($service->images->first()->url)
                                                        : 'https://ui-avatars.com/api/?name=' . urlencode($service->name) . '&background=1ABC9C&color=fff' }}"
                                                        alt="{{ $service->name }}" class="rounded me-3 border"
                                                        width="48" height="48" style="object-fit: cover;">
                                                    <div>
                                                        <h6 class="mb-0 fw-bold">{{ $service->name }}</h6>
                                                        <small class="text-muted">
                                                            {{ $service->type == 'amenities' ? 'Tiện ích' : 'Đơn vị: ' . $service->unit }}
                                                        </small>
                                                    </div>
                                                </div>
                                            </td>

                                            <!-- Cột 2: Danh mục -->
                                            <td>
                                                <span class="badge bg-light text-dark border">
                                                    {{ $service->category->name ?? '---' }}
                                                </span>
                                            </td>

                                            <!-- Cột 3: Các sân đang bán -->
                                            <td>
                                                @if ($service->venues->count())
                                                    @foreach ($service->venues->take(3) as $v)
                                                        <span class="badge bg-info bg-opacity-10 text-dark border border-info mb-1"
                                                            title="Sân: {{ $v->name }}">
                                                            {{ Str::limit($v->name, 15) }}
                                                        </span> <br>
                                                    @endforeach
                                                    @if ($service->venues->count() > 3)
                                                        <small class="text-muted fst-italic">+{{ $service->venues->count() - 3 }} sân khác</small>
                                                    @endif
                                                @else
                                                    <span class="text-muted small">Chưa bán tại sân nào</span>
                                                @endif
                                            </td>

                                            <!-- Cột 4: Giá bán -->
                                            <td class="fw-bold text-success">
                                                @if ($service->type == 'amenities')
                                                    <span class="badge bg-success bg-opacity-10 text-white">Miễn phí</span>
                                                @else
                                                    @php
                                                        $minPrice = $service->venues->min('pivot.price');
                                                        $maxPrice = $service->venues->max('pivot.price');
                                                    @endphp

                                                    @if ($minPrice == $maxPrice)
                                                        {{ number_format($minPrice ?? 0, 0, ',', '.') }}
                                                    @else
                                                        {{ number_format($minPrice, 0, ',', '.') }} - {{ number_format($maxPrice, 0, ',', '.') }}
                                                    @endif
                                                @endif
                                            </td>

                                            <!-- Cột 5: Hành động -->
                                            <td class="text-end pe-4">
                                                {{-- NÚT EDIT: CHỨA TOÀN BỘ DATA --}}
                                                <button type="button" class="btn btn-sm btn-light text-primary border me-1"
                                                    title="Chỉnh sửa"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#modalEditService"
                                                    data-id="{{ $service->id }}"
                                                    data-name="{{ $service->name }}"
                                                    data-category-id="{{ $service->category_id }}"
                                                    data-type="{{ $service->type }}"
                                                    data-unit="{{ $service->unit }}"
                                                    data-description="{{ $service->description }}"
                                                    data-price="{{ $service->venues->max('pivot.price') ?? 0 }}"
                                                    data-status="{{ $service->status ?? 'active' }}"
                                                    data-image="{{ $service->images->first() ? asset($service->images->first()->url) : '' }}"
                                                    {{-- QUAN TRỌNG: Danh sách ID các sân đang áp dụng --}}
                                                    data-venue-ids="{{ $service->venues->pluck('id') }}">
                                                    <i class="fas fa-edit"></i>
                                                </button>

                                                {{-- Form Xóa --}}
                                                <form action="{{ route('owner.services.destroy', $service->id) }}"
                                                    method="POST" class="d-inline-block">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-light text-danger border"
                                                        title="Xóa"
                                                        onclick="return confirm('Bạn có chắc muốn xóa dịch vụ này? Hành động này không thể hoàn tác.');">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center py-5 text-muted">
                                                <i class="fas fa-box-open fa-3x mb-3 text-secondary opacity-50"></i><br>
                                                Chưa có dịch vụ nào trong danh mục này.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer bg-white border-top">
                        {{ $services->appends(request()->all())->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ==================== CÁC MODAL ==================== -->

    <!-- 1. MODAL THÊM DỊCH VỤ MỚI -->
    <div class="modal fade" id="modalAddService" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form action="{{ route('owner.services.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-content">
                    <div class="modal-header bg-light">
                        <h5 class="modal-title fw-bold text-primary"><i class="fas fa-plus-circle me-2"></i>Thêm Dịch vụ Mới</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body p-4">
                        <div class="row g-4">
                            <!-- Cột Trái -->
                            <div class="col-md-6 border-end">
                                <h6 class="text-muted fw-bold small text-uppercase mb-3">Thông tin sản phẩm</h6>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Tên dịch vụ <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control" required placeholder="Vd: Nước suối Aquafina">
                                </div>
                                <div class="row">
                                    <div class="col-6 mb-3">
                                        <label class="form-label fw-bold">Danh mục <span class="text-danger">*</span></label>
                                        <select name="category_id" class="form-select" required>
                                            <option value="">-- Chọn --</option>
                                            @foreach ($categories as $cat)
                                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-6 mb-3">
                                        <label class="form-label fw-bold">Loại hình <span class="text-danger">*</span></label>
                                        <select name="type" id="add_type" class="form-select" required>
                                            <option value="consumable" selected>Hàng hóa (Bán đứt)</option>
                                            <option value="service">Dịch vụ (Thuê/Nhân sự)</option>
                                            <option value="amenities">Tiện ích (Wifi/WC)</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Đơn vị tính <span class="text-danger">*</span></label>
                                    <input type="text" name="unit" class="form-control" required placeholder="Vd: Chai, Lượt, Giờ...">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Hình ảnh</label>
                                    <input type="file" name="image" class="form-control">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Mô tả</label>
                                    <textarea name="description" class="form-control" rows="2"></textarea>
                                </div>
                            </div>
                            <!-- Cột Phải -->
                            <div class="col-md-6">
                                <h6 class="text-success fw-bold small text-uppercase mb-3">Thiết lập bán hàng</h6>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Áp dụng cho sân nào? <span class="text-danger">*</span></label>
                                    <div class="card p-2 bg-light border-0" style="max-height: 150px; overflow-y: auto;">
                                        @if ($venues->count() > 0)
                                            <div class="form-check mb-1 border-bottom pb-1">
                                                <input class="form-check-input" type="checkbox" id="checkAllAdd">
                                                <label class="form-check-label fw-bold" for="checkAllAdd">Chọn tất cả</label>
                                            </div>
                                            @foreach ($venues as $venue)
                                                <div class="form-check">
                                                    <input class="form-check-input venue-checkbox-add" type="checkbox"
                                                        name="venue_ids[]" value="{{ $venue->id }}"
                                                        id="chk_add_{{ $venue->id }}" checked>
                                                    <label class="form-check-label" for="chk_add_{{ $venue->id }}">
                                                        {{ $venue->name }}
                                                    </label>
                                                </div>
                                            @endforeach
                                        @else
                                            <span class="text-danger small">Chưa có sân nào.</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold text-success">Giá bán mặc định (VNĐ)</label>
                                    <input type="number" name="price" id="add_price" class="form-control fw-bold text-success" required min="0" value="0">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Trạng thái</label>
                                    <select name="status" class="form-select">
                                        <option value="active">Đang bán</option>
                                        <option value="inactive">Tạm ngưng</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                        <button type="submit" class="btn btn-primary fw-bold px-4">Lưu Dịch vụ</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- 2. MODAL CẬP NHẬT DỊCH VỤ (Đã thêm phần chọn Sân) -->
    <div class="modal fade" id="modalEditService" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            {{-- KHÔNG truyền tham số vào route() ở đây. Action sẽ được JS điền vào --}}
            <form id="formEditService" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="modal-content">
                    <div class="modal-header bg-light">
                        <h5 class="modal-title fw-bold text-primary">
                            <i class="fas fa-edit me-2"></i>Cập nhật Dịch vụ
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body p-4">
                        <div class="row g-4">
                            <!-- Cột Trái (Thông tin sản phẩm) -->
                            <div class="col-md-6 border-end">
                                <h6 class="text-muted fw-bold small text-uppercase mb-3">Thông tin sản phẩm</h6>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Tên dịch vụ <span class="text-danger">*</span></label>
                                    <input type="text" name="name" id="edit_name" class="form-control" required>
                                </div>
                                <div class="row">
                                    <div class="col-6 mb-3">
                                        <label class="form-label fw-bold">Danh mục <span class="text-danger">*</span></label>
                                        <select name="category_id" id="edit_category_id" class="form-select" required>
                                            <option value="">-- Chọn --</option>
                                            @foreach ($categories as $cat)
                                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-6 mb-3">
                                        <label class="form-label fw-bold">Loại hình <span class="text-danger">*</span></label>
                                        <select name="type" id="edit_type" class="form-select" required>
                                            <option value="consumable">Hàng hóa</option>
                                            <option value="service">Dịch vụ</option>
                                            <option value="amenities">Tiện ích</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Đơn vị tính <span class="text-danger">*</span></label>
                                    <input type="text" name="unit" id="edit_unit" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Hình ảnh</label>
                                    <div class="d-flex align-items-center gap-3">
                                        <img id="edit_image_preview" src="" class="rounded border d-none" width="50" height="50" style="object-fit: cover;">
                                        <input type="file" name="image" class="form-control">
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Mô tả</label>
                                    <textarea name="description" id="edit_description" class="form-control" rows="2"></textarea>
                                </div>
                            </div>

                            <!-- Cột Phải (Thiết lập bán hàng) -->
                            <div class="col-md-6">
                                <h6 class="text-success fw-bold small text-uppercase mb-3">Thiết lập bán hàng</h6>

                                {{-- PHẦN CHỌN SÂN (Giống Modal Add) --}}
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Áp dụng cho sân nào? <span class="text-danger">*</span></label>
                                    <div class="card p-2 bg-light border-0" style="max-height: 150px; overflow-y: auto;">
                                        @if ($venues->count() > 0)
                                            <div class="form-check mb-1 border-bottom pb-1">
                                                <input class="form-check-input" type="checkbox" id="checkAllEdit">
                                                <label class="form-check-label fw-bold" for="checkAllEdit">Chọn tất cả</label>
                                            </div>
                                            @foreach ($venues as $venue)
                                                <div class="form-check">
                                                    {{-- ID phải khác Modal Add để tránh xung đột JS --}}
                                                    <input class="form-check-input venue-checkbox-edit" type="checkbox"
                                                        name="venue_ids[]" value="{{ $venue->id }}"
                                                        id="chk_edit_{{ $venue->id }}">
                                                    <label class="form-check-label" for="chk_edit_{{ $venue->id }}">
                                                        {{ $venue->name }}
                                                    </label>
                                                </div>
                                            @endforeach
                                        @else
                                            <span class="text-danger small">Chưa có sân nào.</span>
                                        @endif
                                    </div>
                                </div>

                                <div class="alert alert-info small py-2">
                                    <i class="fas fa-info-circle me-1"></i> Giá này sẽ cập nhật cho tất cả sân được chọn. <strong>Tồn kho sẽ được giữ nguyên</strong>.
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold text-success">Giá cập nhật (VNĐ)</label>
                                    <input type="number" name="price" id="edit_price"
                                        class="form-control fw-bold text-success" required min="0" step="1">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold">Trạng thái</label>
                                    <select name="status" id="edit_status" class="form-select">
                                        <option value="active">Đang bán</option>
                                        <option value="inactive">Tạm ngưng</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                        <button type="submit" class="btn btn-primary fw-bold px-4">Lưu thay đổi</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- 3. MODAL THÊM DANH MỤC -->
    <div class="modal fade" id="modalAddCategory" tabindex="-1">
        <div class="modal-dialog">
            <form action="{{ route('owner.services.categories.store') }}" method="POST">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Thêm Danh mục Mới</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Tên danh mục <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" required placeholder="Vd: Nước giải khát">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Lưu Danh mục</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- ==================== SCRIPTS XỬ LÝ ==================== -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            // --- 1. Logic Modal ADD ---
            var addTypeSelect = document.getElementById('add_type');
            var addPriceInput = document.getElementById('add_price');
            if (addTypeSelect) {
                addTypeSelect.addEventListener('change', function() { togglePriceInput(this.value, addPriceInput); });
            }
            var checkAllAdd = document.getElementById('checkAllAdd');
            if (checkAllAdd) {
                checkAllAdd.addEventListener('change', function() {
                    document.querySelectorAll('.venue-checkbox-add').forEach(chk => chk.checked = this.checked);
                });
            }


            // --- 2. Logic Modal EDIT (QUAN TRỌNG) ---
            var modalEditService = document.getElementById('modalEditService');
            if (modalEditService) {
                modalEditService.addEventListener('show.bs.modal', function(event) {
                    var button = event.relatedTarget; // Nút được bấm

                    // Lấy dữ liệu từ data attributes
                    var id = button.getAttribute('data-id');
                    var name = button.getAttribute('data-name');
                    var categoryId = button.getAttribute('data-category-id');
                    var type = button.getAttribute('data-type');
                    var unit = button.getAttribute('data-unit');
                    var description = button.getAttribute('data-description');
                    var price = button.getAttribute('data-price');
                    var status = button.getAttribute('data-status');
                    var imageUrl = button.getAttribute('data-image');
                    // Lấy mảng Venue IDs (dạng JSON array: [1, 2])
                    var venueIds = JSON.parse(button.getAttribute('data-venue-ids'));

                    // 1. Set Action Form (Dynamic Route)
                    var form = document.getElementById('formEditService');
                    form.action = '/owner/services/' + id;

                    // 2. Điền dữ liệu vào input
                    document.getElementById('edit_name').value = name;
                    document.getElementById('edit_category_id').value = categoryId;
                    document.getElementById('edit_type').value = type;
                    document.getElementById('edit_unit').value = unit;
                    document.getElementById('edit_description').value = description;
                    document.getElementById('edit_status').value = status;
                    document.getElementById('edit_price').value = Math.floor(parseFloat(price));

                    // 3. Hiển thị ảnh
                    var imgPreview = document.getElementById('edit_image_preview');
                    if (imageUrl) {
                        imgPreview.src = imageUrl;
                        imgPreview.classList.remove('d-none');
                    } else {
                        imgPreview.classList.add('d-none');
                    }

                    // 4. CHECK CÁC SÂN ĐANG BÁN
                    // Reset: Bỏ check tất cả trước
                    var editCheckboxes = document.querySelectorAll('.venue-checkbox-edit');
                    editCheckboxes.forEach(chk => chk.checked = false);

                    // Loop qua venueIds và check các ô tương ứng
                    if (Array.isArray(venueIds)) {
                        venueIds.forEach(function(vId) {
                            var chk = document.getElementById('chk_edit_' + vId);
                            if (chk) chk.checked = true;
                        });
                    }

                    // Reset nút Check All
                    var checkAllEdit = document.getElementById('checkAllEdit');
                    if(checkAllEdit) checkAllEdit.checked = false;

                    // 5. Xử lý khóa/mở ô giá theo Type
                    togglePriceInput(type, document.getElementById('edit_price'));
                });

                // Sự kiện Check All trong Edit Modal
                var checkAllEdit = document.getElementById('checkAllEdit');
                if (checkAllEdit) {
                    checkAllEdit.addEventListener('change', function() {
                        document.querySelectorAll('.venue-checkbox-edit').forEach(chk => chk.checked = this.checked);
                    });
                }

                // Sự kiện thay đổi Type trong Edit Modal
                document.getElementById('edit_type').addEventListener('change', function() {
                    togglePriceInput(this.value, document.getElementById('edit_price'));
                });
            }

            // Hàm dùng chung: Khóa/Mở ô giá
            function togglePriceInput(type, inputElement) {
                if (type === 'amenities') {
                    inputElement.value = 0;
                    inputElement.readOnly = true;
                    inputElement.classList.add('bg-light', 'text-muted');
                    inputElement.classList.remove('fw-bold', 'text-success');
                } else {
                    inputElement.readOnly = false;
                    inputElement.classList.remove('bg-light', 'text-muted');
                    inputElement.classList.add('fw-bold', 'text-success');
                }
            }
        });
    </script>
@endsection
