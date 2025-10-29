@extends('app')

@section('content')
<div class="container py-4">
    <h2 class="mb-4">Chỉnh sửa Booking #{{ $booking->id }}</h2>

    {{-- Thông báo thành công --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Hiển thị lỗi --}}
    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('owner.bookings.update', $booking->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="row g-4">
            {{-- Thông tin khách hàng --}}
            <div class="col-md-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Thông tin khách hàng</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Tên:</strong> {{ $booking->user->name }}</p>
                        <p><strong>Email:</strong> {{ $booking->user->email }}</p>
                        <p><strong>Tổng tiền:</strong> {{ number_format($booking->total_amount) }}₫</p>
                    </div>
                </div>
            </div>

            {{-- Cập nhật trạng thái --}}
            <div class="col-md-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Cập nhật trạng thái</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="status" class="form-label">Trạng thái:</label>
                            <select name="status" id="status" class="form-select" required>
                                <option value="pending" {{ $booking->status == 'pending' ? 'selected' : '' }}>Chờ xác nhận</option>
                                <option value="confirmed" {{ $booking->status == 'confirmed' ? 'selected' : '' }}>Đã xác nhận</option>
                                <option value="cancelled" {{ $booking->status == 'cancelled' ? 'selected' : '' }}>Đã hủy</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="payment_status" class="form-label">Trạng thái thanh toán:</label>
                            <select name="payment_status" id="payment_status" class="form-select">
                                <option value="unpaid" {{ $booking->payment_status == 'unpaid' ? 'selected' : '' }}>Chưa thanh toán</option>
                                <option value="paid" {{ $booking->payment_status == 'paid' ? 'selected' : '' }}>Đã thanh toán</option>
                                <option value="refund" {{ $booking->payment_status == 'refund' ? 'selected' : '' }}>Hoàn tiền</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Chi tiết sân đặt --}}
        <div class="card border-0 shadow-sm mt-4">
            <div class="card-header bg-white">
                <h5 class="mb-0">Chi tiết sân đặt</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped align-middle mb-0">
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

        {{-- Nút hành động căn giữa --}}
        <div class="d-flex justify-content-center gap-3 mt-4">
            <button type="submit" class="btn btn-primary px-4">
                <i class="fas fa-save me-1"></i> Cập nhật
            </button>
            <a href="{{ route('owner.bookings.show', $booking->id) }}" class="btn btn-secondary px-4">
                <i class="fas fa-times me-1"></i> Hủy
            </a>
        </div>
    </form>
</div>
@endsection
