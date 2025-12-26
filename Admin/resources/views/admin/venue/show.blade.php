@extends('app')

@section('content')
    <div class="container-fluid py-4">

        {{-- HEADER --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h4 mb-0 text-primary">Chi tiết điểm đặt sân</h1>
                <p class="text-muted mb-0">Đang xem: <strong>{{ $venue->name }}</strong></p>
            </div>
            <a href="{{ route('admin.venues.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Quay lại
            </a>
        </div>

        <div class="row">

            {{-- CỘT TRÁI (CHÍNH - CHIẾM 8 PHẦN) --}}
            <div class="col-lg-8">

                {{-- 1. HỒ SƠ DOANH NGHIỆP (MERCHANT PROFILE) --}}
                @if (isset($merchant_profile) && $merchant_profile)
                    @php
                        $mStatus = $merchant_profile['status'] ?? 'pending';
                        // Kiểm tra xem hồ sơ đã được xử lý (Duyệt hoặc Từ chối) chưa
                        $isProcessed = in_array($mStatus, ['approved', 'rejected']);

                        // Cấu hình hiển thị màu sắc và icon theo trạng thái
                        $statusConfig = match ($mStatus) {
                            'approved' => ['color' => 'success', 'text' => 'Đã xác thực', 'icon' => 'fa-check-circle'],
                            'rejected' => ['color' => 'danger', 'text' => 'Đã từ chối', 'icon' => 'fa-times-circle'],
                            'resubmitted' => ['color' => 'info', 'text' => 'Xác thực lại', 'icon' => 'fa-sync-alt'],
                            'pending' => [
                                'color' => 'warning text-dark',
                                'text' => 'Chờ xác thực',
                                'icon' => 'fa-clock',
                            ],
                            default => [
                                'color' => 'secondary',
                                'text' => 'Chờ xác thực',
                                'icon' => 'fa-question-circle',
                            ],
                        };
                    @endphp

                    <div class="card shadow-sm border-0 mb-4">
                        {{-- CARD HEADER: LUÔN HIỂN THỊ --}}
                        <div
                            class="card-header py-3 bg-white border-bottom d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 text-primary">
                                <i class="fas fa-briefcase me-2"></i>Hồ sơ Doanh nghiệp
                            </h5>

                            <span class="badge bg-{{ $statusConfig['color'] }} d-flex align-items-center gap-2 px-3 py-2">
                                <i class="fas {{ $statusConfig['icon'] }}"></i>
                                {{ $statusConfig['text'] }}
                            </span>
                        </div>

                        <div class="card-body">
                            {{-- TRƯỜNG HỢP A: ĐÃ XỬ LÝ (APPROVED / REJECTED) --}}
                            {{-- Chỉ hiển thị trạng thái và nút điều hướng --}}
                            @if ($isProcessed)
                                <div class="text-center py-4">
                                    <div class="mb-3">
                                        <i class="fas {{ $mStatus == 'approved' ? 'fa-check-circle text-success' : 'fa-times-circle text-danger' }}"
                                            style="font-size: 4rem;"></i>
                                    </div>
                                    <h5 class="fw-bold text-secondary">
                                        Hồ sơ này đã {{ $mStatus == 'approved' ? 'được xác thực hợp lệ' : 'bị từ chối' }}.
                                    </h5>
                                    <p class="text-muted mb-4">
                                        Để xem chi tiết thông tin ngân hàng, giấy tờ pháp lý hoặc lịch sử chỉnh sửa,<br>
                                        vui lòng truy cập vào trang chi tiết của chủ sân.
                                    </p>

                                    {{-- NÚT ĐIỀU HƯỚNG SANG TRANG USER --}}
                                    <a href="{{ route('admin.users.show', $venue->owner->id) }}"
                                        class="btn btn-primary px-4">
                                        <i class="fas fa-user-shield me-2"></i> Xem hồ sơ Chủ sân
                                    </a>
                                </div>

                                {{-- TRƯỜNG HỢP B: CHỜ XỬ LÝ (PENDING / RESUBMITTED) --}}
                                {{-- Hiển thị đầy đủ thông tin để Admin duyệt --}}
                            @else
                                <dl class="row mb-0">
                                    <dt class="col-sm-4 text-muted">Tên doanh nghiệp:</dt>
                                    <dd class="col-sm-8 fw-bold">{{ $merchant_profile['business_name'] ?? 'N/A' }}</dd>

                                    <dt class="col-sm-4 text-muted">Địa chỉ ĐKKD:</dt>
                                    <dd class="col-sm-8">{{ $merchant_profile['business_address'] ?? 'N/A' }}</dd>

                                    <div class="col-12">
                                        <hr class="my-2 text-muted opacity-25">
                                    </div>

                                    <dt class="col-sm-4 text-muted">Ngân hàng:</dt>
                                    <dd class="col-sm-8">{{ $merchant_profile['bank_name'] ?? 'N/A' }}</dd>

                                    <dt class="col-sm-4 text-muted">Số tài khoản:</dt>
                                    <dd class="col-sm-8 font-monospace text-danger fw-bold">
                                        {{ $merchant_profile['bank_account_number'] ?? 'N/A' }}</dd>

                                    <dt class="col-sm-4 text-muted">Chủ tài khoản:</dt>
                                    <dd class="col-sm-8 text-uppercase">
                                        {{ $merchant_profile['bank_account_name'] ?? 'N/A' }}</dd>

                                    {{-- Nếu là resubmitted, hiện ghi chú cũ để đối chiếu --}}
                                    @if (!empty($merchant_profile['admin_note']) && $mStatus == 'resubmitted')
                                        <div class="col-12 mt-3">
                                            <div class="alert alert-info mb-0 border-0 small">
                                                <i class="fas fa-history me-1"></i>
                                                <strong>Lý do từ chối lần trước:</strong>
                                                {{ $merchant_profile['admin_note'] }}
                                            </div>
                                        </div>
                                    @endif
                                </dl>

                                {{-- Hình ảnh giấy tờ --}}
                                @if (isset($merchant_profile['images']) && count($merchant_profile['images']) > 0)
                                    <div class="mt-4 pt-3 border-top">
                                        <h6 class="text-secondary small fw-bold mb-3"><i
                                                class="fas fa-file-contract me-1"></i> Giấy tờ đính kèm</h6>
                                        <div class="row g-2">
                                            @foreach ($merchant_profile['images'] as $image)
                                                <div class="col-4 col-md-3">
                                                    <a href="{{ asset($image['url']) }}" target="_blank"
                                                        class="d-block border rounded overflow-hidden shadow-sm position-relative group-hover">
                                                        <img src="{{ asset($image['url']) }}" class="img-fluid w-100"
                                                            style="height: 100px; object-fit: cover;">
                                                        <div
                                                            class="position-absolute bottom-0 w-100 bg-dark bg-opacity-75 text-white text-center small py-1">
                                                            <i class="fas fa-search-plus"></i> Xem
                                                        </div>
                                                    </a>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @else
                                    <div class="mt-3 text-muted small fst-italic">Chưa có giấy tờ đính kèm.</div>
                                @endif
                            @endif
                        </div>

                        {{-- CARD FOOTER: CHỈ HIỂN THỊ KHI CHƯA XỬ LÝ --}}
                        @if (!$isProcessed)
                            <div class="card-footer bg-light py-3 d-flex justify-content-end gap-2">
                                {{-- Nút Từ chối --}}
                                <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal"
                                    data-bs-target="#rejectMerchantModal">
                                    <i class="fas fa-times me-1"></i> Từ chối
                                </button>

                                {{-- Nút Duyệt --}}
                                <form action="{{ route('admin.venues.update-merchant', $venue->id) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="approved">
                                    <button type="submit" class="btn btn-success"
                                        onclick="return confirm('Xác nhận thông tin doanh nghiệp hợp lệ?')">
                                        <i class="fas fa-check me-1"></i> Xác nhận hồ sơ
                                    </button>
                                </form>
                            </div>
                        @endif
                    </div>
                @endif


                {{-- 2. THÔNG TIN CƠ BẢN VENUE --}}
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h5 class="mb-0 text-secondary">Thông tin địa điểm</h5>
                    </div>
                    <div class="card-body">
                        <dl class="row mb-0">
                            <dt class="col-sm-3 text-muted">Tên sân:</dt>
                            <dd class="col-sm-9 fw-bold">{{ $venue->name }}</dd>

                            <dt class="col-sm-3 text-muted">Địa chỉ:</dt>
                            <dd class="col-sm-9">{{ $venue->address_detail }}, {{ $venue->district->name ?? '' }},
                                {{ $venue->province->name ?? '' }}</dd>

                            <dt class="col-sm-3 text-muted">Liên hệ:</dt>
                            <dd class="col-sm-9">{{ $venue->phone ?? '---' }} | Chủ: {{ $venue->owner->name }}</dd>

                            <dt class="col-sm-3 text-muted">Giờ mở cửa:</dt>
                            <dd class="col-sm-9">
                                {{ \Carbon\Carbon::parse($venue->start_time)->format('H:i') }} -
                                {{ \Carbon\Carbon::parse($venue->end_time)->format('H:i') }}
                            </dd>
                        </dl>
                    </div>
                </div>

                {{-- Thêm vào bên dưới Card "Thông tin địa điểm" --}}
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 text-secondary"><i class="fas fa-map-marked-alt me-2"></i>Vị trí trên bản đồ</h5>
                        @if ($venue->lat && $venue->lng)
                            <a href="https://www.google.com/maps/search/?api=1&query={{ $venue->lat }},{{ $venue->lng }}"
                                target="_blank" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-directions"></i> Xem trên Google Maps
                            </a>
                        @endif
                    </div>
                    <div class="card-body p-0">
                        @if ($venue->lat && $venue->lng)
                            <div id="venueMap"></div>
                        @else
                            <div class="p-4 text-center text-muted">
                                <i class="fas fa-map-marker-slash fa-2x mb-2"></i>
                                <p>Chưa cập nhật tọa độ cho địa điểm này.</p>
                            </div>
                        @endif
                    </div>
                </div>


                {{-- 3. DANH SÁCH SÂN CON --}}
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 text-secondary">Danh sách sân con</h5>
                        <span class="badge bg-secondary rounded-pill">{{ $venue->courts->count() }} sân</span>
                    </div>
                    <div class="card-body p-0">
                        @if ($venue->courts->count())
                            <div class="table-responsive">
                                <table class="table table-hover mb-0 align-middle">
                                    <thead class="table-light small text-uppercase">
                                        <tr>
                                            <th class="ps-4">Tên sân</th>
                                            <th>Loại hình</th>
                                            <th>Giá (vnđ/h)</th>
                                            <th>Loại sân</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($venue->courts as $court)
                                            <tr>
                                                <td class="ps-4 fw-bold">{{ $court->name }}</td>
                                                <td>{{ $court->venueType->name ?? '-' }}</td>
                                                <td class="text-success fw-bold">
                                                    {{ number_format($court->price_per_hour, 0, ',', '.') }}</td>
                                                <td>
                                                    @if ($court->is_indoor)
                                                        <span class="badge bg-primary bg-opacity-10 text-primary">Trong
                                                            nhà</span>
                                                    @else
                                                        <span class="badge bg-success bg-opacity-10 text-success">Ngoài
                                                            trời</span>
                                                    @endif
                                                </td>
                                                <td class="text-end pe-3">
                                                    <a href="{{ route('admin.venues.courts.show', ['venue' => $venue->id, 'court' => $court->id]) }}"
                                                        class="btn btn-sm btn-light">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="p-4 text-center text-muted">Chưa có sân con nào.</div>
                        @endif
                    </div>
                </div>

            </div>

            {{-- CỘT PHẢI (SIDEBAR - CHIẾM 4 PHẦN) --}}
            <div class="col-lg-4">

                {{-- 1. TRẠNG THÁI VENUE (ĐỊA ĐIỂM) --}}
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-body">
                        <h6 class="text-uppercase text-muted small mb-3 fw-bold">Trạng thái Venue</h6>

                        {{-- Hiển thị trạng thái hiện tại --}}
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <span class="fw-bold">Hiện tại:</span>
                            @if ($venue->is_active)
                                <span class="badge bg-success">Đang hoạt động</span>
                            @else
                                <span class="badge bg-warning text-dark">Tạm dừng / Chờ duyệt</span>
                            @endif
                        </div>

                        {{-- Nút hành động --}}
                        <div class="d-grid gap-2">
                            @if (!$venue->is_active)
                                {{-- Nút KÍCH HOẠT (Gửi form active) --}}
                                <form action="{{ route('admin.venues.update-status', $venue->id) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="is_active" value="1">
                                    <button type="submit" class="btn btn-success w-100 fw-bold">
                                        <i class="fas fa-check-circle me-1"></i> Kích hoạt Sân
                                    </button>
                                </form>

                                {{-- Nút TỪ CHỐI (Mở Modal #rejectVenueModal) --}}
                                <button type="button" class="btn btn-outline-danger w-100" data-bs-toggle="modal"
                                    data-bs-target="#rejectVenueModal">
                                    <i class="fas fa-ban me-1"></i> Từ chối hoạt động
                                </button>
                            @else
                                {{-- Nếu đang active thì hiện nút Tạm dừng (Mở Modal) --}}
                                <button type="button" class="btn btn-warning w-100" data-bs-toggle="modal"
                                    data-bs-target="#rejectVenueModal">
                                    <i class="fas fa-pause me-1"></i> Tạm dừng hoạt động
                                </button>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- 2. HÌNH ẢNH VENUE --}}
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h6 class="mb-0 text-secondary">Hình ảnh Venue</h6>
                    </div>
                    <div class="card-body">
                        @if ($venue->images->count())
                            @php
                                $primary = $venue->images->firstWhere('is_primary', 1) ?? $venue->images->first();
                                $others = $venue->images->where('id', '!=', $primary->id);
                            @endphp
                            <div class="mb-2 rounded overflow-hidden">
                                <img src="{{ $primary->url }}" class="w-100" style="height: 180px; object-fit: cover;">
                            </div>
                            <div class="row g-2">
                                @foreach ($others as $img)
                                    <div class="col-4">
                                        <img src="{{ $img->url }}" class="w-100 rounded"
                                            style="height: 60px; object-fit: cover;">
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-muted small mb-0">Chưa có hình ảnh.</p>
                        @endif
                    </div>
                </div>

                {{-- 3. DỊCH VỤ --}}
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom">
                        <h5 class="mb-0 text-primary fw-bold"><i class="fas fa-cocktail me-2"></i>Dịch vụ</h5>

                    </div>
                    <div class="card-body p-0">
                        @if ($venue->services->count())
                            <div class="list-group list-group-flush">
                                @foreach ($venue->services as $service)
                                    <div class="list-group-item d-flex align-items-center p-3">
                                        <!-- Ảnh dịch vụ -->
                                        <img src="{{ $service->images->first()
                                            ? asset($service->images->first()->url)
                                            : 'https://ui-avatars.com/api/?name=BCP&length=3&background=1ABC9C&color=fff' }}"
                                            class="rounded me-3 border" width="40" height="40"
                                            style="object-fit: cover;" alt="{{ $service->name }}">

                                        <!-- Tên & Đơn vị -->
                                        <div class="flex-grow-1">
                                            <h6 class="mb-0 text-dark small fw-bold">{{ $service->name }}</h6>
                                            <small class="text-muted"
                                                style="font-size: 11px;">{{ $service->unit }}</small>
                                        </div>

                                        <!-- Giá & Kho (Lấy từ Pivot) -->
                                        <div class="text-end">
                                            <div class="fw-bold text-success small">
                                                {{ number_format($service->pivot->price ?? 0, 0, ',', '.') }}đ
                                            </div>
                                            <small class="text-muted" style="font-size: 11px;">
                                                Kho: {{ $service->pivot->stock ?? 0 }}
                                            </small>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="p-4 text-center text-muted small">
                                Chưa có dịch vụ nào đang bán tại sân này.
                            </div>
                        @endif
                    </div>
                    @if ($venue->services->count() > 5)
                        <div class="card-footer bg-white text-center p-2">
                            <a href="{{ route('owner.services.index') }}" class="small text-decoration-none">Xem tất
                                cả</a>
                        </div>
                    @endif
                </div>

            </div>
        </div>
    </div>

    {{-- ========================================================== --}}
    {{-- MODAL 1: TỪ CHỐI MERCHANT (HỒ SƠ DOANH NGHIỆP) --}}
    {{-- ========================================================== --}}
    <div class="modal fade" id="rejectMerchantModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            {{-- Form gửi về update-merchant --}}
            <form action="{{ route('admin.venues.update-merchant', $venue->id) }}" method="POST">
                @csrf
                @method('PATCH')

                <input type="hidden" name="status" value="rejected">

                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title text-danger">Từ chối Hồ sơ Doanh nghiệp</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>Hồ sơ này sẽ bị từ chối. Vui lòng nhập lý do để đối tác chỉnh sửa:</p>
                        <div class="form-group">
                            <textarea name="admin_note" class="form-control" rows="4" required
                                placeholder="VD: Ảnh CCCD bị mờ, Tên chủ tài khoản không khớp với giấy phép..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-danger">Xác nhận Từ chối</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- ========================================================== --}}
    {{-- MODAL 2: TỪ CHỐI / TẠM DỪNG VENUE (SÂN BÓNG) --}}
    {{-- ========================================================== --}}
    <div class="modal fade" id="rejectVenueModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            {{-- Form gửi về update-status --}}
            <form action="{{ route('admin.venues.update-status', $venue->id) }}" method="POST">
                @csrf
                @method('PATCH')

                {{-- Set is_active = 0 (Tắt) --}}
                <input type="hidden" name="is_active" value="0">

                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title text-warning text-dark">
                            {{ $venue->is_active ? 'Tạm dừng hoạt động Sân' : 'Từ chối duyệt Sân' }}
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>Bạn có chắc chắn muốn {{ $venue->is_active ? 'tạm dừng' : 'từ chối' }} địa điểm này không?</p>
                        <div class="form-group">
                            <label class="form-label fw-bold">Lý do / Ghi chú:</label>
                            <textarea name="admin_note" class="form-control" rows="3" required
                                placeholder="VD: Sân chưa hoàn thiện, Địa chỉ không có thực..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-warning">Xác nhận</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if ($venue->lat && $venue->lng)
        <!-- Leaflet JS -->
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Tọa độ từ database
                var lat = {{ $venue->lat }};
                var lng = {{ $venue->lng }};
                var venueName = "{{ $venue->name }}";

                // Khởi tạo bản đồ
                var map = L.map('venueMap').setView([lat, lng], 16);

                // Thêm lớp bản đồ (OpenStreetMap)
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; OpenStreetMap contributors'
                }).addTo(map);

                // Thêm Marker (điểm đánh dấu)
                var marker = L.marker([lat, lng]).addTo(map);

                // Hiển thị Popup khi click vào điểm đánh dấu
                marker.bindPopup("<b>" + venueName + "</b><br>Vị trí sân bóng.").openPopup();

                // Fix lỗi bản đồ bị lệch khi nằm trong tab hoặc card ẩn
                setTimeout(function() {
                    map.invalidateSize()
                }, 500);
            });
        </script>
    @endif

@endsection
