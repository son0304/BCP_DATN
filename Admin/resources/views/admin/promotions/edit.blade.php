@extends('app')
@section('content')
<style>
    .input-code {
        font-family: 'Monaco', monospace;
        letter-spacing: 1px;
        background-color: #f8f9fa;
        font-weight: 700;
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
</style>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <form action="{{ route('admin.promotions.update', $promotion) }}" method="POST">
                @csrf @method('PUT')
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-header bg-white py-4 border-bottom border-light d-flex justify-content-between">
                        <div>
                            <h5 class="fw-bold mb-1 text-dark">Chỉnh sửa Voucher: <span
                                    class="text-primary">{{ $promotion->code }}</span></h5>
                            <div class="text-muted small">Cập nhật thông tin voucher hệ thống</div>
                        </div>
                        <div class="text-end">
                            <span class="badge bg-light text-dark border p-2">Đã dùng:
                                <strong>{{ $promotion->used_count }}</strong> lượt</span>
                        </div>
                    </div>

                    <div class="card-body p-4">
                        <div class="row g-4 mb-4">
                            <div class="col-md-6">
                                <label class="field-label">Mã Voucher</label>
                                <input type="text" class="form-control input-code" value="{{ $promotion->code }}"
                                    readonly tabindex="-1">
                            </div>
                            <div class="col-md-6">
                                <label class="field-label">Trạng thái hoạt động</label>
                                <select name="process_status"
                                    class="form-select fw-bold @error('process_status') is-invalid @enderror">
                                    <option value="active"
                                        {{ old('process_status', $promotion->process_status) == 'active' ? 'selected' : '' }}>
                                        Hoạt động</option>
                                    <option value="disabled"
                                        {{ old('process_status', $promotion->process_status) == 'disabled' ? 'selected' : '' }}>
                                        Tạm dừng</option>
                                </select>
                                @error('process_status')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-12">
                                <label class="field-label">Mô tả chương trình</label>
                                <textarea name="description" rows="2" class="form-control @error('description') is-invalid @enderror">{{ old('description', $promotion->description) }}</textarea>
                                @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <hr class="bg-light my-4">

                        <div class="row g-4 mb-4">
                            <div class="col-md-4">
                                <label class="field-label">Loại giảm giá</label>
                                <select name="type" id="discountType"
                                    class="form-select @error('type') is-invalid @enderror">
                                    <option value="percentage"
                                        {{ old('type', $promotion->type) == 'percentage' ? 'selected' : '' }}>Phần trăm
                                        (%)</option>
                                    <option value="fixed"
                                        {{ old('type', $promotion->type) == 'fixed' ? 'selected' : '' }}>Cố định (VNĐ)
                                    </option>
                                </select>
                                @error('type')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="field-label">Giá trị giảm</label>
                                <input type="number" name="value"
                                    class="form-control fw-bold @error('value') is-invalid @enderror"
                                    value="{{ old('value', $promotion->getRawOriginal('value')) }}">
                                @error('value')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4" id="maxDiscountCol">
                                <label class="field-label">Giảm tối đa (VNĐ)</label>
                                <input type="number" name="max_discount_amount"
                                    class="form-control @error('max_discount_amount') is-invalid @enderror"
                                    value="{{ old('max_discount_amount', $promotion->getRawOriginal('max_discount_amount')) }}">
                                @error('max_discount_amount')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <hr class="bg-light my-4">

                        <div class="row g-4 mb-4">
                            <div class="col-md-4">
                                <label class="field-label text-primary">Phạm vi áp dụng</label>
                                <select name="venue_id"
                                    class="form-select border-primary @error('venue_id') is-invalid @enderror">
                                    <option value="">🌍 Toàn hệ thống</option>
                                    @foreach ($venues as $v)
                                    <option value="{{ $v->id }}"
                                        {{ old('venue_id', $promotion->venue_id) == $v->id ? 'selected' : '' }}>📍
                                        Sân: {{ $v->name }}</option>
                                    @endforeach
                                </select>
                                @error('venue_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="field-label">Đối tượng</label>
                                <select name="target_user_type"
                                    class="form-select @error('target_user_type') is-invalid @enderror">
                                    <option value="all"
                                        {{ old('target_user_type', $promotion->target_user_type) == 'all' ? 'selected' : '' }}>
                                        Tất cả</option>
                                    <option value="new_user"
                                        {{ old('target_user_type', $promotion->target_user_type) == 'new_user' ? 'selected' : '' }}>
                                        Khách mới</option>
                                </select>
                                @error('target_user_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="field-label">Đơn tối thiểu</label>
                                <input type="number" name="min_order_value"
                                    class="form-control @error('min_order_value') is-invalid @enderror"
                                    value="{{ old('min_order_value', $promotion->getRawOriginal('min_order_value')) }}">
                                @error('min_order_value')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <hr class="bg-light my-4">

                        <div class="row g-4">
                            <div class="col-md-4">
                                <div class="d-flex justify-content-between align-items-end mb-2">
                                    <label class="field-label mb-0">Giới hạn sử dụng</label>
                                    <div class="form-check form-switch min-h-0 mb-0">
                                        @php $isUnlimited = old('is_unlimited', $promotion->usage_limit < 0 ? '1' : '0' ); @endphp
                                            <input class="form-check-input" type="checkbox" id="is_unlimited"
                                            name="is_unlimited" value="1"
                                            {{ $isUnlimited == '1' ? 'checked' : '' }}>
                                            <label class="form-check-label small text-muted" for="is_unlimited">Vô
                                                hạn</label>
                                    </div>
                                </div>
                                <input type="number" name="usage_limit" id="usage_limit_input"
                                    class="form-control @error('usage_limit') is-invalid @enderror"
                                    value="{{ old('usage_limit', $promotion->usage_limit > 0 ? $promotion->usage_limit : '') }}"
                                    {{ $isUnlimited == '1' ? 'disabled' : '' }}
                                    placeholder="{{ $isUnlimited == '1' ? '∞ Vô hạn' : 'Nhập số lượt...' }}">
                                @error('usage_limit')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="field-label">Bắt đầu</label>
                                <input type="datetime-local"
                                    name="start_at"
                                    id="start_at"
                                    class="form-control @error('start_at') is-invalid @enderror"
                                    value="{{ old('start_at', $promotion->start_at->setTimezone('Asia/Ho_Chi_Minh')->format('Y-m-d\TH:i')) }}"
                                    min="{{ now()->format('Y-m-d\TH:i') }}">
                                @error('start_at')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="field-label">Kết thúc</label>
                                <input type="datetime-local"
                                    name="end_at"
                                    id="end_at"
                                    class="form-control @error('end_at') is-invalid @enderror"
                                    value="{{ old('end_at', $promotion->end_at->setTimezone('Asia/Ho_Chi_Minh')->format('Y-m-d\TH:i')) }}">
                                @error('end_at')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="card-footer bg-light py-3 d-flex justify-content-between align-items-center">
                        <div class="small text-muted">Mã được tạo bởi:
                            <strong>{{ $promotion->creator->name ?? 'Admin' }}</strong>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('admin.promotions.index') }}" class="btn btn-white border px-4">Hủy</a>
                            <button type="submit" class="btn btn-primary px-5 fw-bold shadow-sm">Lưu thay
                                đổi</button>
                        </div>
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

        function updateUI() {
            maxDiscountCol.style.display = discountType.value === 'percentage' ? 'block' : 'none';
            inputLimit.disabled = checkUnlimited.checked;
            if (checkUnlimited.checked) {
                inputLimit.value = '';
                inputLimit.placeholder = "∞ Vô hạn";
            } else {
                inputLimit.placeholder = "Nhập số lượt...";
            }
        }

        discountType.addEventListener('change', updateUI);
        checkUnlimited.addEventListener('change', updateUI);
        updateUI(); // Khởi tạo khi load trang

        const startInput = document.getElementById('start_at');
        const endInput = document.getElementById('end_at');

        if (!startInput || !endInput) return;

        function getNowLocal() {
            const now = new Date();
            now.setSeconds(0);
            now.setMilliseconds(0);
            return now.toISOString().slice(0, 16);
        }

        // 1. Không cho chọn giờ quá khứ
        const nowLocal = getNowLocal();
        startInput.min = nowLocal;

        // 2. Đồng bộ end_at >= start_at
        if (startInput.value) {
            endInput.min = startInput.value;
        }

        startInput.addEventListener('change', function() {
            endInput.min = this.value;

            // Nếu end_at < start_at thì reset
            if (endInput.value && endInput.value < this.value) {
                endInput.value = '';
            }
        });
    });
</script>
@endsection