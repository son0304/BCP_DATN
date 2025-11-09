@extends('app')
@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="card-title">Chỉnh sửa Voucher: {{ $promotion->code }}</h3>
                        <div>
                            <a href="{{ route('admin.promotions.edit', $promotion) }}" class="btn btn-warning">
                                <i class="fas fa-edit"></i> Chỉnh sửa
                            </a>
                            <a href="{{ route('admin.promotions.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Quay lại
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger mb-4" role="alert" style="display: block !important;">
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

                    <form method="POST" action="{{ route('admin.promotions.update', $promotion) }}">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="code">Mã voucher <span class="text-danger">*</span></label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="code" 
                                           name="code" 
                                           value="{{ old('code', $promotion->code) }}" 
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
                                        <option value="%" {{ old('type', $promotion->type) == '%' ? 'selected' : '' }}>Phần trăm (%)</option>
                                        <option value="VND" {{ old('type', $promotion->type) == 'VND' ? 'selected' : '' }}>Tiền mặt (VND)</option>
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
                                               value="{{ old('value', $promotion->value) }}" 
                                               placeholder="VD: 10 hoặc 50000">
                                        <span class="input-group-text" id="valueType">
                                            @if(old('type', $promotion->type) == '%')
                                                %
                                            @elseif(old('type', $promotion->type) == 'VND')
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
                                <div class="form-group mb-3" id="capWrapper" style="display: {{ old('type', $promotion->type) == '%' ? '' : 'none' }};">
                                    <label for="max_discount_amount">Số tiền giảm tối đa (VND) <span class="text-danger">*</span></label>
                                    <input type="number" 
                                           class="form-control" 
                                           id="max_discount_amount" 
                                           name="max_discount_amount" 
                                           value="{{ old('max_discount_amount', $promotion->max_discount_amount) }}" 
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
                                           value="{{ old('usage_limit', $promotion->usage_limit) }}" 
                                           min="1"
                                           placeholder="VD: 100">
                                    <small class="form-text text-muted">Số lượt sử dụng tối đa cho voucher này (phải lớn hơn 0 và không được nhỏ hơn số lần đã sử dụng)</small>
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
                                           value="{{ old('start_at', \Carbon\Carbon::parse($promotion->start_at, 'UTC')->setTimezone('Asia/Ho_Chi_Minh')->format('Y-m-d\TH:i')) }}">
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="end_at">Ngày kết thúc <span class="text-danger">*</span></label>
                                    <input type="datetime-local" 
                                           class="form-control" 
                                           id="end_at" 
                                           name="end_at" 
                                           value="{{ old('end_at', \Carbon\Carbon::parse($promotion->end_at, 'UTC')->setTimezone('Asia/Ho_Chi_Minh')->format('Y-m-d\TH:i')) }}">
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Lưu ý:</strong> Voucher này đã được sử dụng <strong>{{ $promotion->used_count }}</strong> lần. 
                            Bạn không thể giảm giới hạn sử dụng xuống dưới số lần đã sử dụng.
                        </div>

                        <div class="form-group mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Cập nhật voucher
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

