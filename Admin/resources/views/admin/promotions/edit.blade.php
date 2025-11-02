@extends('app')
@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="card-title">Chỉnh sửa Voucher: {{ $promotion->code }}</h3>
                        <a href="{{ route('admin.promotions.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Quay lại
                        </a>
                    </div>
                </div>
                
                <div class="card-body">
                    <!-- Alerts -->
                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <h5><i class="fas fa-exclamation-triangle me-2"></i>Vui lòng kiểm tra lại thông tin:</h5>
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
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
                                           class="form-control @error('code') is-invalid @enderror" 
                                           id="code" 
                                           name="code" 
                                           value="{{ old('code', $promotion->code) }}" 
                                           placeholder="VD: SALE2024" 
                                           required>
                                    <small class="form-text text-muted">Mã voucher sẽ được tự động chuyển thành chữ in hoa</small>
                                    @error('code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="type">Loại voucher <span class="text-danger">*</span></label>
                                    <select class="form-control @error('type') is-invalid @enderror" 
                                            id="type" 
                                            name="type" 
                                            required>
                                        <option value="">Chọn loại voucher</option>
                                        <option value="%" {{ old('type', $promotion->type) == '%' ? 'selected' : '' }}>Phần trăm (%)</option>
                                        <option value="VND" {{ old('type', $promotion->type) == 'VND' ? 'selected' : '' }}>Tiền mặt (VND)</option>
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
                                    <label for="value">Giá trị <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="number" 
                                               step="0.01" 
                                               min="0"
                                               class="form-control @error('value') is-invalid @enderror" 
                                               id="value" 
                                               name="value" 
                                               value="{{ old('value', $promotion->value) }}" 
                                               placeholder="VD: 10 hoặc 50000" 
                                               required>
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
                                    @error('value')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="usage_limit">Giới hạn sử dụng</label>
                                    <input type="number" 
                                           min="0"
                                           class="form-control @error('usage_limit') is-invalid @enderror" 
                                           id="usage_limit" 
                                           name="usage_limit" 
                                           value="{{ old('usage_limit', $promotion->usage_limit) }}" 
                                           placeholder="0 = Không giới hạn">
                                    <small class="form-text text-muted">Để 0 hoặc trống = không giới hạn số lần sử dụng</small>
                                    @error('usage_limit')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="start_at">Ngày bắt đầu <span class="text-danger">*</span></label>
                                    <input type="datetime-local" 
                                           class="form-control @error('start_at') is-invalid @enderror" 
                                           id="start_at" 
                                           name="start_at" 
                                           value="{{ old('start_at', \Carbon\Carbon::parse($promotion->start_at, 'UTC')->setTimezone('Asia/Ho_Chi_Minh')->format('Y-m-d\TH:i')) }}" 
                                           required>
                                    @error('start_at')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="end_at">Ngày kết thúc <span class="text-danger">*</span></label>
                                    <input type="datetime-local" 
                                           class="form-control @error('end_at') is-invalid @enderror" 
                                           id="end_at" 
                                           name="end_at" 
                                           value="{{ old('end_at', \Carbon\Carbon::parse($promotion->end_at, 'UTC')->setTimezone('Asia/Ho_Chi_Minh')->format('Y-m-d\TH:i')) }}" 
                                           required>
                                    @error('end_at')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
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
    // Cập nhật hiển thị đơn vị giá trị khi thay đổi loại voucher
    document.getElementById('type').addEventListener('change', function() {
        const type = this.value;
        const valueTypeSpan = document.getElementById('valueType');
        
        if (type === '%') {
            valueTypeSpan.textContent = '%';
        } else if (type === 'VND') {
            valueTypeSpan.textContent = '₫';
        } else {
            valueTypeSpan.textContent = '-';
        }
    });

    // Khi start_at thay đổi, cập nhật min của end_at
    document.getElementById('start_at').addEventListener('change', function() {
        const startDate = this.value;
        if (startDate) {
            document.getElementById('end_at').setAttribute('min', startDate);
        }
    });
</script>

@endsection

