@extends('app')
{{-- Thay 'layouts.app' bằng layout admin của bạn (ví dụ: 'admin.layout' hoặc 'venue.layout') --}}

@section('content')
    <div class="container-fluid py-4">
        <!-- Header: Tiêu đề & Nút thêm mới -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-0 text-uppercase text-primary">
                    <i class="bi bi-lightning-fill"></i> Quản lý Flash Sale
                </h4>
                <p class="text-muted small mb-0">Danh sách các chiến dịch khuyến mãi hiện có</p>
            </div>


        </div>

        <!-- Grid Card System -->
        <div class="row g-4">
            @forelse($flashSaleCampaigns as $campaign)
                <div class="col-12 col-md-6 col-xl-4">
                    <div class="card h-100 border-0 shadow-sm campaign-card position-relative">

                        <!-- Dải màu trạng thái bên trái -->
                        @php
                            $statusColor = match ($campaign->status) {
                                'active' => 'success', // Xanh lá
                                'pending' => 'warning', // Vàng
                                'expired' => 'secondary', // Xám
                                'cancelled' => 'danger', // Đỏ
                                default => 'primary',
                            };
                        @endphp
                        <div class="status-indicator bg-{{ $statusColor }}"></div>

                        <div class="card-body">
                            <!-- ID & Status Badge -->
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <span class="text-muted small fw-bold">#{{ $campaign->id }}</span>
                                <span
                                    class="badge bg-{{ $statusColor }} bg-opacity-10 text-{{ $statusColor }} border border-{{ $statusColor }} rounded-pill px-3">
                                    {{ ucfirst($campaign->status) }}
                                </span>
                            </div>

                            <!-- Name -->
                            <h5 class="card-title fw-bold text-dark text-truncate" title="{{ $campaign->name }}">
                                {{ $campaign->name }}
                            </h5>

                            <!-- Description -->
                            <p class="card-text text-muted small mb-3" style="min-height: 40px;">
                                {{ Str::limit($campaign->description, 80, '...') }}
                            </p>

                            <!-- Timeline Block -->
                            <div class="bg-light rounded p-2 mb-3 border border-light">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="bi bi-calendar-check text-success me-2 fs-5"></i>
                                    <div class="lh-1">
                                        <span class="d-block small text-muted">Bắt đầu</span>
                                        <span class="fw-semibold text-dark small">
                                            {{ \Carbon\Carbon::parse($campaign->start_datetime)->format('d/m/Y H:i') }}
                                        </span>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-calendar-x text-danger me-2 fs-5"></i>
                                    <div class="lh-1">
                                        <span class="d-block small text-muted">Kết thúc</span>
                                        <span class="fw-semibold text-dark small">
                                            {{ \Carbon\Carbon::parse($campaign->end_datetime)->format('d/m/Y H:i') }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Footer Actions -->
                        <div
                            class="card-footer bg-white border-top-0 pt-0 pb-3 d-flex justify-content-between align-items-center">
                            <small class="text-muted fst-italic" style="font-size: 0.75rem">
                                Cập nhật: {{ $campaign->updated_at->diffForHumans() }}
                            </small>

                            <div class="btn-group">
                                <a href="{{ route('owner.flash_sale_campaigns.show', $campaign->id) }}">
                                    <button class="btn-primary rounded">Tham gia ngay</button>
                                </a>



                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <!-- Giao diện khi không có dữ liệu -->
                <div class="col-12 text-center py-5">
                    <div class="text-muted mb-3">
                        <i class="bi bi-inbox fs-1"></i>
                    </div>
                    <h5>Chưa có chiến dịch Flash Sale nào</h5>
                    <p class="text-muted">Hãy tạo chiến dịch đầu tiên ngay bây giờ.</p>
                    <a href="{{-- route('flash_sale_campaigns.create') --}}" class="btn btn-primary">
                        Tạo mới ngay
                    </a>
                </div>
            @endforelse
        </div>
    </div>

    <!-- CSS Bổ sung (Inline hoặc đưa vào file .css) -->
    <style>
        .campaign-card {
            transition: transform 0.2s, box-shadow 0.2s;
            overflow: hidden;
            /* Để bo góc cho dải màu */
        }

        .campaign-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1) !important;
        }

        /* Dải màu dọc bên trái */
        .status-indicator {
            position: absolute;
            top: 0;
            left: 0;
            bottom: 0;
            width: 5px;
            z-index: 1;
        }

        .card-body {
            padding-left: 1.5rem;
            /* Cách ra để không đè lên dải màu */
        }
    </style>
@endsection
