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

        .invalid-feedback {
            font-weight: 500;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.1);
        }
    </style>

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <form action="{{ route('owner.promotions.store') }}" method="POST">
                    @csrf

                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
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
                                    <input type="text" name="code"
                                        class="form-control input-code @error('code') is-invalid @enderror"
                                        placeholder="VD: CHAOHE2024" value="{{ old('code') }}">
                                    @error('code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text small">Mã viết liền, không dấu (A-Z, 0-9).</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="field-label">Trạng thái khởi tạo</label>
                                    <select name="process_status"
                                        class="form-select fw-bold @error('process_status') is-invalid @enderror">
                                        <option value="active" {{ old('process_status') == 'active' ? 'selected' : '' }}>
                                            Kích hoạt ngay</option>
                                        <option value="disabled"
                                            {{ old('process_status') == 'disabled' ? 'selected' : '' }}>Tạm ẩn (Nháp)
                                        </option>
                                    </select>
                                    @error('process_status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-12">
                                    <label class="field-label">Mô tả chương trình</label>
                                    <textarea name="description" rows="2" class="form-control @error('description') is-invalid @enderror"
                                        placeholder="VD: Giảm 20k cho khách đặt sân buổi sáng...">{{ old('description') }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <hr class="bg-light my-4">

                            {{-- PHẦN 2: GIÁ TRỊ ƯU ĐÃI --}}
                            <div class="row g-4 mb-4">
                                <div class="col-md-4">
                                    <label class="field-label">Loại giảm giá</label>
                                    <select name="type" id="discountType"
                                        class="form-select @error('type') is-invalid @enderror">
                                        <option value="percentage" {{ old('type') == 'percentage' ? 'selected' : '' }}>Theo
                                            phần trăm (%)</option>
                                        <option value="fixed" {{ old('type') == 'fixed' ? 'selected' : '' }}>Số tiền cố
                                            định (VNĐ)</option>
                                    </select>
                                    @error('type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="field-label">Giá trị giảm <span class="text-danger">*</span></label>
                                    <input type="number" name="value"
                                        class="form-control fw-bold @error('value') is-invalid @enderror"
                                        value="{{ old('value') }}" placeholder="VD: 10 hoặc 50000">
                                    @error('value')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4" id="maxDiscountCol">
                                    <label class="field-label">Giảm tối đa (VNĐ)</label>
                                    <input type="number" name="max_discount_amount"
                                        class="form-control @error('max_discount_amount') is-invalid @enderror"
                                        value="{{ old('max_discount_amount') }}" placeholder="Không giới hạn">
                                    @error('max_discount_amount')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text small" style="font-size: 11px;">Chỉ áp dụng khi chọn giảm theo %
                                    </div>
                                </div>
                            </div>

                            <hr class="bg-light my-4">

                            {{-- PHẦN 3: ĐIỀU KIỆN ÁP DỤNG --}}
                            <div class="row g-4 mb-4">
                                <div class="col-md-4">
                                    <label class="field-label">Phạm vi sân áp dụng</label>
                                    <select name="venue_id" class="form-select @error('venue_id') is-invalid @enderror">
                                        <option value="">Tất cả sân của tôi</option>
                                        @foreach ($venues as $v)
                                            <option value="{{ $v->id }}"
                                                {{ old('venue_id') == $v->id ? 'selected' : '' }}>{{ $v->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('venue_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="field-label">Đối tượng khách hàng</label>
                                    <select name="target_user_type"
                                        class="form-select @error('target_user_type') is-invalid @enderror">
                                        <option value="all" {{ old('target_user_type') == 'all' ? 'selected' : '' }}>Tất
                                            cả khách hàng</option>
                                        <option value="new_user"
                                            {{ old('target_user_type') == 'new_user' ? 'selected' : '' }}>Chỉ khách mới
                                        </option>
                                    </select>
                                    @error('target_user_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="field-label">Giá trị đơn tối thiểu (VNĐ)</label>
                                    <input type="number" name="min_order_value"
                                        class="form-control @error('min_order_value') is-invalid @enderror"
                                        value="{{ old('min_order_value', 0) }}">
                                    @error('min_order_value')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <hr class="bg-light my-4">

                            {{-- PHẦN 4: GIỚI HẠN & THỜI GIAN --}}
                            <div class="row g-4">
                                <div class="col-md-4">
                                    <div class="d-flex justify-content-between align-items-end mb-2">
                                        <label class="field-label mb-0">Lượt sử dụng tối đa</label>
                                        <div class="form-check form-switch min-h-0 mb-0">
                                            {{-- Mặc định là checked nếu không có old dữ liệu (vì hệ thống thường để vô hạn) --}}
                                            <input class="form-check-input" type="checkbox" id="is_unlimited"
                                                name="is_unlimited" value="1"
                                                {{ old('is_unlimited', '1') == '1' ? 'checked' : '' }}
                                                style="cursor: pointer;">
                                            <label class="form-check-label small text-muted" for="is_unlimited"
                                                style="cursor: pointer;">Không giới hạn</label>
                                        </div>
                                    </div>
                                    <input type="number" name="usage_limit" id="usage_limit_input"
                                        class="form-control @error('usage_limit') is-invalid @enderror"
                                        value="{{ old('usage_limit') }}"
                                        {{ old('is_unlimited', '1') == '1' ? 'disabled' : '' }}
                                        placeholder="{{ old('is_unlimited', '1') == '1' ? '∞ Vô hạn' : 'Nhập số lượng...' }}">
                                    @error('usage_limit')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4">
                                    <label class="field-label">Thời gian bắt đầu</label>
                                    <input type="datetime-local" name="start_at"
                                        class="form-control @error('start_at') is-invalid @enderror"
                                        value="{{ old('start_at', now()->format('Y-m-d\TH:i')) }}">
                                    @error('start_at')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="field-label">Thời gian kết thúc</label>
                                    <input type="datetime-local" name="end_at"
                                        class="form-control @error('end_at') is-invalid @enderror"
                                        value="{{ old('end_at') }}">
                                    @error('end_at')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- FOOTER --}}
                        <div class="card-footer bg-light py-3 border-top border-light d-flex justify-content-end gap-2">
                            <a href="{{ route('owner.promotions.index') }}" class="btn btn-white border px-4">Quay
                                lại</a>
                            <button type="submit" class="btn btn-primary px-5 fw-bold shadow-sm">Tạo Voucher</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const discountType = document.getElementById('discountType');
            const maxDiscountCol = document.getElementById('maxDiscountCol');
            const checkUnlimited = document.getElementById('is_unlimited');
            const inputLimit = document.getElementById('usage_limit_input');

            // 1. Xử lý ẩn/hiện "Giảm tối đa"
            function updateDiscountUI() {
                if (discountType.value === 'percentage') {
                    maxDiscountCol.style.display = 'block';
                } else {
                    maxDiscountCol.style.display = 'none';
                }
            }

            // 2. Xử lý Switch Không giới hạn
            function updateLimitUI() {
                if (checkUnlimited.checked) {
                    inputLimit.disabled = true;
                    inputLimit.value = '';
                    inputLimit.placeholder = "∞ Vô hạn";
                    inputLimit.classList.remove('is-invalid'); // Xóa báo đỏ khi chuyển sang vô hạn
                } else {
                    inputLimit.disabled = false;
                    inputLimit.placeholder = "Nhập số lượng...";
                }
            }

            // Gán sự kiện
            discountType.addEventListener('change', updateDiscountUI);
            checkUnlimited.addEventListener('change', updateLimitUI);

            // Chạy ngay khi load trang để khớp với dữ liệu old()
            updateDiscountUI();
            updateLimitUI();
        });
    </script>
@endsection
