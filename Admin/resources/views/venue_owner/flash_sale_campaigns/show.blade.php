@extends('app')

@section('content')
    <div class="main-content bg-light pb-5" style="min-height: 100vh;">
        {{-- HEADER --}}
        <div class="bg-white border-bottom " style="z-index: 100;">
            <div class="container-fluid px-4 py-3 d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-3">
                    <a href="{{ route('owner.flash_sale_campaigns.index') }}"
                        class="btn btn-light btn-sm rounded-circle border">
                        <i class="bi bi-arrow-left"></i>
                    </a>
                    <div>
                        <h5 class="mb-0 fw-bold">Bước 2: Chọn sân giảm giá</h5>
                        <small class="text-muted">{{ $campaign->name }}</small>
                    </div>
                </div>
                <button type="button" class="btn btn-primary fw-bold px-4 shadow-sm"
                    onclick="document.getElementById('flashSaleForm').submit()">
                    <i class="bi bi-check-lg me-1"></i> Lưu thiết lập
                </button>
            </div>
        </div>

        <div class="container-fluid px-4 py-4">
            <form action="{{ route('owner.flash_sale_campaigns.store') }}" method="POST" id="flashSaleForm">
                @csrf
                <input type="hidden" name="campaign_id" value="{{ $campaign->id }}">

                <div class="row g-4">
                    <div class="col-lg-9">
                        {{-- NHẬP GIÁ CHUNG --}}
                        <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden">
                            <div class="card-body p-4 bg-white">
                                <div class="row align-items-center">
                                    <div class="col-md-7">
                                        <label class="form-label fw-bold text-dark">Giá Sale đồng giá (VNĐ) <span
                                                class="text-danger">*</span></label>
                                        <p class="text-muted small mb-0">Tất cả các khung giờ bạn tích chọn bên dưới sẽ được
                                            bán với mức giá này.</p>
                                    </div>
                                    <div class="col-md-5 mt-3 mt-md-0">
                                        <div class="input-group input-group-lg">
                                            <span class="input-group-text bg-light border-0">₫</span>
                                            <input type="number" name="sale_price" id="globalSalePrice"
                                                class="form-control bg-light border-0 fw-bold text-primary"
                                                value="{{ old('sale_price', $oldPrice) }}" placeholder="Nhập giá..."
                                                required>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- DANH SÁCH SÂN --}}
                        @forelse ($groupedAvailabilities as $venueName => $courts)
                            <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden">
                                <div class="card-header bg-white py-3 px-4">
                                    <h6 class="mb-0 fw-bold text-primary"><i
                                            class="bi bi-geo-alt-fill me-2"></i>{{ $venueName }}</h6>
                                </div>
                                <div class="card-body p-0 d-flex flex-column flex-md-row">
                                    <div class="nav flex-column nav-pills p-3 bg-light border-end"
                                        style="min-width: 200px;">
                                        @foreach ($courts as $courtName => $slots)
                                            <button
                                                class="nav-link text-start {{ $loop->first ? 'active' : '' }} mb-2 fw-bold small d-flex justify-content-between align-items-center"
                                                data-bs-toggle="pill"
                                                data-bs-target="#tab-{{ Str::slug($venueName . '-' . $courtName) }}"
                                                type="button">
                                                {{ $courtName }}
                                                <span class="badge bg-white text-dark shadow-sm">{{ count($slots) }}</span>
                                            </button>
                                        @endforeach
                                    </div>
                                    <div class="tab-content w-100 p-4">
                                        @foreach ($courts as $courtName => $slots)
                                            <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}"
                                                id="tab-{{ Str::slug($venueName . '-' . $courtName) }}">
                                                <div class="d-flex justify-content-between align-items-center mb-3">
                                                    <span class="text-muted small fw-bold text-uppercase">Chọn giờ áp
                                                        dụng</span>
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input select-all-court cursor-pointer"
                                                            type="checkbox">
                                                        <label class="form-check-label small text-muted">Chọn tất cả sân
                                                            {{ $courtName }}</label>
                                                    </div>
                                                </div>
                                                <div class="slot-grid">
                                                    @foreach ($slots as $slot)
                                                        <label class="slot-item">
                                                            <input type="checkbox" name="availability_ids[]"
                                                                value="{{ $slot->id }}" class="slot-checkbox d-none"
                                                                {{ in_array($slot->id, $joinedIds ?? []) ? 'checked' : '' }}>
                                                            <div class="slot-box">
                                                                <div class="time fw-bold">
                                                                    {{ substr($slot->timeSlot->start_time, 0, 5) }}</div>
                                                                <div class="date text-muted" style="font-size: 0.7rem">
                                                                    {{ \Carbon\Carbon::parse($slot->date)->format('d/m') }}
                                                                </div>
                                                                <div class="price-box mt-1">
                                                                    <span
                                                                        class="old text-muted text-decoration-line-through">{{ number_format($slot->price / 1000) }}k</span>
                                                                    <span
                                                                        class="new sale-preview fw-bold text-danger ms-1">--</span>
                                                                </div>
                                                                <div class="check-mark"><i
                                                                        class="bi bi-check-circle-fill"></i></div>
                                                            </div>
                                                        </label>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="card border-0 shadow-sm rounded-4 p-5 text-center">
                                <i class="bi bi-calendar-x display-4 text-light mb-3"></i>
                                <h5 class="text-muted">Không có sân nào còn trống trong khung giờ này</h5>
                                <p class="small text-muted">Các khung giờ đã qua hoặc đã có khách đặt sẽ bị ẩn.</p>
                            </div>
                        @endforelse
                    </div>

                    {{-- SIDEBAR --}}
                    <div class="col-lg-3">
                        <div class="" style="top: 6rem;">
                            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-3">
                                <div class="card-header bg-dark text-white py-3 px-4">
                                    <h6 class="mb-0 fw-bold">Tóm tắt</h6>
                                </div>
                                <div class="card-body p-4">
                                    <div class="mb-3">
                                        <div class="small text-muted mb-1">Thời gian Flash Sale:</div>
                                        <div class="fw-bold small">
                                            {{ \Carbon\Carbon::parse($campaign->start_datetime)->format('H:i d/m') }} -
                                            {{ \Carbon\Carbon::parse($campaign->end_datetime)->format('H:i d/m') }}</div>
                                    </div>
                                    <div class="d-flex justify-content-between py-2 border-top">
                                        <span class="small text-muted">Số sân đã chọn:</span>
                                        <span class="fw-bold" id="totalCount">0</span>
                                    </div>
                                    <div class="d-flex justify-content-between py-2 mb-3">
                                        <span class="small text-muted">Giá Sale:</span>
                                        <span class="fw-bold text-primary" id="pricePreview">--</span>
                                    </div>
                                    <button type="submit" class="btn btn-primary w-100 py-2 fw-bold shadow" id="btnSubmit"
                                        disabled>Xác nhận & Lưu</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <style>
        .slot-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(90px, 1fr));
            gap: 12px;
        }

        .slot-box {
            background: white;
            border: 1.5px solid #eee;
            border-radius: 10px;
            padding: 10px 5px;
            text-align: center;
            position: relative;
            transition: all 0.2s ease;
            cursor: pointer;
        }

        .slot-box:hover {
            border-color: #ddd;
            background: #fcfcfc;
        }

        .slot-checkbox:checked+.slot-box {
            border-color: #0d6efd;
            background-color: #eef6ff;
            box-shadow: 0 4px 10px rgba(13, 110, 253, 0.1);
        }

        .check-mark {
            position: absolute;
            top: -8px;
            right: -8px;
            color: #0d6efd;
            background: white;
            border-radius: 50%;
            display: none;
            font-size: 1.1rem;
        }

        .slot-checkbox:checked+.slot-box .check-mark {
            display: block;
        }

        .slot-checkbox:checked+.slot-box .time {
            color: #0d6efd;
        }

        .nav-pills .nav-link {
            color: #555;
            border-radius: 8px;
        }

        .nav-pills .nav-link.active {
            background-color: #0d6efd;
            box-shadow: 0 4px 10px rgba(13, 110, 253, 0.3);
        }

        .cursor-pointer {
            cursor: pointer;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const priceInput = document.getElementById('globalSalePrice');
            const checkboxes = document.querySelectorAll('.slot-checkbox');
            const totalCount = document.getElementById('totalCount');
            const pricePreview = document.getElementById('pricePreview');
            const btnSubmit = document.getElementById('btnSubmit');

            const updateUI = () => {
                const price = parseFloat(priceInput.value) || 0;
                let checkedCount = 0;

                pricePreview.textContent = price > 0 ? new Intl.NumberFormat('vi-VN').format(price) + 'đ' :
                '--';

                checkboxes.forEach(cb => {
                    const preview = cb.closest('.slot-item').querySelector('.sale-preview');
                    preview.textContent = price > 0 ? (price / 1000) + 'k' : '--';
                    if (cb.checked) checkedCount++;
                });

                totalCount.textContent = checkedCount;
                btnSubmit.disabled = !(price > 0 && checkedCount > 0);
            };

            document.querySelectorAll('.select-all-court').forEach(sw => {
                sw.addEventListener('change', function() {
                    this.closest('.tab-pane').querySelectorAll('.slot-checkbox').forEach(cb => cb
                        .checked = this.checked);
                    updateUI();
                });
            });

            priceInput.addEventListener('input', updateUI);
            checkboxes.forEach(cb => cb.addEventListener('change', updateUI));
            updateUI(); // Khởi tạo nếu có dữ liệu cũ
        });
    </script>
@endsection
