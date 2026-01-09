@extends('app')
@section('content')
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-header bg-white py-3 d-flex justify-content-between">
                        <h5 class="fw-bold mb-0 text-warning"><i class="fas fa-edit me-2"></i>Sửa Voucher:
                            {{ $promotion->code }}</h5>
                        <span class="badge bg-light text-dark border">Đã sử dụng: {{ $promotion->used_count }} lần</span>
                    </div>
                    <div class="card-body p-4">
                        <form action="{{ route('owner.promotions.update', $promotion) }}" method="POST">
                            @csrf @method('PUT')
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Mã Voucher (Không thể sửa)</label>
                                    <input type="text" class="form-control bg-light fw-bold"
                                        value="{{ $promotion->code }}" readonly>
                                    <input type="hidden" name="code" value="{{ $promotion->code }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Trạng thái</label>
                                    <select name="process_status" class="form-select fw-bold">
                                        <option value="active"
                                            {{ $promotion->process_status == 'active' ? 'selected' : '' }}>✅ Đang chạy
                                        </option>
                                        <option value="disabled"
                                            {{ $promotion->process_status == 'disabled' ? 'selected' : '' }}>❌ Tạm dừng
                                        </option>
                                    </select>
                                </div>

                                <div class="col-12">
                                    <label class="form-label fw-bold">Mô tả</label>
                                    <textarea name="description" rows="2" class="form-control">{{ old('description', $promotion->description) }}</textarea>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Cách thức giảm</label>
                                    <select name="type" id="discountType" class="form-select">
                                        <option value="percentage" {{ $promotion->type == 'percentage' ? 'selected' : '' }}>
                                            Phần trăm (%)</option>
                                        <option value="fixed" {{ $promotion->type == 'fixed' ? 'selected' : '' }}>Số tiền
                                            cố định (₫)</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Giá trị giảm</label>
                                    <input type="number" name="value" class="form-control fw-bold"
                                        value="{{ old('value', $promotion->getRawOriginal('value')) }}" required>
                                </div>
                                <div id="maxDiscountCol" class="col-md-4">
                                    <label class="form-label fw-bold">Giảm tối đa (₫)</label>
                                    <input type="number" name="max_discount_amount" class="form-control"
                                        value="{{ old('max_discount_amount', $promotion->getRawOriginal('max_discount_amount')) }}">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Phạm vi áp dụng</label>
                                    <select name="venue_id" class="form-select border-warning">
                                        <option value="">🏘️ TẤT CẢ SÂN CỦA TÔI</option>
                                        @foreach ($venues as $v)
                                            <option value="{{ $v->id }}"
                                                {{ $promotion->venue_id == $v->id ? 'selected' : '' }}>📍 Chỉ riêng sân:
                                                {{ $v->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Đơn tối thiểu (₫)</label>
                                    <input type="number" name="min_order_value" class="form-control"
                                        value="{{ old('min_order_value', $promotion->getRawOriginal('min_order_value')) }}">
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Giới hạn lượt dùng (Đã dùng:
                                        {{ $promotion->used_count }})</label>
                                    <input type="number" name="usage_limit" class="form-control"
                                        value="{{ old('usage_limit', $promotion->usage_limit) }}"
                                        min="{{ $promotion->used_count }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Bắt đầu</label>
                                    <input type="datetime-local" name="start_at" class="form-control"
                                        value="{{ $promotion->start_at->format('Y-m-d\TH:i') }}" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Kết thúc</label>
                                    <input type="datetime-local" name="end_at" class="form-control text-danger fw-bold"
                                        value="{{ $promotion->end_at->format('Y-m-d\TH:i') }}" required>
                                </div>
                            </div>

                            <div class="mt-4 pt-3 border-top text-end">
                                <a href="{{ route('owner.promotions.index') }}"
                                    class="btn btn-light px-4 rounded-pill">Hủy bỏ</a>
                                <button type="submit" class="btn btn-warning px-5 rounded-pill shadow fw-bold">Cập Nhật
                                    Voucher</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const discountType = document.getElementById('discountType');
            const maxDiscountCol = document.getElementById('maxDiscountCol');

            function toggleMax() {
                maxDiscountCol.style.display = (discountType.value === 'percentage') ? 'block' : 'none';
            }
            discountType.addEventListener('change', toggleMax);
            toggleMax();
        });
    </script>
@endsection
