@extends('app')

@section('content')
    <style>
        .combo-card {
            transition: all 0.3s ease;
            border: 1px solid #dee2e6;
            border-radius: 8px;
        }

        .combo-card.active {
            border-color: #0d6efd;
            background-color: #f8fbff;
            box-shadow: 0 4px 12px rgba(13, 110, 253, 0.08);
        }

        /* Tăng kích thước và khoảng cách cho switch */
        .form-switch .form-check-input {
            width: 2.8em;
            height: 1.4em;
            cursor: pointer;
            margin-top: 0.15rem;
        }

        /* Tạo khoảng cách cho label */
        .form-switch .form-check-label {
            padding-left: 0.8rem;
            cursor: pointer;
            line-height: 1.6;
        }

        .badge {
            font-weight: 500;
            padding: 0.5em 0.8em;
        }
    </style>

    <div class="container-fluid py-4 text-start">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold text-primary"><i class="fas fa-box-open me-2"></i>Quản lý Gói Quảng Cáo (Combo)</h5>
                <button type="button" class="btn btn-primary shadow-sm fw-bold" onclick="openCreateModal()">
                    <i class="fas fa-plus me-1"></i> Tạo gói mới
                </button>
            </div>
            <div class="card-body">
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4">
                        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th width="25%">Tên gói</th>
                                <th width="30%">Quyền lợi (Combo)</th>
                                <th>Giá bán</th>
                                <th>Thời hạn</th>
                                <th>Trạng thái</th>
                                <th class="text-end">Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($packages as $package)
                                <tr>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $package->name }}</div>
                                        <small class="text-muted">{{ Str::limit($package->description, 60) }}</small>
                                    </td>
                                    <td>
                                        @foreach ($package->items as $item)
                                            <span
                                                class="badge {{ $item->type == 'top_search' ? 'bg-primary' : ($item->type == 'featured' ? 'bg-warning text-dark' : 'bg-info text-dark') }} mb-1">
                                                <i
                                                    class="fas {{ $item->type == 'top_search' ? 'fa-arrow-up' : ($item->type == 'featured' ? 'fa-star' : 'fa-image') }} me-1"></i>
                                                {{ $item->type == 'top_search' ? 'Top' : ($item->type == 'featured' ? 'Nổi bật' : 'Banner') }}
                                            </span>
                                        @endforeach
                                    </td>
                                    <td class="text-danger fw-bold">{{ number_format($package->price) }}đ</td>
                                    <td><span class="badge border text-dark fw-normal">{{ $package->duration_days }}
                                            ngày</span></td>
                                    <td>
                                        {!! $package->is_active
                                            ? '<span class="badge bg-success-subtle text-success px-3 border border-success">Đang bán</span>'
                                            : '<span class="badge bg-light text-muted px-3 border">Dừng bán</span>' !!}
                                    </td>
                                    <td class="text-end text-nowrap">
                                        <button class="btn btn-sm btn-outline-primary border-0"
                                            onclick='openEditModal(@json($package))'>
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <form action="{{ route('admin.packages.destroy', $package->id) }}" method="POST"
                                            class="d-inline" onsubmit="return confirm('Xóa gói này?')">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger border-0"><i
                                                    class="fas fa-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL FORM -->
    <div class="modal fade" id="packageModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <form id="packageForm" method="POST" class="needs-validation" novalidate>
                @csrf
                <div id="methodField"></div>
                <div class="modal-content border-0 shadow">
                    <div class="modal-header border-bottom-0">
                        <h5 class="modal-title fw-bold text-primary" id="modalTitle">Thiết lập Gói Quảng Cáo</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body py-0">
                        <!-- I. THÔNG TIN CHUNG -->
                        <div class="bg-light p-3 rounded mb-4 shadow-sm">
                            <h6 class="fw-bold mb-3 mt-0 text-dark"><i
                                    class="fas fa-info-circle me-2 text-primary"></i>Thông tin cơ bản</h6>
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label fw-semibold">Tên gói quảng cáo <span
                                            class="text-danger">*</span></label>
                                    <input type="text" name="name" id="pkgName" class="form-control shadow-none"
                                        required placeholder="VD: Gói Siêu VIP">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Giá trọn gói (VNĐ) <span
                                            class="text-danger">*</span></label>
                                    <input type="number" name="price" id="pkgPrice" class="form-control shadow-none"
                                        required min="0" placeholder="0">
                                    <div id="priceFormatted" class="form-text text-primary fw-bold mt-1"></div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Thời hạn (Ngày) <span
                                            class="text-danger">*</span></label>
                                    <input type="number" name="duration_days" id="pkgDuration"
                                        class="form-control shadow-none" required min="1" value="7">
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold">Mô tả ngắn</label>
                                    <textarea name="description" id="pkgDesc" class="form-control shadow-none" rows="2"
                                        placeholder="Nội dung giới thiệu gói..."></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- II. CẤU HÌNH QUYỀN LỢI -->
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="fw-bold mb-0 text-dark px-1"><i class="fas fa-list-check me-2 text-primary"></i>Quyền
                                lợi đi kèm (Combo)</h6>
                            <small id="comboErr" class="text-danger fw-bold d-none">! Cần chọn ít nhất 1 quyền lợi</small>
                        </div>

                        <div class="row g-3">
                            <!-- Option A: Top Search -->
                            <div class="col-12">
                                <div class="card combo-card shadow-none" id="cardTop">
                                    <div class="card-body py-3">
                                        <div class="form-check form-switch d-flex align-items-center">
                                            <input class="form-check-input section-toggle" type="checkbox" name="types[]"
                                                value="top_search" id="chkTop" data-target="#areaTop">
                                            <label class="form-check-label fw-bold ms-3" for="chkTop">Đẩy Top Tìm
                                                Kiếm</label>
                                        </div>
                                        <div class="mt-3 ps-5 d-none border-start ms-4" id="areaTop">
                                            <div class="row align-items-center g-2">
                                                <div class="col-auto"><span class="small text-muted">Số điểm ưu
                                                        tiên:</span></div>
                                                <div class="col-md-4">
                                                    <input type="number" name="top_search_point" id="inpTopPoint"
                                                        class="form-control form-control-sm shadow-none"
                                                        placeholder="VD: 100">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Option B: Featured -->
                            <div class="col-12">
                                <div class="card combo-card shadow-none" id="cardFeatured">
                                    <div class="card-body py-3">
                                        <div class="form-check form-switch d-flex align-items-center">
                                            <input class="form-check-input section-toggle" type="checkbox" name="types[]"
                                                value="featured" id="chkFeatured" data-target="#areaFeatured">
                                            <label class="form-check-label fw-bold ms-3" for="chkFeatured">Sân Nổi Bật
                                                (Featured)</label>
                                        </div>
                                        <div class="mt-3 ps-5 d-none border-start ms-4" id="areaFeatured">
                                            <div class="row align-items-center g-2">
                                                <div class="col-auto"><span class="small text-muted">Vị trí hiển
                                                        thị:</span></div>
                                                <div class="col-md-6">
                                                    <select name="featured_section" id="inpFeaturedSection"
                                                        class="form-select form-select-sm shadow-none">
                                                        <option value="home_featured">Trang chủ - Mục Nổi bật</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Option C: Banner -->
                            <div class="col-12">
                                <div class="card combo-card shadow-none" id="cardBanner">
                                    <div class="card-body py-3">
                                        <div class="form-check form-switch d-flex align-items-center">
                                            <input class="form-check-input section-toggle" type="checkbox" name="types[]"
                                                value="banner" id="chkBanner" data-target="#areaBanner">
                                            <label class="form-check-label fw-bold ms-3" for="chkBanner">Banner Hình
                                                Ảnh</label>
                                        </div>
                                        <div class="mt-3 ps-5 d-none border-start ms-4" id="areaBanner">
                                            <div class="row align-items-center g-2">
                                                <div class="col-auto"><span class="small text-muted">Vị trí Banner:</span>
                                                </div>
                                                <div class="col-md-6">
                                                    <select name="banner_position" id="inpBannerPosition"
                                                        class="form-select form-select-sm shadow-none">
                                                        <option value="home_slider">Slider đầu trang</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Trạng thái bán -->
                        <div class="card border mt-4 shadow-none">
                            <div class="card-body py-3">
                                <div class="form-check form-switch d-flex align-items-center">
                                    <input class="form-check-input ms-0" type="checkbox" name="is_active" id="pkgActive"
                                        checked value="1">
                                    <label class="form-check-label fw-bold ms-3 text-dark" for="pkgActive">Cho phép người
                                        dùng mua gói này</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-top-0 p-3">
                        <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-primary px-4 fw-bold shadow-sm" id="btnSubmit">Lưu Thay
                            Đổi</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('packageForm');
            const toggles = document.querySelectorAll('.section-toggle');
            const priceInp = document.getElementById('pkgPrice');
            const priceTxt = document.getElementById('priceFormatted');

            toggles.forEach(toggle => {
                toggle.addEventListener('change', function() {
                    const targetId = this.getAttribute('data-target');
                    const targetDiv = document.querySelector(targetId);
                    const card = this.closest('.combo-card');
                    const inputs = targetDiv.querySelectorAll('input, select');

                    if (this.checked) {
                        targetDiv.classList.remove('d-none');
                        card.classList.add('active');
                        inputs.forEach(i => i.setAttribute('required', 'required'));
                    } else {
                        targetDiv.classList.add('d-none');
                        card.classList.remove('active');
                        inputs.forEach(i => {
                            i.removeAttribute('required');
                            i.value = '';
                        });
                    }
                });
            });

            priceInp.addEventListener('input', function() {
                const val = this.value;
                priceTxt.innerText = val ? new Intl.NumberFormat('vi-VN').format(val) + ' VNĐ' : '';
            });

            form.addEventListener('submit', function(e) {
                let isValid = true;
                const checked = document.querySelectorAll('.section-toggle:checked');
                const err = document.getElementById('comboErr');

                if (checked.length === 0) {
                    err.classList.remove('d-none');
                    isValid = false;
                } else {
                    err.classList.add('d-none');
                }

                if (!form.checkValidity() || !isValid) {
                    e.preventDefault();
                    e.stopPropagation();
                }
                form.classList.add('was-validated');
            });
        });

        function openCreateModal() {
            const form = document.getElementById('packageForm');
            form.reset();
            form.classList.remove('was-validated');
            document.getElementById('methodField').innerHTML = '';
            document.getElementById('modalTitle').innerText = 'Tạo Gói Combo Mới';
            document.getElementById('priceFormatted').innerText = '';
            document.getElementById('comboErr').classList.add('d-none');

            document.querySelectorAll('.section-toggle').forEach(el => {
                el.checked = false;
                el.dispatchEvent(new Event('change'));
            });

            form.action = "{{ route('admin.packages.store') }}";
            new bootstrap.Modal(document.getElementById('packageModal')).show();
        }

        function openEditModal(pkg) {
            const form = document.getElementById('packageForm');
            form.reset();
            form.classList.remove('was-validated');

            let url = "{{ route('admin.packages.update', ':id') }}".replace(':id', pkg.id);
            form.action = url;
            document.getElementById('methodField').innerHTML = '<input type="hidden" name="_method" value="PUT">';
            document.getElementById('modalTitle').innerText = 'Chỉnh sửa: ' + pkg.name;

            document.getElementById('pkgName').value = pkg.name;
            document.getElementById('pkgPrice').value = pkg.price;
            document.getElementById('pkgDuration').value = pkg.duration_days;
            document.getElementById('pkgDesc').value = pkg.description || '';
            document.getElementById('pkgActive').checked = pkg.is_active == 1;
            document.getElementById('pkgPrice').dispatchEvent(new Event('input'));

            document.querySelectorAll('.section-toggle').forEach(el => {
                el.checked = false;
                el.dispatchEvent(new Event('change'));
            });

            if (pkg.items) {
                pkg.items.forEach(item => {
                    if (item.type === 'top_search') {
                        document.getElementById('chkTop').checked = true;
                        document.getElementById('chkTop').dispatchEvent(new Event('change'));
                        document.getElementById('inpTopPoint').value = item.settings.point || '';
                    } else if (item.type === 'featured') {
                        document.getElementById('chkFeatured').checked = true;
                        document.getElementById('chkFeatured').dispatchEvent(new Event('change'));
                        document.getElementById('inpFeaturedSection').value = item.settings.section;
                    } else if (item.type === 'banner') {
                        document.getElementById('chkBanner').checked = true;
                        document.getElementById('chkBanner').dispatchEvent(new Event('change'));
                        document.getElementById('inpBannerPosition').value = item.settings.position;
                    }
                });
            }
            new bootstrap.Modal(document.getElementById('packageModal')).show();
        }
    </script>
@endsection
