@extends('app')
@section('content')
    <style>
        .input-code { font-family: 'Monaco', monospace; letter-spacing: 1px; font-weight: 700; text-transform: uppercase; }
        .field-label { font-size: 0.75rem; text-transform: uppercase; font-weight: 700; color: #6c757d; margin-bottom: 0.5rem; display: block; }
    </style>

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <form action="{{ route('admin.promotions.store') }}" method="POST">
                    @csrf
                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-header bg-white py-4 border-bottom border-light">
                            <h5 class="fw-bold mb-1 text-primary">Tạo Voucher Hệ Thống</h5>
                            <div class="text-muted small">Mã này có thể áp dụng toàn sàn hoặc cho một sân cụ thể</div>
                        </div>

                        <div class="card-body p-4">
                            <div class="row g-4 mb-4">
                                <div class="col-md-6">
                                    <label class="field-label">Mã Voucher <span class="text-danger">*</span></label>
                                    <input type="text" name="code" class="form-control input-code" placeholder="VD: SUMMER2024" value="{{ old('code') }}" required>
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
                                    <textarea name="description" rows="2" class="form-control" placeholder="Nhập mô tả cho voucher...">{{ old('description') }}</textarea>
                                </div>
                            </div>

                            <hr class="bg-light my-4">

                            <div class="row g-4 mb-4">
                                <div class="col-md-4">
                                    <label class="field-label">Loại giảm giá</label>
                                    <select name="type" id="discountType" class="form-select">
                                        <option value="percentage">Theo phần trăm (%)</option>
                                        <option value="fixed">Số tiền cố định (VNĐ)</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="field-label">Giá trị giảm <span class="text-danger">*</span></label>
                                    <input type="number" name="value" class="form-control fw-bold" value="{{ old('value') }}" placeholder="VD: 10 hoặc 50000" required>
                                </div>
                                <div class="col-md-4" id="maxDiscountCol">
                                    <label class="field-label">Giảm tối đa (VNĐ)</label>
                                    <input type="number" name="max_discount_amount" class="form-control" placeholder="Không giới hạn">
                                </div>
                            </div>

                            <hr class="bg-light my-4">

                            <div class="row g-4 mb-4">
                                <div class="col-md-4">
                                    <label class="field-label text-primary">Phạm vi áp dụng</label>
                                    <select name="venue_id" class="form-select border-primary">
                                        <option value="">🌍 Toàn hệ thống (Mặc định)</option>
                                        @foreach ($venues as $v)
                                            <option value="{{ $v->id }}">📍 Sân: {{ $v->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="field-label">Đối tượng khách hàng</label>
                                    <select name="target_user_type" class="form-select">
                                        <option value="all">Tất cả khách hàng</option>
                                        <option value="new_user">Chỉ khách mới</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="field-label">Đơn tối thiểu (VNĐ)</label>
                                    <input type="number" name="min_order_value" class="form-control" value="0">
                                </div>
                            </div>

                            <hr class="bg-light my-4">

                            <div class="row g-4">
                                <div class="col-md-4">
                                    <div class="d-flex justify-content-between align-items-end mb-2">
                                        <label class="field-label mb-0">Giới hạn sử dụng</label>
                                        <div class="form-check form-switch min-h-0 mb-0">
                                            <input class="form-check-input" type="checkbox" id="is_unlimited" name="is_unlimited" value="1" checked>
                                            <label class="form-check-label small text-muted" for="is_unlimited">Vô hạn</label>
                                        </div>
                                    </div>
                                    <input type="number" name="usage_limit" id="usage_limit_input" class="form-control" disabled placeholder="∞ Vô hạn">
                                </div>
                                <div class="col-md-4">
                                    <label class="field-label">Bắt đầu</label>
                                    <input type="datetime-local" name="start_at" class="form-control" value="{{ now()->format('Y-m-d\TH:i') }}" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="field-label">Kết thúc</label>
                                    <input type="datetime-local" name="end_at" class="form-control" required>
                                </div>
                            </div>
                        </div>

                        <div class="card-footer bg-light py-3 d-flex justify-content-end gap-2">
                            <a href="{{ route('admin.promotions.index') }}" class="btn btn-white border px-4">Quay lại</a>
                            <button type="submit" class="btn btn-primary px-5 fw-bold shadow-sm">Phát Hành Voucher</button>
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

            discountType.addEventListener('change', function() {
                maxDiscountCol.style.display = this.value === 'percentage' ? 'block' : 'none';
            });

            checkUnlimited.addEventListener('change', function() {
                inputLimit.disabled = this.checked;
                inputLimit.value = '';
                inputLimit.placeholder = this.checked ? "∞ Vô hạn" : "Nhập số lượng...";
            });
        });
    </script>
@endsection
