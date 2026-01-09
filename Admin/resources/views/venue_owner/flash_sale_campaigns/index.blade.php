@extends('app')

@section('content')
    <div class="container-fluid py-4">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold text-primary mb-0"><i class="bi bi-lightning-charge-fill me-2"></i>QUẢN LÝ FLASH SALE</h4>
                <p class="text-muted small mb-0">Tự tạo chương trình giảm giá cho các sân của bạn</p>
            </div>
            <button type="button" class="btn btn-primary shadow-sm px-4" data-bs-toggle="modal"
                data-bs-target="#createCampaignModal">
                <i class="bi bi-plus-lg me-1"></i> Tạo chiến dịch mới
            </button>
        </div>

        @if (session('success'))
            <div class="alert alert-success border-0 shadow-sm mb-4">{{ session('success') }}</div>
        @endif

        <!-- Danh sách Cards -->
        <div class="row g-4">
            @forelse($flashSaleCampaigns as $campaign)
                @php
                    $now = now();
                    $start = \Carbon\Carbon::parse($campaign->start_datetime);
                    $end = \Carbon\Carbon::parse($campaign->end_datetime);
                    $isLive = $now->between($start, $end);
                    $themeColor = $isLive ? 'danger' : 'info';
                @endphp
                <div class="col-12 col-md-6 col-xl-4">
                    <div class="card h-100 border-0 shadow-sm campaign-card {{ $isLive ? 'border-live' : '' }}">
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-3">
                                <span class="badge bg-{{ $themeColor }} bg-opacity-10 text-white px-3 py-2 rounded-pill">
                                    <i class="bi {{ $isLive ? 'bi-record-circle-fill' : 'bi-clock' }} me-1"></i>
                                    {{ $isLive ? 'Đang diễn ra' : 'Sắp diễn ra' }}
                                </span>
                                <small class="text-muted">#{{ $campaign->id }}</small>
                            </div>

                            <h5 class="fw-bold mb-2">{{ $campaign->name }}</h5>
                            <p class="text-muted small mb-3 text-truncate-2">
                                {{ $campaign->description ?: 'Không có mô tả.' }}</p>

                            <div class="bg-light rounded-3 p-3 mb-3 border">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="small text-muted">Bắt đầu:</span>
                                    <span class="small fw-bold">{{ $start->format('d/m H:i') }}</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span class="small text-muted">Kết thúc:</span>
                                    <span class="small fw-bold">{{ $end->format('d/m H:i') }}</span>
                                </div>
                            </div>

                            <div class="d-grid mt-4">
                                <a href="{{ route('owner.flash_sale_campaigns.show', $campaign->id) }}"
                                    class="btn btn-{{ $themeColor }} fw-bold">
                                    <i class="bi bi-gear-wide-connected me-1"></i> Cấu hình giảm giá sân
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center py-5">
                    <i class="bi bi-calendar2-x display-1 text-light"></i>
                    <p class="text-muted mt-3">Bạn chưa có chiến dịch nào sắp tới.</p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Modal Thêm Mới -->
    <div class="modal fade" id="createCampaignModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title fw-bold">Tạo Chiến Dịch Flash Sale</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                {{-- Form gửi đến controller --}}
                <form action="{{ route('owner.flash_sale_campaigns.store_campaign') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Tên chiến dịch <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                value="{{ old('name') }}" placeholder="VD: Giảm giá giờ vàng tối nay" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Mô tả (không bắt buộc)</label>
                            <textarea name="description" class="form-control" rows="2">{{ old('description') }}</textarea>
                        </div>

                        <div class="row">
                            <div class="col-6 mb-3">
                                <label class="form-label fw-semibold text-success small">Thời gian bắt đầu</label>
                                <input type="datetime-local" name="start_datetime"
                                    class="form-control @error('start_datetime') is-invalid @enderror"
                                    value="{{ old('start_datetime') }}" required>
                                @error('start_datetime')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label fw-semibold text-danger small">Thời gian kết thúc</label>
                                <input type="datetime-local" name="end_datetime"
                                    class="form-control @error('end_datetime') is-invalid @enderror"
                                    value="{{ old('end_datetime') }}" required>
                                @error('end_datetime')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="alert alert-warning py-2 small mb-0 mt-2">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i> Sau khi tạo thành công, hệ thống sẽ tự động
                            chuyển sang trang **Chọn sân giảm giá**.
                        </div>
                    </div>
                    <div class="modal-footer bg-light border-top-0">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Đóng</button>
                        <button type="submit" class="btn btn-primary px-4 fw-bold">Tiếp tục bước 2</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <style>
        .campaign-card {
            transition: all 0.3s ease;
            border-top: 4px solid #dee2e6;
        }

        .campaign-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 1rem 3rem rgba(0, 0, 0, .175) !important;
        }

        .border-live {
            border-top-color: #dc3545 !important;
        }

        .text-truncate-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            height: 38px;
        }
    </style>

    @if ($errors->any())
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var myModal = new bootstrap.Modal(document.getElementById('createCampaignModal'));
                myModal.show();
            });
        </script>
    @endif
@endsection
