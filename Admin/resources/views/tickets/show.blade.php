@extends('app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Chi tiết vé #{{ $ticket->id }}</h1>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.tickets.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Quay lại
            </a>
            <a href="{{ route('admin.tickets.edit', $ticket) }}" class="btn btn-warning">
                <i class="fas fa-edit me-1"></i> Chỉnh sửa
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Thông tin vé -->
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Thông tin vé</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>ID vé:</strong></td>
                                    <td>#{{ $ticket->id }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Trạng thái:</strong></td>
                                    <td>
                                        <span class="badge 
                                            @if($ticket->status == 'pending') bg-warning
                                            @elseif($ticket->status == 'confirmed') bg-success
                                            @elseif($ticket->status == 'cancelled') bg-danger
                                            @elseif($ticket->status == 'completed') bg-info
                                            @endif">
                                            @switch($ticket->status)
                                                @case('pending') Chờ xử lý @break
                                                @case('confirmed') Đã xác nhận @break
                                                @case('cancelled') Đã hủy @break
                                                @case('completed') Hoàn thành @break
                                                @default {{ $ticket->status }}
                                            @endswitch
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Thanh toán:</strong></td>
                                    <td>
                                        <span class="badge 
                                            @if($ticket->payment_status == 'pending') bg-warning
                                            @elseif($ticket->payment_status == 'paid') bg-success
                                            @elseif($ticket->payment_status == 'refunded') bg-danger
                                            @endif">
                                            @switch($ticket->payment_status)
                                                @case('pending') Chờ thanh toán @break
                                                @case('paid') Đã thanh toán @break
                                                @case('refunded') Đã hoàn tiền @break
                                                @default {{ $ticket->payment_status }}
                                            @endswitch
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Ngày tạo:</strong></td>
                                    <td>{{ $ticket->created_at->format('d/m/Y H:i:s') }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Khách hàng:</strong></td>
                                    <td>{{ $ticket->user->name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Email:</strong></td>
                                    <td>{{ $ticket->user->email ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Khuyến mãi:</strong></td>
                                    <td>{{ $ticket->promotion->name ?? 'Không có' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Cập nhật:</strong></td>
                                    <td>{{ $ticket->updated_at->format('d/m/Y H:i:s') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    @if($ticket->notes)
                    <div class="mt-3">
                        <strong>Ghi chú:</strong>
                        <p class="text-muted mt-1">{{ $ticket->notes }}</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Chi tiết sân -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Chi tiết sân đặt</h5>
                </div>
                <div class="card-body">
                    @if($ticket->items->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Sân</th>
                                        <th>Địa điểm</th>
                                        <th>Khung giờ</th>
                                        <th>Ngày</th>
                                        <th>Giá</th>
                                        <th>Trạng thái</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($ticket->items as $item)
                                    <tr>
                                        <td>
                                            <strong>{{ $item->court->name ?? 'N/A' }}</strong>
                                        </td>
                                        <td>
                                            {{ $item->court->venue->name ?? 'N/A' }}
                                        </td>
                                        <td>
                                            @if($item->booking && $item->booking->timeSlot)
                                                {{ \Carbon\Carbon::parse($item->booking->timeSlot->start_time)->format('H:i') }} - 
                                                {{ \Carbon\Carbon::parse($item->booking->timeSlot->end_time)->format('H:i') }}
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                        <td>
                                            @if($item->booking)
                                                {{ \Carbon\Carbon::parse($item->booking->date)->format('d/m/Y') }}
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                        <td>
                                            <strong class="text-success">
                                                {{ number_format($item->unit_price, 0, ',', '.') }} VNĐ
                                            </strong>
                                        </td>
                                        <td>
                                            @if($item->booking)
                                                <span class="badge 
                                                    @if($item->booking->status == 'pending') bg-warning
                                                    @elseif($item->booking->status == 'confirmed') bg-success
                                                    @elseif($item->booking->status == 'cancelled') bg-danger
                                                    @endif">
                                                    {{ ucfirst($item->booking->status) }}
                                                </span>
                                            @else
                                                <span class="badge bg-secondary">N/A</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-exclamation-circle fa-2x mb-3"></i>
                            <p>Không có sân nào trong vé này</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Tổng kết -->
        <div class="col-md-4">
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
                        <strong class="text-success fs-5">
                            {{ number_format($ticket->total_amount, 0, ',', '.') }} VNĐ
                        </strong>
                    </div>
                    
                    <div class="mt-3">
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
