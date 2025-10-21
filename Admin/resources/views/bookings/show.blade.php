@extends('app')

@section('content')
<div class="container">
    <h2>Chi tiết Booking #{{ $booking->id }}</h2>

    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5>Thông tin khách hàng</h5>
                </div>
                <div class="card-body">
                    <p><strong>Tên:</strong> {{ $booking->user->name }}</p>
                    <p><strong>Email:</strong> {{ $booking->user->email }}</p>
                    <p><strong>Số điện thoại:</strong> {{ $booking->user->phone ?? 'N/A' }}</p>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5>Thông tin đơn hàng</h5>
                </div>
                <div class="card-body">
                    <p><strong>Trạng thái:</strong>
                        <span class="badge badge-{{ $booking->status == 'confirmed' ? 'success' : ($booking->status == 'cancelled' ? 'danger' : 'warning') }}">
                            {{ ucfirst($booking->status) }}
                        </span>
                    </p>
                    <p><strong>Thanh toán:</strong>
                        <span class="badge badge-{{ $booking->payment_status == 'paid' ? 'success' : 'warning' }}">
                            {{ ucfirst($booking->payment_status) }}
                        </span>
                    </p>
                    <p><strong>Tổng tiền:</strong> {{ number_format($booking->total_amount) }}₫</p>
                    <p><strong>Giảm giá:</strong> {{ number_format($booking->discount_amount) }}₫</p>
                    <p><strong>Ngày đặt:</strong> {{ $booking->created_at->format('d/m/Y H:i') }}</p>
                    @if($booking->notes)
                    <p><strong>Ghi chú:</strong> {{ $booking->notes }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5>Chi tiết sân đặt</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Sân</th>
                                    <th>Địa điểm</th>
                                    <th>Ngày</th>
                                    <th>Giờ</th>
                                    <th>Giá</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($booking->items as $item)
                                <tr>
                                    <td>{{ $item->court->name }}</td>
                                    <td>{{ $item->court->venue->name }}</td>
                                    <td>{{ \Carbon\Carbon::parse($item->date)->format('d/m/Y') }}</td>
                                    <td>{{ $item->slot->start_time }} - {{ $item->slot->end_time }}</td>
                                    <td>{{ number_format($item->unit_price) }}₫</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-12 text-center">
            <a href="{{ route('admin.bookings.index') }}" class="btn btn-secondary mx-2">Quay lại</a>
            <a href="{{ route('admin.bookings.edit', $booking->id) }}" class="btn btn-primary mx-2">Chỉnh sửa</a>
        </div>
    </div>

</div>
@endsection