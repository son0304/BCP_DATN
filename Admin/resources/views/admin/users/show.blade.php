@extends('app')

@section('content')
    <div class="container-fluid py-4">
        {{-- 1. HEADER & ACTION BUTTONS --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1">
                        <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">Người dùng</a></li>
                        <li class="breadcrumb-item active">Chi tiết</li>
                    </ol>
                </nav>
                <h1 class="h4 mb-0 text-primary fw-bold">
                    <i class="fas fa-user-circle me-2"></i>Người dùng: {{ $user->name }}
                </h1>
            </div>
            <div class="btn-group">
                <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-warning shadow-sm">
                    <i class="fas fa-edit me-1"></i> Chỉnh sửa
                </a>
                <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary shadow-sm ms-2">
                    <i class="fas fa-arrow-left me-1"></i> Quay lại
                </a>
            </div>
        </div>

        <div class="row g-4">
            {{-- 2. CỘT TRÁI: THÔNG TIN CƠ BẢN --}}
            <div class="col-lg-6">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-info text-white py-3">
                        <h5 class="card-title mb-0"><i class="fas fa-info-circle me-2"></i>Thông tin cơ bản</h5>
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-4">
                            <div class="position-relative d-inline-block">
                                <img src="{{ $user->avt ? asset($user->avt) : 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&background=random' }}"
                                    class="rounded-circle img-thumbnail shadow-sm"
                                    style="width: 120px; height: 120px; object-fit: cover;" alt="User Avatar">
                                <span class="position-absolute bottom-0 end-0 p-2 bg-{{ $user->is_active ? 'success' : 'danger' }} border border-light rounded-circle"></span>
                            </div>
                        </div>
                        <table class="table table-sm align-middle">
                            <tbody>
                                <tr>
                                    <th class="text-muted border-0 py-2" width="35%">ID hệ thống:</th>
                                    <td class="fw-bold border-0">#{{ $user->id }}</td>
                                </tr>
                                <tr>
                                    <th class="text-muted py-2">Email:</th>
                                    <td class="fw-bold">{{ $user->email }}</td>
                                </tr>
                                <tr>
                                    <th class="text-muted py-2">Số điện thoại:</th>
                                    <td>{{ $user->phone ?? 'Chưa cập nhật' }}</td>
                                </tr>
                                <tr>
                                    <th class="text-muted py-2">Vai trò:</th>
                                    <td>
                                        @php
                                            $roleBadge = match($user->role->name) {
                                                'admin' => 'danger',
                                                'venue_owner' => 'success',
                                                default => 'secondary'
                                            };
                                            $roleText = match($user->role->name) {
                                                'admin' => 'Quản trị viên',
                                                'venue_owner' => 'Chủ sân',
                                                default => 'Khách hàng'
                                            };
                                        @endphp
                                        <span class="badge bg-{{ $roleBadge }} px-3 py-2">{{ $roleText }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="text-muted py-2">Trạng thái:</th>
                                    <td>
                                        <span class="badge bg-{{ $user->is_active ? 'success' : 'danger' }}-subtle text-{{ $user->is_active ? 'success' : 'danger' }} border border-{{ $user->is_active ? 'success' : 'danger' }} px-3">
                                            {{ $user->is_active ? 'Đang hoạt động' : 'Đang khóa' }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="text-muted py-2">Ngày tham gia:</th>
                                    <td>{{ $user->created_at->format('d/m/Y') }} <small class="text-muted">({{ $user->created_at->diffForHumans() }})</small></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- 3. CỘT PHẢI: ĐỊA CHỈ --}}
            <div class="col-lg-6">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-primary text-white py-3">
                        <h5 class="card-title mb-0"><i class="fas fa-map-marker-alt me-2"></i>Thông tin địa chỉ</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm align-middle">
                            <tbody>
                                <tr>
                                    <th class="text-muted border-0 py-2" width="35%">Tỉnh/Thành phố:</th>
                                    <td class="fw-bold border-0">{{ $user->province->name ?? '---' }}</td>
                                </tr>
                                <tr>
                                    <th class="text-muted py-2">Quận/Huyện:</th>
                                    <td class="fw-bold">{{ $user->district->name ?? '---' }}</td>
                                </tr>
                                <tr>
                                    <th class="text-muted py-2">Tọa độ:</th>
                                    <td>
                                        @if ($user->lat && $user->lng)
                                            <a href="https://maps.google.com/?q={{ $user->lat }},{{ $user->lng }}" target="_blank" class="text-decoration-none">
                                                <code>{{ $user->lat }}, {{ $user->lng }}</code>
                                                <i class="fas fa-external-link-alt ms-1 small"></i>
                                            </a>
                                        @else
                                            <span class="text-muted italic">Chưa cập nhật tọa độ</span>
                                        @endif
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <div class="mt-4">
                            <h6 class="text-muted small fw-bold text-uppercase">Bản đồ khu vực</h6>
                            <div class="rounded bg-light border d-flex align-items-center justify-content-center" style="height: 185px;">
                                @if ($user->lat && $user->lng)
                                    <i class="fas fa-map-marked-alt fa-3x text-muted opacity-25"></i>
                                    {{-- Nhúng Iframe Google Map thực tế ở đây --}}
                                @else
                                    <p class="text-muted mb-0 small text-center px-4">Cần vĩ độ/kinh độ để hiển thị bản đồ chi tiết</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 4. HỒ SƠ ĐỐI TÁC (CHỈ HIỆN NẾU CÓ) --}}
            @if ($user->merchantProfile)
                <div class="col-12 mt-4">
                    <div class="card shadow-sm border-0 border-start border-warning border-4">
                        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 text-dark fw-bold"><i class="fas fa-briefcase text-warning me-2"></i>Hồ sơ Đối tác (Merchant Profile)</h5>
                            @php
                                $mStatus = $user->merchantProfile->status;
                                $statusBadge = match($mStatus) {
                                    'approved' => 'success',
                                    'rejected' => 'danger',
                                    'resubmitted' => 'info',
                                    default => 'warning text-dark'
                                };
                            @endphp
                            <span class="badge bg-{{ $statusBadge }} px-3 py-2">
                                <i class="fas {{ $mStatus == 'approved' ? 'fa-check-circle' : 'fa-clock' }} me-1"></i>
                                {{ strtoupper($mStatus) }}
                            </span>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-7">
                                    <h6 class="text-primary fw-bold mb-3">Thông tin pháp lý & Tài chính</h6>
                                    <dl class="row mb-0">
                                        <dt class="col-sm-4 text-muted">Doanh nghiệp:</dt>
                                        <dd class="col-sm-8 fw-bold">{{ $user->merchantProfile->business_name }}</dd>

                                        <dt class="col-sm-4 text-muted">Địa chỉ ĐKKD:</dt>
                                        <dd class="col-sm-8">{{ $user->merchantProfile->business_address }}</dd>

                                        <dt class="col-sm-4 text-muted">Ngân hàng:</dt>
                                        <dd class="col-sm-8">{{ $user->merchantProfile->bank_name }}</dd>

                                        <dt class="col-sm-4 text-muted">Số tài khoản:</dt>
                                        <dd class="col-sm-8 text-primary font-monospace fs-5 fw-bold">{{ $user->merchantProfile->bank_account_number }}</dd>

                                        <dt class="col-sm-4 text-muted">Chủ tài khoản:</dt>
                                        <dd class="col-sm-8 text-uppercase">{{ $user->merchantProfile->bank_account_name }}</dd>

                                        @if ($user->merchantProfile->admin_note)
                                            <dt class="col-sm-4 text-danger">Ghi chú Admin:</dt>
                                            <dd class="col-sm-8 text-danger fst-italic">"{{ $user->merchantProfile->admin_note }}"</dd>
                                        @endif
                                    </dl>
                                </div>
                                <div class="col-md-5 border-start">
                                    <h6 class="text-primary fw-bold mb-3 px-md-3">Giấy tờ minh chứng</h6>
                                    <div class="row g-2 px-md-3">
                                        @forelse ($user->merchantProfile->images as $image)
                                            <div class="col-4 col-sm-6">
                                                <a href="{{ asset($image->url) }}" target="_blank" class="d-block border rounded overflow-hidden">
                                                    <img src="{{ asset($image->url) }}" class="img-fluid w-100" style="height: 100px; object-fit: cover;" alt="Minh chứng">
                                                </a>
                                            </div>
                                        @empty
                                            <div class="text-muted py-4 text-center">Không có hình ảnh đính kèm.</div>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- 5. DANH SÁCH ĐỊA ĐIỂM SỞ HỮU --}}
            @if ($venues->count() > 0)
                <div class="col-12 mt-4">
                    <div class="card shadow-sm border-0 overflow-hidden">
                        <div class="card-header bg-success text-white py-3">
                            <h5 class="mb-0"><i class="fas fa-futbol me-2"></i>Địa điểm sở hữu ({{ $venues->total() }})</h5>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-4">ID</th>
                                        <th>Tên sân</th>
                                        <th>Địa chỉ</th>
                                        <th>Sân con</th>
                                        <th>Trạng thái</th>
                                        <th class="text-end pe-4">Hành động</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($venues as $venue)
                                        <tr>
                                            <td class="ps-4">#{{ $venue->id }}</td>
                                            <td class="fw-bold">{{ $venue->name }}</td>
                                            <td class="small">{{ $venue->address_detail }}</td>
                                            <td><span class="badge bg-info-subtle text-info">{{ $venue->courts->count() }} sân</span></td>
                                            <td>
                                                <span class="badge rounded-pill bg-{{ $venue->is_active ? 'success' : 'secondary' }}">
                                                    {{ $venue->is_active ? 'Hoạt động' : 'Tạm dừng' }}
                                                </span>
                                            </td>
                                            <td class="text-end pe-4">
                                                <a href="{{ route('admin.venues.show', $venue->id) }}" class="btn btn-sm btn-light border">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="card-footer bg-white border-top">
                            {{ $venues->appends(['bookings_page' => $bookings->currentPage()])->links() }}
                        </div>
                    </div>
                </div>
            @endif

            {{-- 6. LỊCH SỬ ĐẶT SÂN --}}
            @if ($bookings->count() > 0)
                <div class="col-12 mt-4 mb-5">
                    <div class="card shadow-sm border-0 overflow-hidden">
                        <div class="card-header bg-dark text-white py-3">
                            <h5 class="mb-0"><i class="fas fa-history me-2"></i>Lịch sử đặt sân gần đây ({{ $bookings->total() }})</h5>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-4">Mã đơn</th>
                                        <th>Địa điểm</th>
                                        <th>Sân</th>
                                        <th>Thời gian đặt</th>
                                        <th>Trạng thái</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($bookings as $booking)
                                        <tr>
                                            <td class="ps-4 font-monospace">#{{ $booking->id }}</td>
                                            <td class="fw-bold">{{ $booking->court->venue->name ?? '---' }}</td>
                                            <td>{{ $booking->court->name ?? '---' }}</td>
                                            <td>
                                                <div class="fw-bold">{{ \Carbon\Carbon::parse($booking->date)->format('d/m/Y') }}</div>
                                                <small class="text-muted">{{ $booking->timeSlot->start_time ?? '--' }} - {{ $booking->timeSlot->end_time ?? '--' }}</small>
                                            </td>
                                            <td>
                                                @php
                                                    $bStatus = match ($booking->status) {
                                                        'success' => 'success',
                                                        'pending' => 'warning',
                                                        'canceled' => 'danger',
                                                        default => 'secondary',
                                                    };
                                                @endphp
                                                <span class="badge bg-{{ $bStatus }} text-uppercase" style="font-size: 0.7rem;">{{ $booking->status }}</span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="card-footer bg-white border-top">
                            {{ $bookings->appends(['venues_page' => $venues->currentPage()])->links() }}
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
