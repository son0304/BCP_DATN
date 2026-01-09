@extends('app')
@section('content')
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                    <div class="card-header bg-warning py-3 d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold mb-0 text-dark"><i class="fas fa-edit me-2"></i>Cập Nhật Voucher:
                            {{ $promotion->code }}</h5>
                        <span class="badge bg-dark rounded-pill px-3">Đã sử dụng: {{ $promotion->used_count }}</span>
                    </div>
                    <div class="card-body p-4">
                        <form action="{{ route('admin.promotions.update', $promotion) }}" method="POST">
                            @csrf @method('PUT')
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold text-muted">Mã Voucher (Không thể sửa)</label>
                                    <input type="text" value="{{ $promotion->code }}"
                                        class="form-control bg-light fw-bold" readonly>
                                    <input type="hidden" name="code" value="{{ $promotion->code }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Trạng thái hiện tại</label>
                                    <select name="process_status"
                                        class="form-select fw-bold {{ $promotion->process_status == 'active' ? 'text-success' : 'text-danger' }}">
                                        <option value="active"
                                            {{ $promotion->process_status == 'active' ? 'selected' : '' }}>✅ Đang hoạt động
                                        </option>
                                        <option value="disabled"
                                            {{ $promotion->process_status == 'disabled' ? 'selected' : '' }}>❌ Tạm tắt / Ẩn
                                        </option>
                                    </select>
                                </div>

                                <div class="col-12">
                                    <label class="form-label fw-bold">Mô tả chương trình</label>
                                    <textarea name="description" rows="2" class="form-control">{{ old('description', $promotion->description) }}</textarea>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label fw-bold text-primary">Loại giảm giá</label>
                                    <select name="type" id="discountType" class="form-select border-primary fw-bold">
                                        <option value="percentage" {{ $promotion->type == 'percentage' ? 'selected' : '' }}>
                                            Phần trăm (%)</option>
                                        <option value="fixed" {{ $promotion->type == 'fixed' ? 'selected' : '' }}>Tiền mặt
                                            (₫)</option>
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

                                <div class="col-md-4 border-start ps-4">
                                    <label class="form-label fw-bold">Đơn tối thiểu (₫)</label>
                                    <input type="number" name="min_order_value" class="form-control"
                                        value="{{ old('min_order_value', $promotion->getRawOriginal('min_order_value')) }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Giới hạn lượt dùng</label>
                                    <input type="number" name="usage_limit" class="form-control border-warning"
                                        value="{{ old('usage_limit', $promotion->usage_limit) }}"
                                        min="{{ $promotion->used_count }}">
                                    <small class="text-muted italic">Phải >= số lượng đã dùng
                                        ({{ $promotion->used_count }})</small>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold text-info">Đối tượng áp dụng</label>
                                    <select name="target_user_type" class="form-select border-info">
                                        <option value="all"
                                            {{ $promotion->target_user_type == 'all' ? 'selected' : '' }}>Tất cả người dùng
                                        </option>
                                        <option value="new_user"
                                            {{ $promotion->target_user_type == 'new_user' ? 'selected' : '' }}>Chỉ người
                                            mới</option>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Phạm vi áp dụng (Admin có thể thay đổi)</label>
                                    <select name="venue_id" class="form-select border-primary">
                                        <option value="">🌍 Toàn bộ hệ thống</option>
                                        @foreach ($venues as $v)
                                            <option value="{{ $v->id }}"
                                                {{ $promotion->venue_id == $v->id ? 'selected' : '' }}>📍 Sân:
                                                {{ $v->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label fw-bold">Ngày bắt đầu</label>
                                    <input type="datetime-local" name="start_at" class="form-control"
                                        value="{{ $promotion->start_at->format('Y-m-d\TH:i') }}" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-bold">Ngày kết thúc</label>
                                    <input type="datetime-local" name="end_at" class="form-control text-danger fw-bold"
                                        value="{{ $promotion->end_at->format('Y-m-d\TH:i') }}" required>
                                </div>
                            </div>

                            <div class="mt-5 pt-3 border-top d-flex justify-content-between align-items-center">
                                <div class="text-muted small">
                                    <i class="fas fa-user-edit me-1"></i> Người tạo:
                                    <strong>{{ $promotion->creator->name }}</strong> ({{ $promotion->creator->role->name }})
                                </div>
                                <div class="d-flex gap-2">
                                    <a href="{{ route('admin.promotions.index') }}"
                                        class="btn btn-light px-4 rounded-pill">Hủy</a>
                                    <button type="submit"
                                        class="btn btn-warning px-5 shadow rounded-pill fw-bold text-dark">Lưu Thay
                                        Đổi</button>
                                </div>
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

            function toggleMaxDiscount() {
                if (discountType.value === 'percentage') {
                    maxDiscountCol.style.opacity = '1';
                    maxDiscountCol.querySelector('input').disabled = false;
                } else {
                    maxDiscountCol.style.opacity = '0.3';
                    maxDiscountCol.querySelector('input').disabled = true;
                    maxDiscountCol.querySelector('input').value = '';
                }
            }
            discountType.addEventListener('change', toggleMaxDiscount);
            toggleMaxDiscount();
        });
    </script>
@endsection
