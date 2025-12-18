@extends('app')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">

                <!-- HEADER -->
                <div class="card mb-3">
                    <div class="card-header bg-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <h3 class="card-title mb-0">
                                <i class="fas fa-user-circle text-primary"></i> Chi tiết Người dùng: {{ $user->name }}
                            </h3>
                            <div>
                                <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-warning btn-sm">
                                    <i class="fas fa-edit"></i> Chỉnh sửa
                                </a>
                                <a href="{{ route('admin.users.index') }}" class="btn btn-secondary btn-sm">
                                    <i class="fas fa-arrow-left"></i> Quay lại
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- 1. THÔNG TIN CƠ BẢN -->
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-header bg-info text-white">
                                <h5 class="card-title mb-0"><i class="fas fa-info-circle"></i> Thông tin cơ bản</h5>
                            </div>
                            <div class="card-body">
                                <div class="text-center mb-4">
                                    <img src="{{ $user->avt ? asset($user->avt) : 'https://via.placeholder.com/150' }}"
                                        class="rounded-circle img-thumbnail"
                                        style="width: 120px; height: 120px; object-fit: cover;" alt="User Avatar">
                                </div>
                                <table class="table table-striped">
                                    <tr>
                                        <th style="width: 30%">ID:</th>
                                        <td>#{{ $user->id }}</td>
                                    </tr>
                                    <tr>
                                        <th>Họ tên:</th>
                                        <td>{{ $user->name }}</td>
                                    </tr>
                                    <tr>
                                        <th>Email:</th>
                                        <td>{{ $user->email }}</td>
                                    </tr>
                                    <tr>
                                        <th>SĐT:</th>
                                        <td>{{ $user->phone ?? 'Chưa cập nhật' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Vai trò:</th>
                                        <td>
                                            @if ($user->role->name === 'admin')
                                                <span class="badge badge-danger">Admin</span>
                                            @elseif($user->role->name === 'venue_owner')
                                                <span class="badge badge-success">Chủ sân</span>
                                            @else
                                                <span class="badge badge-secondary">Khách hàng</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Trạng thái:</th>
                                        <td>
                                            @if ($user->is_active)
                                                <span class="badge badge-success">Đang hoạt động</span>
                                            @else
                                                <span class="badge badge-danger">Đang khóa</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Ngày tham gia:</th>
                                        <td>{{ $user->created_at->format('d/m/Y') }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- 2. THÔNG TIN ĐỊA CHỈ -->
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-header bg-primary text-white">
                                <h5 class="card-title mb-0"><i class="fas fa-map-marker-alt"></i> Thông tin địa chỉ</h5>
                            </div>
                            <div class="card-body">
                                <table class="table table-striped">
                                    <tr>
                                        <th style="width: 30%">Tỉnh/Thành:</th>
                                        <td>{{ $user->province->name ?? '---' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Quận/Huyện:</th>
                                        <td>{{ $user->district->name ?? '---' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Tọa độ (Lat/Lng):</th>
                                        <td>
                                            @if ($user->lat && $user->lng)
                                                <a href="https://maps.google.com/?q={{ $user->lat }},{{ $user->lng }}"
                                                    target="_blank">
                                                    {{ $user->lat }}, {{ $user->lng }} <i
                                                        class="fas fa-external-link-alt"></i>
                                                </a>
                                            @else
                                                ---
                                            @endif
                                        </td>
                                    </tr>
                                </table>

                                @if ($user->lat && $user->lng)
                                    <div class="mt-3 border rounded p-1">
                                        <!-- Map Iframe placeholder -->
                                        <div
                                            style="width: 100%; height: 200px; background: #eee; display: flex; align-items: center; justify-content: center;">
                                            <span class="text-muted">Khu vực bản đồ (Cần API Key để hiển thị)</span>
                                            {{-- Bỏ comment dòng dưới nếu có API Key
                                    <iframe
                                        width="100%"
                                        height="100%"
                                        frameborder="0"
                                        style="border:0"
                                        src="https://www.google.com/maps/embed/v1/view?key=YOUR_API_KEY&center={{ $user->lat }},{{ $user->lng }}&zoom=15">
                                    </iframe>
                                    --}}
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 3. HỒ SƠ ĐỐI TÁC (MERCHANT PROFILE) - CHỈ HIỆN NẾU CÓ -->
                @if ($user->merchantProfile)
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card border-warning">
                                <div class="card-header bg-warning text-dark">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-briefcase"></i> Hồ sơ Đăng ký Đối tác (Merchant Profile)
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <!-- CỘT 1: THÔNG TIN CHI TIẾT -->
                                        <div class="col-md-6">
                                            <h6 class="text-uppercase text-muted border-bottom pb-2">Thông tin doanh nghiệp
                                                & Thanh toán</h6>
                                            <table class="table table-borderless">
                                                <tr>
                                                    <th style="width: 35%">Tên doanh nghiệp:</th>
                                                    <td class="font-weight-bold">
                                                        {{ $user->merchantProfile->business_name }}</td>
                                                </tr>
                                                <tr>
                                                    <th>Địa chỉ KD:</th>
                                                    <td>{{ $user->merchantProfile->business_address }}</td>
                                                </tr>
                                                <tr>
                                                    <th>Ngân hàng:</th>
                                                    <td>{{ $user->merchantProfile->bank_name }}</td>
                                                </tr>
                                                <tr>
                                                    <th>Số tài khoản:</th>
                                                    <td class="font-weight-bold text-primary">
                                                        {{ $user->merchantProfile->bank_account_number }}</td>
                                                </tr>
                                                <tr>
                                                    <th>Chủ tài khoản:</th>
                                                    <td>{{ $user->merchantProfile->bank_account_name }}</td>
                                                </tr>
                                                <tr>
                                                    <th>Trạng thái:</th>
                                                    <td>
                                                        @if ($user->merchantProfile->status == 'approved')
                                                            <span class="badge badge-success p-2">
                                                                <i class="fas fa-check-circle"></i> Đã duyệt
                                                            </span>
                                                        @elseif($user->merchantProfile->status == 'rejected')
                                                            <span class="badge badge-danger p-2">
                                                                <i class="fas fa-times-circle"></i> Đã từ chối
                                                            </span>
                                                        @else
                                                            {{-- Bao gồm cả pending và resubmitted --}}
                                                            <span class="badge badge-warning p-2">
                                                                <i class="fas fa-clock"></i>
                                                                {{ $user->merchantProfile->status == 'resubmitted' ? 'Đã nộp lại (Chờ duyệt)' : 'Chờ duyệt' }}
                                                            </span>
                                                        @endif
                                                    </td>
                                                </tr>
                                                @if ($user->merchantProfile->admin_note)
                                                    <tr>
                                                        <th class="text-danger">Ghi chú Admin:</th>
                                                        <td class="text-danger">
                                                            <em>"{{ $user->merchantProfile->admin_note }}"</em>
                                                        </td>
                                                    </tr>
                                                @endif
                                            </table>
                                        </div>

                                        <!-- CỘT 2: HÌNH ẢNH MINH CHỨNG -->
                                        <div class="col-md-6">
                                            <h6 class="text-uppercase text-muted border-bottom pb-2">Giấy tờ & Hình ảnh đính
                                                kèm</h6>

                                            <div class="row mt-3">
                                                @if ($user->merchantProfile->images && $user->merchantProfile->images->count() > 0)
                                                    @foreach ($user->merchantProfile->images as $image)
                                                        <div class="col-4 mb-3">
                                                            {{-- Thẻ A để click vào xem ảnh to --}}
                                                            <a href="{{ $image->url }}" target="_blank"
                                                                title="Click để xem ảnh gốc">
                                                                <img src="{{ $image->url }}"
                                                                    class="img-thumbnail w-100 shadow-sm"
                                                                    style="height: 120px; object-fit: cover; border-radius: 8px; cursor: pointer;"
                                                                    alt="Giấy tờ minh chứng"
                                                                    onerror="this.src='https://via.placeholder.com/150?text=Lỗi+Ảnh'">
                                                            </a>
                                                        </div>
                                                    @endforeach
                                                @else
                                                    <div class="col-12 text-center text-muted py-4">
                                                        <i class="fas fa-image fa-2x mb-2"></i><br>
                                                        <small>Người dùng chưa tải lên hình ảnh nào.</small>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- 4. DANH SÁCH VENUE (ĐỊA ĐIỂM) -->
                @if ($venues->count() > 0)
                    <div class="card mt-4">
                        <div class="card-header bg-success text-white">
                            <h5 class="card-title mb-0"><i class="fas fa-futbol"></i> Danh sách Địa điểm sở hữu
                                ({{ $venues->total() }})</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>ID</th>
                                            <th>Tên sân</th>
                                            <th>Địa chỉ chi tiết</th>
                                            <th>Số sân con</th>
                                            <th>Trạng thái</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($venues as $venue)
                                            <tr>
                                                <td>{{ $venue->id }}</td>
                                                <td class="font-weight-bold">{{ $venue->name }}</td>
                                                <td>{{ $venue->address_detail }}</td>
                                                <td><span class="badge badge-info">{{ $venue->courts->count() }} sân</span>
                                                </td>
                                                <td>
                                                    @if ($venue->is_active)
                                                        <span class="badge badge-success">Hoạt động</span>
                                                    @else
                                                        <span class="badge badge-secondary">Tạm dừng</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="d-flex justify-content-center mt-3">
                                {{ $venues->appends(['bookings_page' => $bookings->currentPage()])->links() }}
                            </div>
                        </div>
                    </div>
                @endif

                <!-- 5. LỊCH SỬ ĐẶT SÂN -->
                @if ($bookings->count() > 0)
                    <div class="card mt-4 mb-5">
                        <div class="card-header bg-secondary text-white">
                            <h5 class="card-title mb-0"><i class="fas fa-history"></i> Lịch sử Đặt sân gần đây
                                ({{ $bookings->total() }})</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>ID</th>
                                            <th>Địa điểm</th>
                                            <th>Sân</th>
                                            <th>Thời gian</th>
                                            <th>Trạng thái</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($bookings as $booking)
                                            <tr>
                                                <td>#{{ $booking->id }}</td>
                                                <td>{{ $booking->court->venue->name ?? '---' }}</td>
                                                <td>{{ $booking->court->name ?? '---' }}</td>
                                                <td>
                                                    {{ \Carbon\Carbon::parse($booking->date)->format('d/m/Y') }} <br>
                                                    <small>{{ $booking->timeSlot->start_time ?? '--' }} -
                                                        {{ $booking->timeSlot->end_time ?? '--' }}</small>
                                                </td>
                                                <td>
                                                    @php
                                                        $statusClass = match ($booking->status) {
                                                            'success' => 'success',
                                                            'pending' => 'warning',
                                                            'canceled' => 'danger',
                                                            default => 'secondary',
                                                        };
                                                    @endphp
                                                    <span
                                                        class="badge badge-{{ $statusClass }}">{{ ucfirst($booking->status) }}</span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="d-flex justify-content-center mt-3">
                                {{ $bookings->appends(['venues_page' => $venues->currentPage()])->links() }}
                            </div>
                        </div>
                    </div>
                @endif

            </div>
        </div>
    </div>
@endsection
