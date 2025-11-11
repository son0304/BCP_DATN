@extends('app')
@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="card-title">Tạo Voucher Mới</h3>
                        <a href="{{ route('admin.promotions.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Quay lại
                        </a>
                    </div>
                </div>
                
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger mb-4" style="display: block !important;">
                            <h5 class="alert-heading">
                                <i class="fas fa-exclamation-triangle me-2"></i>Vui lòng kiểm tra lại thông tin
                            </h5>
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('admin.promotions.store') }}">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="code">Mã voucher <span class="text-danger">*</span></label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="code" 
                                           name="code" 
                                           value="{{ old('code') }}" 
                                           placeholder="VD: SALE2024">
                                    <small class="form-text text-muted">Mã voucher sẽ được tự động chuyển thành chữ in hoa</small>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="type">Loại voucher <span class="text-danger">*</span></label>
                                    <select class="form-control" 
                                            id="type" 
                                            name="type">
                                        <option value="">Chọn loại voucher</option>
                                        <option value="%" {{ old('type') == '%' ? 'selected' : '' }}>Phần trăm (%)</option>
                                        <option value="VND" {{ old('type') == 'VND' ? 'selected' : '' }}>Tiền mặt (VND)</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="value">Giá trị <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="number" 
                                               class="form-control" 
                                               id="value" 
                                               name="value" 
                                               value="{{ old('value') }}" 
                                               placeholder="VD: 10 hoặc 50000">
                                        <span class="input-group-text" id="valueType">
                                            @if(old('type') == '%')
                                                %
                                            @elseif(old('type') == 'VND')
                                                ₫
                                            @else
                                                -
                                            @endif
                                        </span>
                                    </div>
                                    <small class="form-text text-muted">
                                       Phần trăm : 10 = 10%<br>
                                       VND : 50000 = 50,000
                                    </small>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group mb-3" id="capWrapper" style="display: {{ old('type') == '%' ? 'block' : 'none' }};">
                                    <label for="max_discount_amount">Số tiền giảm tối đa (VND) <span class="text-danger">*</span></label>
                                    <input type="number" 
                                           class="form-control" 
                                           id="max_discount_amount" 
                                           name="max_discount_amount" 
                                           value="{{ old('max_discount_amount') }}" 
                                           placeholder="VD: 50000">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="usage_limit">Giới hạn sử dụng <span class="text-danger">*</span></label>
                                    <input type="number" 
                                           class="form-control" 
                                           id="usage_limit" 
                                           name="usage_limit" 
                                           value="{{ old('usage_limit', 1) }}" 
                                       
                                           placeholder="VD: 100">
                                    <small class="form-text text-muted">Số lượt sử dụng tối đa cho voucher này (phải lớn hơn 0)</small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="start_at">Ngày bắt đầu <span class="text-danger">*</span></label>
                                    <input type="datetime-local" 
                                           class="form-control" 
                                           id="start_at" 
                                           name="start_at" 
                                           value="{{ old('start_at') }}">
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="end_at">Ngày kết thúc <span class="text-danger">*</span></label>
                                    <input type="datetime-local" 
                                           class="form-control" 
                                           id="end_at" 
                                           name="end_at" 
                                           value="{{ old('end_at') }}">
                                </div>
                            </div>
                        </div>

                        <div class="form-group mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Tạo voucher
                            </button>
                            <a href="{{ route('admin.promotions.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Hủy
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Hàm cập nhật hiển thị đơn vị giá trị và field max_discount_amount
    function updateTypeDisplay() {
        const typeSelect = document.getElementById('type');
        const type = typeSelect ? typeSelect.value : '';
        const valueTypeSpan = document.getElementById('valueType');
        const capWrapper = document.getElementById('capWrapper');
        
        if (type === '%') {
            if (valueTypeSpan) valueTypeSpan.textContent = '%';
            if (capWrapper) capWrapper.style.display = 'block';
        } else if (type === 'VND') {
            if (valueTypeSpan) valueTypeSpan.textContent = '₫';
            if (capWrapper) capWrapper.style.display = 'none';
        } else {
            if (valueTypeSpan) valueTypeSpan.textContent = '-';
            if (capWrapper) capWrapper.style.display = 'none';
        }
    }

    // Cập nhật khi thay đổi loại voucher
    document.addEventListener('DOMContentLoaded', function() {
        const typeSelect = document.getElementById('type');
        if (typeSelect) {
            // Cập nhật ngay khi trang load (cho trường hợp có old('type'))
            updateTypeDisplay();
            
            // Lắng nghe sự kiện thay đổi
            typeSelect.addEventListener('change', updateTypeDisplay);
        }
    });
</script>

@endsection

