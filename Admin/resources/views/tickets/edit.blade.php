@extends('app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Chỉnh sửa vé #{{ $ticket->id }}</h1>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.tickets.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Quay lại
            </a>
            <a href="{{ route('admin.tickets.show', $ticket) }}" class="btn btn-outline-primary">
                <i class="fas fa-eye me-1"></i> Xem chi tiết
            </a>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Có lỗi xảy ra!</strong>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Thông tin vé</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.tickets.update', $ticket) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="status" class="form-label">Trạng thái</label>
                                    <select class="form-select" id="status" name="status" required>
                                        <option value="pending" {{ old('status', $ticket->status) == 'pending' ? 'selected' : '' }}>
                                            Chờ xử lý
                                        </option>
                                        <option value="confirmed" {{ old('status', $ticket->status) == 'confirmed' ? 'selected' : '' }}>
                                            Đã xác nhận
                                        </option>
                                        <option value="cancelled" {{ old('status', $ticket->status) == 'cancelled' ? 'selected' : '' }}>
                                            Đã hủy
                                        </option>
                                        <option value="completed" {{ old('status', $ticket->status) == 'completed' ? 'selected' : '' }}>
                                            Hoàn thành
                                        </option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="payment_status" class="form-label">Trạng thái thanh toán</label>
                                    <select class="form-select" id="payment_status" name="payment_status" required>
                                        <option value="pending" {{ old('payment_status', $ticket->payment_status) == 'pending' ? 'selected' : '' }}>
                                            Chờ thanh toán
                                        </option>
                                        <option value="paid" {{ old('payment_status', $ticket->payment_status) == 'paid' ? 'selected' : '' }}>
                                            Đã thanh toán
                                        </option>
                                        <option value="refunded" {{ old('payment_status', $ticket->payment_status) == 'refunded' ? 'selected' : '' }}>
                                            Đã hoàn tiền
                                        </option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="notes" class="form-label">Ghi chú</label>
                            <textarea class="form-control" id="notes" name="notes" rows="4" 
                                placeholder="Nhập ghi chú về vé...">{{ old('notes', $ticket->notes) }}</textarea>
                        </div>
                        
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save me-1"></i> Lưu thay đổi
                            </button>
                            <a href="{{ route('admin.tickets.show', $ticket) }}" class="btn btn-secondary">
                                Hủy bỏ
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <!-- Thông tin khách hàng -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Thông tin khách hàng</h5>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <strong>Tên:</strong> {{ $ticket->user->name ?? 'N/A' }}
                    </div>
                    <div class="mb-2">
                        <strong>Email:</strong> {{ $ticket->user->email ?? 'N/A' }}
                    </div>
                    <div class="mb-2">
                        <strong>Khuyến mãi:</strong> {{ $ticket->promotion->name ?? 'Không có' }}
                    </div>
                    <div class="mb-2">
                        <strong>Ngày tạo:</strong> {{ $ticket->created_at->format('d/m/Y H:i') }}
                    </div>
                </div>
            </div>
            
            <!-- Tổng kết -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Tổng kết</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Tạm tính:</span>
                        <span>{{ number_format($ticket->subtotal, 0, ',', '.') }} VNĐ</span>
                    </div>
                    
                    @if($ticket->discount_amount > 0)
                    <div class="d-flex justify-content-between mb-2 text-success">
                        <span>Giảm giá:</span>
                        <span>-{{ number_format($ticket->discount_amount, 0, ',', '.') }} VNĐ</span>
                    </div>
                    @endif
                    
                    <hr>
                    <div class="d-flex justify-content-between">
                        <strong>Tổng cộng:</strong>
                        <strong class="text-success">
                            {{ number_format($ticket->total_amount, 0, ',', '.') }} VNĐ
                        </strong>
                    </div>
                    
                    <div class="mt-2">
                        <small class="text-muted">
                            Số sân: <strong>{{ $ticket->items->count() }}</strong>
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
