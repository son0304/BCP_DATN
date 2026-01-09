@extends('app')

@section('content')
    <div class="container py-5">

        {{-- Breadcrumb / Nút quay lại --}}
        <div class="mb-4">
            <a href="{{ route('owner.packages.index') }}" class="text-decoration-none text-muted">
                <i class="fas fa-arrow-left me-1"></i> Quay lại danh sách gói
            </a>
        </div>

        <div class="row">
            <!-- CỘT TRÁI: THÔNG TIN GÓI -->
            <div class="col-lg-4 mb-4">
                <div class="card shadow-sm border-0 rounded-4">
                    <div class="card-body text-center p-4">
                        <span class="badge bg-primary-soft text-primary px-3 py-2 rounded-pill mb-3">Đang quản lý</span>
                        <h3 class="fw-bold text-dark">{{ $package->name }}</h3>
                        <h2 class="display-5 fw-bold text-primary my-3">{{ number_format($package->price) }}đ</h2>
                        <p class="text-muted">Thời hạn gói: {{ $package->duration_days }} ngày</p>

                        <div class="bg-light rounded-3 p-3 text-start mt-4">
                            <h6 class="fw-bold text-dark mb-3"><i class="fas fa-gift me-2"></i>Quyền lợi gói:</h6>
                            <ul class="list-unstyled mb-0">
                                @foreach ($package->items as $item)
                                    <li class="mb-2 d-flex align-items-center text-secondary">
                                        <i class="fas fa-check-circle text-success me-2"></i>
                                        @if ($item->type == 'top_search')
                                            Đẩy Top Tìm Kiếm
                                        @elseif($item->type == 'featured')
                                            Hiển thị Nổi bật
                                        @elseif($item->type == 'banner')
                                            Banner Hình ảnh
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- CỘT PHẢI: DANH SÁCH SÂN ĐANG CHẠY -->
            <div class="col-lg-8">
                <div class="card shadow-sm border-0 rounded-4">
                    <div class="card-header bg-white py-3 border-0">
                        <h5 class="fw-bold mb-0 text-dark">
                            <i class="fas fa-list-ul me-2 text-primary"></i>Sân đang sử dụng gói này
                        </h5>
                    </div>

                    <div class="card-body p-0">
                        @if ($activeVenues->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="ps-4 py-3">Tên sân</th>
                                            <th class="py-3">Ngày hết hạn</th>
                                            <th class="py-3">Thời gian còn lại</th>
                                            <th class="py-3 text-end pe-4">Hành động</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($activeVenues as $venue)
                                            <tr>
                                                <td class="ps-4">
                                                    <div class="fw-bold text-dark">{{ $venue->name }}</div>
                                                    <small class="text-muted">{{ $venue->address }}</small>
                                                </td>
                                                <td>
                                                    <div class="fw-bold text-dark">
                                                        {{ $venue->real_expiry->format('d/m/Y') }}
                                                    </div>
                                                    <small class="text-muted">
                                                        {{ $venue->real_expiry->format('H:i') }}
                                                    </small>
                                                </td>
                                                <td>
                                                    @if ($venue->days_remaining > 3)
                                                        <span
                                                            class="badge bg-success-subtle text-success px-3 py-2 rounded-pill">
                                                            Còn {{ $venue->days_remaining }} ngày
                                                        </span>
                                                    @else
                                                        <span
                                                            class="badge bg-warning-subtle text-warning px-3 py-2 rounded-pill">
                                                            Sắp hết hạn ({{ $venue->days_remaining }} ngày)
                                                        </span>
                                                    @endif
                                                </td>
                                                <td class="text-end pe-4">
                                                    {{-- Nút gia hạn trỏ về trang Mua nhưng đã chọn sẵn sân này (nếu bạn xử lý logic đó) --}}
                                                    {{-- Hoặc đơn giản trỏ về trang Buy --}}
                                                    <a href="{{ route('owner.packages.buy', $package->id) }}"
                                                        class="btn btn-sm btn-outline-primary fw-bold">
                                                        <i class="fas fa-sync-alt me-1"></i> Gia hạn
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-5">
                                <img src="https://cdn-icons-png.flaticon.com/512/4076/4076432.png" alt="Empty"
                                    width="80" class="opacity-50 mb-3">
                                <p class="text-muted">Chưa có sân nào đang sử dụng gói này.</p>
                                <a href="{{ route('owner.packages.buy', $package->id) }}" class="btn btn-primary">
                                    Đăng ký ngay
                                </a>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Gợi ý mua thêm --}}
                <div class="alert alert-info border-0 rounded-4 mt-4 d-flex align-items-center shadow-sm">
                    <i class="fas fa-info-circle fs-4 me-3"></i>
                    <div>
                        <strong>Mẹo:</strong> Bạn có thể mua gia hạn bất cứ lúc nào. Thời gian sẽ được cộng dồn vào ngày hết
                        hạn hiện tại.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .bg-primary-soft {
            background-color: rgba(13, 110, 253, 0.1);
        }

        .bg-success-subtle {
            background-color: #d1e7dd;
            color: #0f5132;
        }

        .bg-warning-subtle {
            background-color: #fff3cd;
            color: #664d03;
        }
    </style>
@endsection
