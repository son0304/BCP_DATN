@extends('app')

@section('content')
    <div class="container-fluid py-4">

        {{-- Header: Tiêu đề & Nút quay lại --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h4 mb-0 fw-bold">Chi tiết Sân bãi</h1>
                <p class="text-muted mb-0">Quản lý thông tin: <span class="text-primary">{{ $venue->name }}</span></p>
            </div>
            <div>
                <a href="{{ route('owner.venues.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Danh sách sân
                </a>
            </div>
        </div>

        <div class="row g-4">
            {{-- === CỘT CHÍNH (BÊN TRÁI) === --}}
            <div class="col-lg-8">
                {{-- Thông tin cơ bản --}}
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h5 class="mb-0 text-primary fw-bold"><i class="fas fa-info-circle me-2"></i>Thông tin cơ bản</h5>
                    </div>
                    <div class="card-body">
                        <dl class="row mb-0">
                            <dt class="col-sm-3 text-muted">Tên thương hiệu:</dt>
                            <dd class="col-sm-9 fw-bold">{{ $venue->name }}</dd>
                            <dt class="col-sm-3 text-muted">Chủ sở hữu:</dt>
                            <dd class="col-sm-9">{{ $venue->owner->name ?? 'N/A' }}</dd>
                            <dt class="col-sm-3 text-muted">Địa chỉ:</dt>
                            <dd class="col-sm-9">{{ $venue->address_detail }}</dd>
                            <dt class="col-sm-3 text-muted">Giờ hoạt động:</dt>
                            <dd class="col-sm-9">
                                <span class="badge bg-light text-dark border">
                                    {{ \Carbon\Carbon::parse($venue->start_time)->format('H:i') }} -
                                    {{ \Carbon\Carbon::parse($venue->end_time)->format('H:i') }}
                                </span>
                            </dd>
                            <dt class="col-sm-3 text-muted">Số điện thoại:</dt>
                            <dd class="col-sm-9">{{ $venue->phone ?? 'Chưa cập nhật' }}</dd>
                            <dt class="col-sm-3 text-muted">Trạng thái:</dt>
                            <dd class="col-sm-9">
                                @if ($venue->is_active)
                                    <span class="badge bg-success-subtle text-success border border-success">Đang hoạt
                                        động</span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary border border-secondary">Tạm
                                        dừng</span>
                                @endif
                            </dd>
                        </dl>
                    </div>
                </div>

                {{-- Danh sách sân con --}}
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom">
                        <h5 class="mb-0 text-primary fw-bold"><i class="fas fa-layer-group me-2"></i>Danh sách sân con
                            ({{ $venue->courts->count() }})</h5>
                        <a href="{{ route('owner.venues.courts.create', $venue->id) }}" class="btn btn-sm btn-success">
                            <i class="fas fa-plus me-1"></i> Tạo sân con
                        </a>
                    </div>
                    <div class="card-body p-0">
                        @if ($venue->courts->count())
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="bg-light text-secondary">
                                        <tr>
                                            <th class="ps-4">Tên sân</th>
                                            <th>Loại hình</th>
                                            <th>Giá (đ/giờ)</th>
                                            <th class="text-center">Thiết kế</th>
                                            <th class="text-end pe-4">Chi tiết</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($venue->courts as $court)
                                            <tr>
                                                <td class="ps-4 fw-bold">{{ $court->name }}</td>
                                                <td>{{ $court->venueType->name ?? 'N/A' }}</td>
                                                <td class="text-success fw-bold">
                                                    {{ number_format($court->price_per_hour, 0, ',', '.') }}</td>
                                                <td class="text-center">
                                                    @if ($court->is_indoor)
                                                        <span
                                                            class="badge bg-info bg-opacity-10 text-info border border-info">Trong
                                                            nhà</span>
                                                    @else
                                                        <span
                                                            class="badge bg-warning bg-opacity-10 text-warning border border-warning">Ngoài
                                                            trời</span>
                                                    @endif
                                                </td>
                                                <td class="text-end pe-4">
                                                    <a href="{{ route('owner.venues.courts.show', ['venue' => $venue->id, 'court' => $court->id]) }}"
                                                        class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-calendar-alt me-1"></i> Lịch
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-5 text-muted">
                                <i class="fas fa-inbox fa-3x mb-3 text-secondary opacity-50"></i><br>
                                Chưa có sân con nào được tạo.
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- === CỘT BÊN PHẢI === --}}
            <div class="col-lg-4">
                {{-- Hành động nhanh --}}
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-body">
                        <h6 class="fw-bold mb-3 text-uppercase small text-muted">Thao tác nhanh</h6>
                        <div class="d-grid gap-2">
                            <a href="{{ route('owner.venues.edit', $venue) }}" class="btn btn-warning text-white fw-bold">
                                <i class="fas fa-edit me-1"></i> Cập nhật thông tin
                            </a>
                        </div>
                    </div>
                </div>

                {{-- Dịch vụ --}}
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom">
                        <h5 class="mb-0 text-primary fw-bold"><i class="fas fa-cocktail me-2"></i>Dịch vụ</h5>
                        <a href="{{ route('owner.services.index') }}" class="btn btn-sm btn-outline-primary"
                            title="Quản lý dịch vụ chung">
                            <i class="fas fa-cog"></i>
                        </a>
                    </div>
                    <div class="card-body p-0">
                        @if ($venue->services->count())
                            <div class="list-group list-group-flush">
                                @foreach ($venue->services as $service)
                                    <div class="list-group-item d-flex align-items-center p-3">
                                        <img src="{{ $service->images->first() ? asset($service->images->first()->url) : 'https://ui-avatars.com/api/?name=' . urlencode($service->name) . '&background=1ABC9C&color=fff' }}"
                                            class="rounded me-3 border" width="40" height="40"
                                            style="object-fit: cover;" alt="{{ $service->name }}">

                                        <div class="flex-grow-1">
                                            <h6 class="mb-0 text-dark small fw-bold">{{ $service->name }}</h6>
                                            <small class="text-muted" style="font-size: 11px;">
                                                {{ $service->type == 'amenities' ? 'Tiện ích' : $service->unit }}
                                            </small>
                                        </div>

                                        <div class="text-end me-3">
                                            <div class="fw-bold text-success small">
                                                {{ number_format($service->pivot->price ?? 0, 0, ',', '.') }}đ
                                            </div>
                                            @if ($service->type == 'amenities')
                                                <small
                                                    class="{{ $service->pivot->stock == 1 ? 'text-primary' : 'text-danger' }}"
                                                    style="font-size: 11px;">
                                                    {{ $service->pivot->stock == 1 ? 'Đang hoạt động' : 'Bảo trì' }}
                                                </small>
                                            @else
                                                <small class="text-muted" style="font-size: 11px;">
                                                    Kho: {{ $service->pivot->stock ?? 0 }}
                                                </small>
                                            @endif
                                        </div>

                                        {{-- Nút Cài đặt --}}
                                        <button type="button" class="btn btn-sm btn-light text-secondary border"
                                            data-bs-toggle="modal" data-bs-target="#updateServiceModal"
                                            data-venue-id="{{ $venue->id }}" data-service-id="{{ $service->id }}"
                                            data-name="{{ $service->name }}" data-type="{{ $service->type }}"
                                            data-price="{{ $service->pivot->price }}"
                                            data-stock="{{ $service->pivot->stock }}">
                                            <i class="fas fa-cog"></i>
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="p-4 text-center text-muted small">
                                Chưa có dịch vụ nào đang bán tại sân này.
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Hình ảnh --}}
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h5 class="mb-0 text-primary fw-bold"><i class="fas fa-images me-2"></i>Thư viện ảnh</h5>
                    </div>
                    <div class="card-body">
                        @if ($venue->images->count())
                            @php $primaryImage = $venue->images->firstWhere('is_primary', 1); @endphp
                            @if ($primaryImage)
                                <div class="mb-3 position-relative">
                                    <img src="{{ asset($primaryImage->url) }}" class="img-fluid rounded shadow-sm w-100"
                                        style="max-height: 200px; object-fit: cover;" alt="Ảnh chính">
                                </div>
                            @endif
                            <div class="row g-2">
                                @foreach ($venue->images->where('is_primary', 0)->take(6) as $image)
                                    <div class="col-4">
                                        <img src="{{ asset($image->url) }}" class="img-fluid rounded border"
                                            style="aspect-ratio: 1/1; object-fit: cover;" alt="Gallery">
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-muted text-center py-3 mb-0">Chưa có hình ảnh mô tả.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ========================================== --}}
    {{-- MODAL CẬP NHẬT DỊCH VỤ (Pop-up) --}}
    {{-- ========================================== --}}
    <div class="modal fade" id="updateServiceModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                {{-- Form Action trỏ về route update_stock --}}
                <form id="formUpdateService" method="POST" action="{{ route('owner.services.update_stock') }}">
                    @csrf

                    {{-- 1. CÁC INPUT ẨN ĐỂ GỬI DỮ LIỆU LÊN SERVER --}}
                    <input type="hidden" name="venue_id" id="hiddenVenueId">
                    <input type="hidden" name="service_id" id="hiddenServiceId">

                    {{-- QUAN TRỌNG: Đây là input stock duy nhất được gửi đi. JS sẽ đổ dữ liệu vào đây --}}
                    <input type="hidden" name="stock" id="finalStockValue">

                    <div class="modal-header">
                        <h5 class="modal-title fw-bold">Cài đặt dịch vụ</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label text-muted small text-uppercase fw-bold">Tên dịch vụ</label>
                            <input type="text" class="form-control bg-light" id="modalServiceName" disabled>
                        </div>

                        <div class="row g-3">
                            {{-- Cột Giá --}}
                            <div class="col-6" id="priceInputColumn">
                                <label class="form-label small fw-bold">Giá bán (VNĐ)</label>
                                <input type="number" name="price" id="modalServicePrice" class="form-control"
                                    min="0">
                            </div>

                            {{-- Cột Stock / Trạng thái --}}
                            <div class="col-6" id="stockStatusColumn">

                                {{-- Giao diện 1: Nhập số tồn (Cho sản phẩm thường) --}}
                                {{-- Lưu ý: Không đặt name="stock" ở đây để tránh trùng lặp --}}
                                <div id="stockInputGroup">
                                    <label class="form-label small fw-bold">Số lượng tồn kho</label>
                                    <input type="number" id="uiStockInput" class="form-control" min="0">
                                </div>

                                {{-- Giao diện 2: Toggle Bảo trì (Cho Amenities) --}}
                                <div id="statusToggleGroup" class="d-none">
                                    <label class="form-label small fw-bold">Trạng thái tiện ích</label>
                                    <div class="btn-group w-100" role="group">
                                        {{-- Name là ui_stock_radio để JS bắt sự kiện, không gửi trực tiếp --}}
                                        <input type="radio" class="btn-check" name="ui_stock_radio"
                                            id="statusMaintenance" value="0">
                                        <label class="btn btn-outline-danger" for="statusMaintenance">Bảo trì</label>

                                        <input type="radio" class="btn-check" name="ui_stock_radio" id="statusActive"
                                            value="1">
                                        <label class="btn btn-outline-success" for="statusActive">Hoạt động</label>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- SCRIPT XỬ LÝ LOGIC --}}
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var updateModal = document.getElementById('updateServiceModal');

                updateModal.addEventListener('show.bs.modal', function(event) {
                    // 1. Lấy nút kích hoạt modal
                    var button = event.relatedTarget;

                    // 2. Lấy dữ liệu từ data attributes
                    var venueId = button.getAttribute('data-venue-id');
                    var serviceId = button.getAttribute('data-service-id');
                    var name = button.getAttribute('data-name');
                    var type = button.getAttribute('data-type');
                    var price = button.getAttribute('data-price');
                    var stock = button.getAttribute('data-stock');

                    // 3. Gán ID vào input ẩn
                    document.getElementById('hiddenVenueId').value = venueId;
                    document.getElementById('hiddenServiceId').value = serviceId;

                    // 4. Fill dữ liệu hiển thị cơ bản
                    document.getElementById('modalServiceName').value = name;

                    document.getElementById('modalServicePrice').value = price.toLocaleString('vi-VN');ss
                    var priceCol = document.getElementById('priceInputColumn');
                    var stockCol = document.getElementById('stockStatusColumn');

                    var stockGroup = document.getElementById('stockInputGroup');
                    var statusGroup = document.getElementById('statusToggleGroup');

                    var uiStockInput = document.getElementById('uiStockInput'); // Input nhập số
                    var finalStockInput = document.getElementById('finalStockValue'); // Input ẩn gửi đi

                    // Reset Radio
                    document.getElementById('statusActive').checked = false;
                    document.getElementById('statusMaintenance').checked = false;

                    // 6. LOGIC HIỂN THỊ THEO TYPE
                    if (type === 'amenities') {
                        // === TRƯỜNG HỢP: AMENITIES ===
                        priceCol.classList.add('d-none'); // Ẩn giá
                        stockCol.classList.replace('col-6', 'col-12'); // Mở rộng cột trạng thái

                        stockGroup.classList.add('d-none'); // Ẩn nhập số
                        statusGroup.classList.remove('d-none'); // Hiện Radio

                        // Set giá trị ban đầu cho Radio
                        if (stock == 1) {
                            document.getElementById('statusActive').checked = true;
                        } else {
                            document.getElementById('statusMaintenance').checked = true;
                        }

                        // Set giá trị cho input ẩn
                        finalStockInput.value = stock;

                    } else {
                        // === TRƯỜNG HỢP: DỊCH VỤ THƯỜNG ===
                        priceCol.classList.remove('d-none'); // Hiện giá
                        stockCol.classList.replace('col-12', 'col-6'); // Thu nhỏ cột stock

                        stockGroup.classList.remove('d-none'); // Hiện nhập số
                        statusGroup.classList.add('d-none'); // Ẩn Radio

                        // Set giá trị ban đầu cho Input số
                        uiStockInput.value = stock;

                        // Set giá trị cho input ẩn
                        finalStockInput.value = stock;
                    }

                    // 7. LẮNG NGHE SỰ KIỆN ĐỂ CẬP NHẬT INPUT ẨN

                    // a. Khi gõ số -> update input ẩn
                    uiStockInput.oninput = function() {
                        finalStockInput.value = this.value;
                    };

                    // b. Khi chọn Radio -> update input ẩn
                    var radios = document.querySelectorAll('input[name="ui_stock_radio"]');
                    radios.forEach(radio => {
                        radio.onchange = function() {
                            if (this.checked) {
                                finalStockInput.value = this.value;
                            }
                        };
                    });
                });
            });
        </script>
    @endpush
@endsection
