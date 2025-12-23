@extends('app')
@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <h4 class="card-title mb-0 text-primary font-weight-bold">Chỉnh sửa Voucher</h4>
                            <span class="badge bg-info ms-2 fs-6">{{ $promotion->code }}</span>
                        </div>
                        <a href="{{ route('admin.promotions.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Quay lại
                        </a>
                    </div>
                </div>

                <div class="card-body">

                    <form method="POST" action="{{ route('admin.promotions.update', $promotion) }}">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="code" class="form-label fw-bold">Mã voucher <span class="text-danger">*</span></label>
                                    <input type="text"
                                        class="form-control @error('code') is-invalid @enderror"
                                        id="code"
                                        name="code"
                                        value="{{ old('code', $promotion->code) }}"
                                        placeholder="VD: SALE2024"
                                        style="text-transform: uppercase">
                                    @error('code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="type" class="form-label fw-bold">Loại voucher <span class="text-danger">*</span></label>
                                    <select class="form-select @error('type') is-invalid @enderror"
                                        id="type"
                                        name="type">
                                        <option value="">Chọn loại voucher</option>
                                        <option value="%" {{ old('type', $promotion->type) == '%' ? 'selected' : '' }}>Phần trăm (%)</option>
                                        <option value="money" {{ old('type', $promotion->type) == 'money' ? 'selected' : '' }}>Tiền mặt (VND)</option>
                                    </select>
                                    @error('type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="value" class="form-label fw-bold">Giá trị giảm <span class="text-danger">*</span></label>
                                    <div class="input-group has-validation">
                                        <input type="number"
                                            class="form-control @error('value') is-invalid @enderror"
                                            id="value"
                                            name="value"
                                            value="{{ old('value', $promotion->value) }}"
                                            placeholder="VD: 10 hoặc 50000"
                                            min="0">
                                        <span class="input-group-text" id="valueType">
                                            @if(old('type', $promotion->type) == '%') % @elseif(old('type', $promotion->type) == 'money') ₫ @else - @endif
                                        </span>
                                        @error('value')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group mb-3" id="capWrapper" style="display: {{ old('type', $promotion->type) == '%' ? 'block' : 'none' }};">
                                    <label for="max_discount_amount" class="form-label fw-bold">Số tiền giảm tối đa (VND)</label>
                                    <div class="input-group has-validation">
                                        <input type="number"
                                            class="form-control @error('max_discount_amount') is-invalid @enderror"
                                            id="max_discount_amount"
                                            name="max_discount_amount"
                                            value="{{ old('max_discount_amount', $promotion->max_discount_amount) }}"
                                            placeholder="VD: 50000"
                                            min="0">
                                        <span class="input-group-text">₫</span>
                                        @error('max_discount_amount')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="usage_limit" class="form-label fw-bold">Giới hạn sử dụng <span class="text-danger">*</span></label>
                                    <input type="number"
                                        class="form-control @error('usage_limit') is-invalid @enderror"
                                        id="usage_limit"
                                        name="usage_limit"
                                        value="{{ old('usage_limit', $promotion->usage_limit) }}"
                                        min="{{ $promotion->used_count }}"
                                        placeholder="VD: 100">
                                    @error('usage_limit')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="start_at" class="form-label fw-bold">Thời gian bắt đầu <span class="text-danger">*</span></label>
                                    <input type="datetime-local"
                                        class="form-control @error('start_at') is-invalid @enderror"
                                        id="start_at"
                                        name="start_at"
                                        value="{{ old('start_at', \Carbon\Carbon::parse($promotion->start_at, 'UTC')->setTimezone('Asia/Ho_Chi_Minh')->format('Y-m-d\TH:i')) }}">
                                    @error('start_at')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="end_at" class="form-label fw-bold">Thời gian kết thúc <span class="text-danger">*</span></label>
                                    <input type="datetime-local"
                                        class="form-control @error('end_at') is-invalid @enderror"
                                        id="end_at"
                                        name="end_at"
                                        value="{{ old('end_at', \Carbon\Carbon::parse($promotion->end_at, 'UTC')->setTimezone('Asia/Ho_Chi_Minh')->format('Y-m-d\TH:i')) }}">
                                    @error('end_at')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-info border-0 shadow-sm mt-3">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-info-circle fs-4 me-3 text-info"></i>
                                <div>
                                    <h6 class="fw-bold mb-1">Thông tin sử dụng</h6>
                                    <p class="mb-0 small">Voucher này đã được sử dụng <strong class="text-dark">{{ $promotion->used_count }}</strong> lần. Bạn không thể giảm giới hạn sử dụng thấp hơn con số này.</p>
                                </div>
                            </div>
                        </div>

                        <div class="form-group mt-4 d-flex justify-content-end gap-2">
                            <a href="{{ route('admin.promotions.index') }}" class="btn btn-secondary px-4">
                                <i class="fas fa-times me-1"></i> Hủy bỏ
                            </a>
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="fas fa-save me-1"></i> Cập nhật
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // --- LOGIC 1: XỬ LÝ NGÀY GIỜ ---
        const startInput = document.getElementById('start_at');
        const endInput = document.getElementById('end_at');

        if (startInput && endInput) {
            // Hàm cập nhật ngày kết thúc dựa theo ngày bắt đầu
            function updateEndDateMin() {
                if (startInput.value) {
                    endInput.min = startInput.value;

                    // Nếu người dùng sửa ngày bắt đầu lớn hơn ngày kết thúc hiện tại
                    // -> Clear hoặc reset ngày kết thúc để bắt buộc chọn lại
                    if (endInput.value && endInput.value < startInput.value) {
                        // Thông báo nhỏ hoặc tự động đẩy ngày kết thúc lên (tùy chọn)
                        endInput.value = startInput.value;
                    }
                }
            }

            startInput.addEventListener('change', updateEndDateMin);

            // Chạy lần đầu để đảm bảo logic đúng với dữ liệu đang có
            updateEndDateMin();
        }

        // --- LOGIC 2: XỬ LÝ LOẠI VOUCHER (Giữ nguyên) ---
        const typeSelect = document.getElementById('type');
        const codeInput = document.getElementById('code');

        function updateTypeDisplay() {
            const type = typeSelect ? typeSelect.value : '';
            const valueTypeSpan = document.getElementById('valueType');
            const capWrapper = document.getElementById('capWrapper');

            if (type === '%') {
                if (valueTypeSpan) valueTypeSpan.textContent = '%';
                if (capWrapper) capWrapper.style.display = 'block';
            } else if (type === 'money') {
                if (valueTypeSpan) valueTypeSpan.textContent = '₫';
                if (capWrapper) capWrapper.style.display = 'none';
            } else {
                if (valueTypeSpan) valueTypeSpan.textContent = '-';
                if (capWrapper) capWrapper.style.display = 'none';
            }
        }

        if (codeInput) {
            codeInput.addEventListener('input', function() {
                this.value = this.value.toUpperCase();
            });
        }

        if (typeSelect) {
            updateTypeDisplay();
            typeSelect.addEventListener('change', updateTypeDisplay);
        }
    });
</script>
@endpush
@endsection