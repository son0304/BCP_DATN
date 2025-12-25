@extends('app')
@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0 text-primary font-weight-bold">Tạo Voucher Mới</h4>
                        <a href="{{ route('owner.promotions.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Quay lại
                        </a>
                    </div>
                </div>

                <div class="card-body">

                    <form method="POST" action="{{ route('owner.promotions.store') }}">
                        @csrf

                        <div class="row">
                            {{-- Venue (Bắt buộc cho Owner) --}}
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="venue_id" class="form-label fw-bold">Venue <span class="text-danger">*</span></label>
                                    <select class="form-select @error('venue_id') is-invalid @enderror"
                                        id="venue_id"
                                        name="venue_id"
                                        required>
                                        <option value="">Chọn venue</option>
                                        @foreach($venues as $venue)
                                            <option value="{{ $venue->id }}" {{ old('venue_id') == $venue->id ? 'selected' : '' }}>
                                                {{ $venue->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('venue_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">Voucher này sẽ chỉ áp dụng cho venue đã chọn</small>
                                </div>
                            </div>

                            {{-- Mã Voucher --}}
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="code" class="form-label fw-bold">Mã voucher <span class="text-danger">*</span></label>
                                    <input type="text"
                                        class="form-control @error('code') is-invalid @enderror"
                                        id="code"
                                        name="code"
                                        value="{{ old('code') }}"
                                        placeholder="VD: SALE2024"
                                        style="text-transform: uppercase">
                                    @error('code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">Mã voucher sẽ được tự động chuyển thành chữ in hoa</small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            {{-- Loại Voucher --}}
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="type" class="form-label fw-bold">Loại voucher <span class="text-danger">*</span></label>
                                    <select class="form-select @error('type') is-invalid @enderror"
                                        id="type"
                                        name="type">
                                        <option value="">Chọn loại voucher</option>
                                        <option value="%" {{ old('type') == '%' ? 'selected' : '' }}>Phần trăm (%)</option>
                                        <option value="money" {{ old('type') == 'money' ? 'selected' : '' }}>Tiền mặt (VND)</option>
                                    </select>
                                    @error('type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            {{-- Giá trị --}}
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="value" class="form-label fw-bold">Giá trị giảm <span class="text-danger">*</span></label>
                                    <div class="input-group has-validation">
                                        <input type="number"
                                            class="form-control @error('value') is-invalid @enderror"
                                            id="value"
                                            name="value"
                                            value="{{ old('value') }}"
                                            placeholder="VD: 10 hoặc 50000"
                                            min="0">
                                        <span class="input-group-text" id="valueType">
                                            @if(old('type') == '%') % @elseif(old('type') == 'money') ₫ @else - @endif
                                        </span>
                                        @error('value')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <small class="form-text text-muted fst-italic">
                                        Ví dụ: Nhập 10 cho 10%, nhập 50000 cho 50,000đ
                                    </small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            {{-- Giảm tối đa (Chỉ hiện khi chọn %) --}}
                            <div class="col-md-6">
                                <div class="form-group mb-3" id="capWrapper" style="display: {{ old('type') == '%' ? 'block' : 'none' }};">
                                    <label for="max_discount_amount" class="form-label fw-bold">Số tiền giảm tối đa (VND)</label>
                                    <div class="input-group has-validation">
                                        <input type="number"
                                            class="form-control @error('max_discount_amount') is-invalid @enderror"
                                            id="max_discount_amount"
                                            name="max_discount_amount"
                                            value="{{ old('max_discount_amount') }}"
                                            placeholder="VD: 50000"
                                            min="0">
                                        <span class="input-group-text">₫</span>
                                        @error('max_discount_amount')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <small class="form-text text-muted">Bỏ trống nếu không giới hạn số tiền giảm tối đa</small>
                                </div>
                            </div>

                            {{-- Giới hạn sử dụng --}}
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="usage_limit" class="form-label fw-bold">Giới hạn số lần sử dụng <span class="text-danger">*</span></label>
                                    <input type="number"
                                        class="form-control @error('usage_limit') is-invalid @enderror"
                                        id="usage_limit"
                                        name="usage_limit"
                                        value="{{ old('usage_limit', 1) }}"
                                        min="1"
                                        placeholder="VD: 100">
                                    @error('usage_limit')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">Tổng số lần voucher này có thể được áp dụng</small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            {{-- Ngày bắt đầu --}}
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="start_at" class="form-label fw-bold">Thời gian bắt đầu <span class="text-danger">*</span></label>
                                    <input type="datetime-local"
                                        class="form-control @error('start_at') is-invalid @enderror"
                                        id="start_at"
                                        name="start_at"
                                        value="{{ old('start_at') }}">
                                    @error('start_at')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            {{-- Ngày kết thúc --}}
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="end_at" class="form-label fw-bold">Thời gian kết thúc <span class="text-danger">*</span></label>
                                    <input type="datetime-local"
                                        class="form-control @error('end_at') is-invalid @enderror"
                                        id="end_at"
                                        name="end_at"
                                        value="{{ old('end_at') }}">
                                    @error('end_at')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group mt-4 d-flex justify-content-end gap-2">
                            <a href="{{ route('owner.promotions.index') }}" class="btn btn-secondary px-4">
                                <i class="fas fa-times me-1"></i> Hủy bỏ
                            </a>
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="fas fa-save me-1"></i> Tạo voucher
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
            // 1. Lấy thời gian hiện tại định dạng YYYY-MM-DDThh:mm
            const now = new Date();
            now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
            const minDateTime = now.toISOString().slice(0, 16);

            // 2. Set min cho ngày bắt đầu là hiện tại
            startInput.min = minDateTime;

            // 3. Hàm cập nhật ngày kết thúc dựa theo ngày bắt đầu
            function updateEndDateMin() {
                if (startInput.value) {
                    // Ngày kết thúc tối thiểu phải bằng ngày bắt đầu
                    endInput.min = startInput.value;

                    // Nếu ngày kết thúc hiện tại < ngày bắt đầu mới chọn -> Reset ngày kết thúc
                    if (endInput.value && endInput.value < startInput.value) {
                        endInput.value = startInput.value;
                    }
                } else {
                    endInput.min = minDateTime;
                }
            }

            // 4. Lắng nghe sự kiện thay đổi
            startInput.addEventListener('change', updateEndDateMin);

            // Chạy lần đầu (phòng trường hợp trình duyệt lưu cache value cũ)
            updateEndDateMin();
        }

        // --- LOGIC 2: XỬ LÝ LOẠI VOUCHER ---
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

