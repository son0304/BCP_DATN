@extends('app')
@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="card-header bg-primary py-3">
                    <h5 class="fw-bold mb-0 text-white"><i class="fas fa-plus-circle me-2"></i>Tạo Voucher Hệ Thống Mới</h5>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('admin.promotions.store') }}" method="POST">
                        @csrf
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Mã Voucher <span class="text-danger">*</span></label>
                                <input type="text" name="code" class="form-control text-uppercase fw-bold" placeholder="VD: UUDAI2026" value="{{ old('code') }}" required>
                                @error('code') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Trạng thái kích hoạt</label>
                                <select name="process_status" class="form-select">
                                    <option value="active" {{ old('process_status') == 'active' ? 'selected' : '' }}>Hoạt động ngay</option>
                                    <option value="disabled" {{ old('process_status') == 'disabled' ? 'selected' : '' }}>Tạm ẩn</option>
                                </select>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-bold">Mô tả ngắn</label>
                                <textarea name="description" rows="2" class="form-control" placeholder="Nhập mục đích chương trình hoặc ghi chú...">{{ old('description') }}</textarea>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold text-primary">Loại ưu đãi</label>
                                <select name="type" id="discountType" class="form-select border-primary fw-bold">
                                    <option value="percentage" {{ old('type') == 'percentage' ? 'selected' : '' }}>Phần trăm (%)</option>
                                    <option value="fixed" {{ old('type') == 'fixed' ? 'selected' : '' }}>Tiền mặt (₫)</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Giá trị giảm <span class="text-danger">*</span></label>
                                <input type="number" name="value" class="form-control fw-bold" value="{{ old('value', 0) }}" required>
                            </div>
                            <div id="maxDiscountCol" class="col-md-4">
                                <label class="form-label fw-bold">Giảm tối đa (₫)</label>
                                <input type="number" name="max_discount_amount" class="form-control" value="{{ old('max_discount_amount') }}" placeholder="Để trống = Không giới hạn">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold">Đơn tối thiểu (₫)</label>
                                <input type="number" name="min_order_value" class="form-control" value="{{ old('min_order_value', 0) }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Tổng lượt sử dụng tối đa</label>
                                <input type="number" name="usage_limit" class="form-control" value="{{ old('usage_limit', 0) }}" placeholder="0 = Vô hạn">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold text-info">Đối tượng áp dụng</label>
                                <select name="target_user_type" class="form-select border-info">
                                    <option value="all" {{ old('target_user_type') == 'all' ? 'selected' : '' }}>Tất cả người dùng</option>
                                    <option value="new_user" {{ old('target_user_type') == 'new_user' ? 'selected' : '' }}>Chỉ người mới (Lần đầu đặt)</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">Phạm vi áp dụng</label>
                                <select name="venue_id" class="form-select border-primary">
                                    <option value="">🌍 Toàn bộ hệ thống (Tất cả các sân)</option>
                                    @foreach ($venues as $v)
                                        <option value="{{ $v->id }}" {{ old('venue_id') == $v->id ? 'selected' : '' }}>📍 Sân cụ thể: {{ $v->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-bold">Thời gian bắt đầu</label>
                                <input type="datetime-local" name="start_at" class="form-control" value="{{ old('start_at', now()->format('Y-m-d\TH:i')) }}" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Thời gian kết thúc</label>
                                <input type="datetime-local" name="end_at" class="form-control" value="{{ old('end_at') }}" required>
                            </div>
                        </div>

                        <div class="mt-5 pt-3 border-top d-flex gap-2 justify-content-end">
                            <a href="{{ route('admin.promotions.index') }}" class="btn btn-light px-4 rounded-pill">Hủy</a>
                            <button type="submit" class="btn btn-primary px-5 shadow rounded-pill fw-bold">Phát Hành Voucher</button>
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
