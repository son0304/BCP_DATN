@extends('app')
@section('content')
    <style>
        .input-code {
            font-family: 'Monaco', 'Consolas', monospace;
            letter-spacing: 1px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .field-label {
            font-size: 0.75rem;
            text-transform: uppercase;
            font-weight: 700;
            color: #6c757d;
            margin-bottom: 0.5rem;
            display: block;
        }

        .form-control:disabled {
            background-color: #e9ecef;
            opacity: 1;
        }
    </style>

    <div class="container py-5">
        {{-- Hiển thị lỗi --}}
        @if ($errors->any())
            <div class="alert alert-danger shadow-sm border-0 mb-4 rounded-3">
                <div class="fw-bold mb-2">Vui lòng kiểm tra lại dữ liệu:</div>
                <ul class="mb-0 small">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="row justify-content-center">
            <div class="col-lg-10">
                <form action="{{ route('owner.promotions.store') }}" method="POST">
                    @csrf

                    <div class="card border-0 shadow-sm rounded-4">
                        {{-- HEADER --}}
                        <div class="card-header bg-white py-4 border-bottom border-light">
                            <h5 class="fw-bold mb-1 text-dark">Tạo Voucher Mới</h5>
                            <div class="text-muted small">Thiết lập chương trình khuyến mãi mới cho sân của bạn</div>
                        </div>

                        <div class="card-body p-4">
                            {{-- PHẦN 1: THÔNG TIN CƠ BẢN --}}
                            <div class="row g-4 mb-4">
                                <div class="col-md-6">
                                    <label class="field-label">Mã Voucher <span class="text-danger">*</span></label>
                                    <input type="text" name="code" class="form-control input-code"
                                        placeholder="VD: CHAOHE2024" value="{{ old('code') }}" required>
                                    <div class="form-text small">Mã định danh duy nhất (A-Z, 0-9).</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="field-label">Trạng thái khởi tạo</label>
                                    <select name="process_status" class="form-select fw-bold">
                                        <option value="active" selected>Kích hoạt ngay</option>
                                        <option value="disabled">Tạm ẩn (Nháp)</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="field-label">Mô tả chương trình</label>
                                    <textarea name="description" rows="2" class="form-control"
                                        placeholder="VD: Giảm 20k cho khách đặt sân buổi sáng...">{{ old('description') }}</textarea>
                                </div>
                            </div>

                            <hr class="bg-light my-4">

                            {{-- PHẦN 2: GIÁ TRỊ ƯU ĐÃI --}}
                            <div class="row g-4 mb-4">
                                <div class="col-md-4">
                                    <label class="field-label">Loại giảm giá</label>
                                    <select name="type" id="discountType" class="form-select">
                                        <option value="percentage" {{ old('type') == 'percentage' ? 'selected' : '' }}>Theo
                                            phần trăm (%)</option>
                                        <option value="fixed" {{ old('type') == 'fixed' ? 'selected' : '' }}>Số tiền cố
                                            định (VNĐ)</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="field-label">Giá trị giảm <span class="text-danger">*</span></label>
                                    <input type="number" name="value" class="form-control fw-bold"
                                        value="{{ old('value') }}" placeholder="VD: 10 hoặc 50000" required min="0">
                                </div>
                                <div class="col-md-4" id="maxDiscountCol">
                                    <label class="field-label">Giảm tối đa (VNĐ)</label>
                                    <input type="number" name="max_discount_amount" class="form-control"
                                        value="{{ old('max_discount_amount') }}" placeholder="Không giới hạn">
                                    <div class="form-text small" style="font-size: 11px;">Chỉ áp dụng cho giảm theo %</div>
                                </div>
                            </div>

                            <hr class="bg-light my-4">

                            {{-- PHẦN 3: ĐIỀU KIỆN ÁP DỤNG --}}
                            <div class="row g-4 mb-4">
                                <div class="col-md-4">
                                    <label class="field-label">Phạm vi sân áp dụng</label>
                                    <select name="venue_id" class="form-select">
                                        <option value="">Tất cả sân của tôi</option>
                                        @foreach ($venues as $v)
                                            <option value="{{ $v->id }}"
                                                {{ old('venue_id') == $v->id ? 'selected' : '' }}>
                                                {{ $v->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="field-label">Đối tượng khách hàng</label>
                                    <select name="target_user_type" class="form-select">
                                        <option value="all" selected>Tất cả khách hàng</option>
                                        <option value="new_user">Chỉ khách mới (Chưa từng đặt)</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="field-label">Giá trị đơn tối thiểu</label>
                                    <input type="number" name="min_order_value" class="form-control"
                                        value="{{ old('min_order_value') }}" placeholder="0">
                                </div>
                            </div>

                            <hr class="bg-light my-4">

                            {{-- PHẦN 4: GIỚI HẠN & THỜI GIAN --}}
                            <div class="row g-4">
                                <div class="col-md-4">
                                    <div class="d-flex justify-content-between align-items-end mb-2">
                                        <label class="field-label mb-0">Lượt sử dụng tối đa</label>
                                        <div class="form-check form-switch min-h-0 mb-0">
                                            <input class="form-check-input" type="checkbox" id="is_unlimited"
                                                name="is_unlimited" value="1"
                                                {{ old('is_unlimited') ? 'checked' : '' }} style="cursor: pointer;">
                                            <label class="form-check-label small text-muted" for="is_unlimited"
                                                style="cursor: pointer;">Không giới hạn</label>
                                        </div>
                                    </div>
                                    <input type="number" name="usage_limit" id="usage_limit_input" class="form-control"
                                        value="{{ old('usage_limit') }}" placeholder="Nhập số lượng..." min="1">
                                </div>

                                <div class="col-md-4">
                                    <label class="field-label">Thời gian bắt đầu</label>
                                    <input type="datetime-local" name="start_at" class="form-control"
                                        value="{{ old('start_at', now()->format('Y-m-d\TH:i')) }}" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="field-label">Thời gian kết thúc</label>
                                    <input type="datetime-local" name="end_at" class="form-control"
                                        value="{{ old('end_at') }}" required>
                                </div>
                            </div>
                        </div>

                        {{-- FOOTER --}}
                        <div class="card-footer bg-light py-3 border-top border-light">
                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('owner.promotions.index') }}" class="btn btn-white border px-4">Quay
                                    lại</a>
                                <button type="submit" class="btn btn-primary px-5 fw-bold shadow-sm">Tạo Voucher</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Script xử lý logic --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // 1. Xử lý hiển thị ô Giảm tối đa
            const discountType = document.getElementById('discountType');
            const maxDiscountCol = document.getElementById('maxDiscountCol');

            function toggleMax() {
                if (discountType.value === 'percentage') {
                    maxDiscountCol.style.display = 'block';
                } else {
                    maxDiscountCol.style.display = 'none';
                    document.querySelector('input[name="max_discount_amount"]').value = '';
                }
            }

            if (discountType) {
                discountType.addEventListener('change', toggleMax);
                toggleMax();
            }

            // 2. Xử lý Switch Không giới hạn
            const checkUnlimited = document.getElementById('is_unlimited');
            const inputLimit = document.getElementById('usage_limit_input');

            function toggleLimit() {
                if (checkUnlimited.checked) {
                    inputLimit.value = '';
                    inputLimit.disabled = true;
                    inputLimit.placeholder = "∞ Vô hạn";
                } else {
                    inputLimit.disabled = false;
                    inputLimit.placeholder = "Nhập số lượng...";
                }
            }

            if (checkUnlimited) {
                checkUnlimited.addEventListener('change', toggleLimit);
                toggleLimit();
            }
        });
    </script>
@endsection
