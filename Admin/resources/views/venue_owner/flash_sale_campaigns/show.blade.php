@extends('app')

@section('content')
<div class="main-content bg-light pb-5" style="min-height: 100vh;">

    {{-- 1. HEADER SECTION --}}
    <div class="bg-white border-bottom sticky-top" style="z-index: 100;">
        <div class="container-fluid px-4 py-3">
            <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-3">
                    <a href="{{ route('owner.flash_sale_campaigns.index') }}"
                       class="btn btn-light btn-sm rounded-circle shadow-sm border d-flex align-items-center justify-content-center"
                       style="width: 40px; height: 40px;">
                        <i class="bi bi-arrow-left text-dark"></i>
                    </a>
                    <div>
                        <h5 class="mb-0 fw-bold text-dark">Thiết lập Flash Sale</h5>
                        <div class="text-muted" style="font-size: 0.8rem;">
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle me-1">Bước 2/2</span>
                            {{ $campaign->name }}
                        </div>
                    </div>
                </div>

                {{-- Nút Save ở Header (Mobile View) --}}
                <div class="d-md-none">
                     <button type="button" class="btn btn-primary btn-sm fw-bold" onclick="document.getElementById('flashSaleForm').submit()">
                        <i class="bi bi-check-lg"></i> Lưu
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid px-4 py-4">
        <form action="{{ route('owner.flash_sale_campaigns.store') }}" method="POST" id="flashSaleForm">
            @csrf
            <input type="hidden" name="campaign_id" value="{{ $campaign->id }}">

            <div class="row g-4">

                {{-- 2. LEFT COLUMN: INPUT & SLOTS --}}
                <div class="col-lg-9 order-2 order-lg-1">

                    {{-- INPUT GIÁ (Nổi bật) --}}
                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-body p-4">
                            <div class="d-flex flex-column flex-md-row align-items-md-center gap-4">
                                <div class="flex-grow-1">
                                    <label for="globalSalePrice" class="form-label fw-bold text-dark mb-1">
                                        Nhập giá Sale mong muốn <span class="text-danger">*</span>
                                    </label>
                                    <div class="text-muted small mb-2">Giá này sẽ áp dụng cho tất cả các khung giờ được chọn bên dưới.</div>
                                </div>
                                <div class="position-relative" style="min-width: 250px;">
                                    <span class="position-absolute top-50 start-0 translate-middle-y ms-3 text-muted fw-bold">₫</span>
                                    <input type="number"
                                           class="form-control form-control-lg bg-light border-0 fw-bold text-primary ps-4"
                                           style="font-size: 1.5rem;"
                                           name="sale_price"
                                           id="globalSalePrice"
                                           placeholder="0"
                                           required>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- LIST VENUES & COURTS --}}
                    @if($groupedAvailabilities->isEmpty())
                        <div class="text-center py-5">
                            <img src="https://cdn-icons-png.flaticon.com/512/7486/7486747.png" width="80" class="opacity-50 mb-3" alt="">
                            <h6 class="text-muted fw-bold">Không tìm thấy lịch trống trong khung giờ chiến dịch</h6>
                        </div>
                    @else
                        @foreach ($groupedAvailabilities as $venueName => $courts)
                            <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden">
                                {{-- Venue Header --}}
                                <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="avatar-sm bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center" style="width:32px; height:32px">
                                            <i class="bi bi-building-fill"></i>
                                        </div>
                                        <h6 class="mb-0 fw-bold text-dark">{{ $venueName }}</h6>
                                    </div>
                                    <span class="badge bg-light text-muted border">{{ count($courts) }} sân</span>
                                </div>

                                <div class="card-body p-0">
                                    {{-- Vertical Tabs Layout --}}
                                    <div class="d-flex align-items-start">

                                        {{-- Court List (Tabs Left) --}}
                                        <div class="nav flex-column nav-pills me-3 bg-light h-100 p-3"
                                             id="v-pills-tab-{{ Str::slug($venueName) }}"
                                             role="tablist"
                                             aria-orientation="vertical"
                                             style="min-width: 160px; border-right: 1px solid #eee;">

                                            @foreach ($courts as $courtName => $slots)
                                                <button class="nav-link text-start {{ $loop->first ? 'active' : '' }} mb-2 rounded-3 small fw-bold d-flex justify-content-between align-items-center"
                                                        id="v-pills-{{ Str::slug($venueName.'-'.$courtName) }}-tab"
                                                        data-bs-toggle="pill"
                                                        data-bs-target="#v-pills-{{ Str::slug($venueName.'-'.$courtName) }}"
                                                        type="button" role="tab">
                                                    {{ $courtName }}
                                                    <span class="badge bg-white text-secondary shadow-sm" style="font-size: 0.65rem;">{{ count($slots) }}</span>
                                                </button>
                                            @endforeach
                                        </div>

                                        {{-- Slots Grid (Content Right) --}}
                                        <div class="tab-content w-100 p-4" id="v-pills-tabContent-{{ Str::slug($venueName) }}">
                                            @foreach ($courts as $courtName => $slots)
                                                <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}"
                                                     id="v-pills-{{ Str::slug($venueName.'-'.$courtName) }}"
                                                     role="tabpanel">

                                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                                        <span class="text-muted small text-uppercase fw-bold">Chọn khung giờ</span>
                                                        <div class="form-check form-switch">
                                                            <input class="form-check-input select-all-court cursor-pointer" type="checkbox" id="selectAll-{{ Str::slug($venueName.'-'.$courtName) }}">
                                                            <label class="form-check-label small text-muted cursor-pointer" for="selectAll-{{ Str::slug($venueName.'-'.$courtName) }}">Chọn tất cả</label>
                                                        </div>
                                                    </div>

                                                    {{-- COMPACT GRID --}}
                                                    <div class="slot-grid">
                                                        @foreach ($slots as $slot)
                                                            <label class="slot-compact-item position-relative cursor-pointer">
                                                                <input type="checkbox"
                                                                       class="slot-checkbox opacity-0 position-absolute"
                                                                       name="availability_ids[]"
                                                                       value="{{ $slot->id }}"
                                                                       data-original="{{ $slot->price }}">

                                                                <div class="slot-box transition-all">
                                                                    {{-- Time --}}
                                                                    <div class="slot-time">
                                                                        {{ substr($slot->timeSlot->start_time, 0, 5) }}
                                                                    </div>
                                                                    {{-- Date --}}
                                                                    <div class="slot-date">
                                                                        {{ \Carbon\Carbon::parse($slot->date)->format('d/m') }}
                                                                    </div>
                                                                    {{-- Price Info --}}
                                                                    <div class="d-flex align-items-center justify-content-center gap-1 mt-1">
                                                                        <span class="slot-price-original">{{ number_format($slot->price/1000) }}k</span>
                                                                        <i class="bi bi-arrow-right text-muted" style="font-size: 8px;"></i>
                                                                        <span class="slot-price-sale sale-preview">--</span>
                                                                    </div>

                                                                    {{-- Check Icon --}}
                                                                    <div class="check-icon">
                                                                        <i class="bi bi-check2"></i>
                                                                    </div>
                                                                </div>
                                                            </label>
                                                        @endforeach
                                                    </div>

                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>

                {{-- 3. RIGHT COLUMN: SUMMARY SIDEBAR (STICKY) --}}
                <div class="col-lg-3 order-1 order-lg-2">
                    <div class="sticky-top" style="top: 6rem;">
                        <div class="card border-0 shadow rounded-4 overflow-hidden">
                            <div class="card-header bg-white border-bottom pt-4 pb-3 px-4">
                                <h6 class="fw-bold mb-0 text-dark">Tổng quan chiến dịch</h6>
                            </div>
                            <div class="card-body p-4 bg-white">

                                {{-- Campaign Time --}}
                                <div class="timeline-simple mb-4">
                                    <div class="timeline-item">
                                        <div class="timeline-marker bg-success"></div>
                                        <div class="timeline-content">
                                            <span class="d-block text-muted small">Bắt đầu</span>
                                            <span class="fw-bold text-dark">{{ \Carbon\Carbon::parse($campaign->start_datetime)->format('H:i - d/m/Y') }}</span>
                                        </div>
                                    </div>
                                    <div class="timeline-item">
                                        <div class="timeline-marker bg-danger"></div>
                                        <div class="timeline-content">
                                            <span class="d-block text-muted small">Kết thúc</span>
                                            <span class="fw-bold text-dark">{{ \Carbon\Carbon::parse($campaign->end_datetime)->format('H:i - d/m/Y') }}</span>
                                        </div>
                                    </div>
                                </div>

                                <hr class="border-light">

                                {{-- Summary Numbers --}}
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="text-secondary">Slot đã chọn</span>
                                    <span class="fw-bolder fs-5 text-dark" id="totalSelectedCount">0</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <span class="text-secondary">Giá Sale</span>
                                    <span class="fw-bolder fs-5 text-primary" id="sidebarPricePreview">--</span>
                                </div>

                                <button type="submit" class="btn btn-primary w-100 py-2 rounded-3 fw-bold shadow-sm" id="submitBtn" disabled>
                                    Xác nhận & Lưu
                                </button>
                            </div>
                            <div class="card-footer bg-light p-3 text-center">
                                <small class="text-muted" style="font-size: 0.75rem;">Vui lòng kiểm tra kỹ giá trước khi lưu</small>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </form>
    </div>
</div>

{{-- CSS SECTION --}}
<style>
    /* CSS Grid Layout cho Slot nhỏ gọn */
    .slot-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(90px, 1fr)); /* Tự động chia cột, tối thiểu 90px */
        gap: 0.75rem;
    }

    /* Slot Box Styling */
    .slot-box {
        background: #fff;
        border: 1px solid #e9ecef;
        border-radius: 8px;
        padding: 0.5rem 0.25rem;
        text-align: center;
        user-select: none;
        position: relative;
        overflow: hidden;
    }

    .slot-time {
        font-weight: 700;
        font-size: 0.9rem;
        color: #343a40;
        line-height: 1.2;
    }

    .slot-date {
        font-size: 0.7rem;
        color: #adb5bd;
    }

    .slot-price-original {
        font-size: 0.7rem;
        text-decoration: line-through;
        color: #adb5bd;
    }

    .slot-price-sale {
        font-size: 0.75rem;
        font-weight: 700;
        color: #adb5bd; /* Mặc định xám */
    }

    /* Hover Effect */
    .slot-compact-item:hover .slot-box {
        border-color: #dee2e6;
        background-color: #f8f9fa;
        transform: translateY(-2px);
        box-shadow: 0 4px 6px rgba(0,0,0,0.05);
    }

    /* Checked State */
    .slot-checkbox:checked + .slot-box {
        background-color: #ebf5ff; /* Xanh nhạt */
        border-color: #3b82f6;     /* Viền xanh */
        box-shadow: 0 0 0 1px #3b82f6;
    }

    .slot-checkbox:checked + .slot-box .slot-time {
        color: #1c4ed8;
    }

    .slot-checkbox:checked + .slot-box .slot-price-sale {
        color: #dc2626; /* Màu đỏ cho giá sale khi selected */
    }

    /* Check Icon (Góc trên phải) */
    .check-icon {
        position: absolute;
        top: -2px;
        right: -2px;
        background: #3b82f6;
        color: white;
        width: 16px;
        height: 16px;
        font-size: 10px;
        border-bottom-left-radius: 6px;
        display: none;
        align-items: center;
        justify-content: center;
    }

    .slot-checkbox:checked + .slot-box .check-icon {
        display: flex;
    }

    /* Timeline Styling Sidebar */
    .timeline-simple {
        position: relative;
        padding-left: 1rem;
    }
    .timeline-simple::before {
        content: '';
        position: absolute;
        left: 6px;
        top: 5px;
        bottom: 5px;
        width: 2px;
        background: #f1f5f9;
    }
    .timeline-item {
        position: relative;
        margin-bottom: 1rem;
        padding-left: 1rem;
    }
    .timeline-item:last-child {
        margin-bottom: 0;
    }
    .timeline-marker {
        position: absolute;
        left: -1rem; /* Adjust based on parent padding */
        left: -5px;
        top: 5px;
        width: 10px;
        height: 10px;
        border-radius: 50%;
        border: 2px solid #fff;
        box-shadow: 0 0 0 1px #e2e8f0;
        z-index: 1;
    }

    /* Custom Scrollbar for Nav Pills */
    .nav-pills::-webkit-scrollbar {
        width: 4px;
    }
    .nav-pills::-webkit-scrollbar-thumb {
        background-color: #dee2e6;
        border-radius: 4px;
    }

    /* Nav Link Custom */
    .nav-pills .nav-link {
        color: #495057;
        transition: all 0.2s;
    }
    .nav-pills .nav-link:hover {
        background-color: #e9ecef;
    }
    .nav-pills .nav-link.active {
        background-color: #3b82f6;
        color: white;
        box-shadow: 0 4px 6px -1px rgba(59, 130, 246, 0.5);
    }
    .nav-pills .nav-link.active .badge {
        color: #3b82f6 !important;
    }

    .cursor-pointer { cursor: pointer; }
    .transition-all { transition: all 0.2s ease; }
</style>

{{-- JAVASCRIPT LOGIC (Giữ nguyên logic cũ, chỉ cập nhật selector) --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const salePriceInput = document.getElementById('globalSalePrice');
        const checkboxes = document.querySelectorAll('.slot-checkbox');
        const totalCountSpan = document.getElementById('totalSelectedCount');
        const sidebarPricePreview = document.getElementById('sidebarPricePreview');
        const submitBtn = document.getElementById('submitBtn');
        const selectAllCheckboxes = document.querySelectorAll('.select-all-court');

        // Format tiền rút gọn (50k)
        const formatMoneyShort = (amount) => (amount / 1000) + 'k';
        // Format tiền đầy đủ (50.000đ)
        const formatMoneyFull = (amount) => new Intl.NumberFormat('vi-VN').format(amount) + 'đ';

        // Hàm cập nhật giao diện
        const updateUI = () => {
            const salePrice = parseFloat(salePriceInput.value) || 0;
            let checkedCount = 0;

            // Update text giá bên sidebar
            sidebarPricePreview.textContent = salePrice > 0 ? formatMoneyFull(salePrice) : '--';

            // Loop qua tất cả các slot để update hiển thị
            checkboxes.forEach(cb => {
                const card = cb.nextElementSibling; // div.slot-box
                const previewEl = card.querySelector('.sale-preview');

                if (salePrice > 0) {
                    previewEl.textContent = formatMoneyShort(salePrice);
                    // Nếu đã chọn thì màu đỏ, chưa chọn thì vẫn xám nhạt (nhưng có số)
                    if(cb.checked) {
                         previewEl.style.color = '#dc2626';
                    } else {
                         previewEl.style.color = '#6c757d';
                    }
                } else {
                    previewEl.textContent = '--';
                    previewEl.style.color = '#adb5bd';
                }

                if (cb.checked) checkedCount++;
            });

            // Update tổng số lượng
            totalCountSpan.textContent = checkedCount;

            // Validate nút Submit
            submitBtn.disabled = !(salePrice > 0 && checkedCount > 0);
        };

        // Logic "Chọn tất cả" (theo từng Tab/Sân riêng biệt)
        selectAllCheckboxes.forEach(selectAll => {
            selectAll.addEventListener('change', function() {
                // Tìm tab-pane chứa nút này
                const tabPane = this.closest('.tab-pane');
                // Tìm tất cả checkbox con TRONG TAB ĐÓ thôi
                const children = tabPane.querySelectorAll('.slot-checkbox');

                children.forEach(cb => {
                    cb.checked = this.checked;
                });
                updateUI();
            });
        });

        // Event Listeners
        salePriceInput.addEventListener('input', updateUI);
        checkboxes.forEach(cb => {
            cb.addEventListener('change', updateUI);
        });
    });
</script>

@endsection
