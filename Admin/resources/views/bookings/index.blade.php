@extends('app')

@section('content')
<div class="container-fluid py-4">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 fw-bold">Danh sách Booking</h1>
            <p class="text-muted mb-0">Quản lý các đơn đặt sân của khách hàng.</p>
        </div>
    </div>

    {{-- Danh sách booking --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center">#</th>
                            <th>Khách hàng</th>
                            <th>Địa điểm</th>
                            <th>Tổng tiền</th>
                            <th>Trạng thái</th>
                            <th>Thanh toán</th>
                            <th>Ngày đặt</th>
                            <th class="text-center">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($bookings as $b)
                        <tr>
                            <td class="text-center">{{ $b->id }}</td>
                            <td>{{ $b->user->name }}</td>
                            <td>{{ $b->items->first()?->court?->venue?->name ?? 'N/A' }}</td>
                            <td>{{ number_format($b->total_amount) }}₫</td>

                            {{-- Trạng thái --}}
                            <td>
                                <span class="badge 
                                    {{ $b->status === 'confirmed' ? 'bg-success-subtle border border-success-subtle text-success-emphasis' : 
                                    ($b->status === 'cancelled' ? 'bg-danger-subtle border border-danger-subtle text-danger-emphasis' : 
                                    'bg-warning-subtle border border-warning-subtle text-warning-emphasis') 
                                }} rounded-pill px-3 py-2">
                                    {{ ucfirst($b->status) }}
                                </span>
                            </td>

                            {{-- Thanh toán --}}
                            <td>
                                <span class="badge 
                                    {{ $b->payment_status === 'paid' ? 'bg-success-subtle border border-success-subtle text-success-emphasis' : 
                                    'bg-warning-subtle border border-warning-subtle text-warning-emphasis' 
                                }} rounded-pill px-3 py-2">
                                    {{ ucfirst($b->payment_status) }}
                                </span>
                            </td>

                            <td>{{ $b->created_at->format('d/m/Y H:i') }}</td>

                            <td class="text-center">
                                <a href="{{ route('admin.bookings.show', $b->id) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye me-1"></i> Chi tiết
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                Không có đơn đặt sân nào.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Phân trang --}}
        <div class="card-footer bg-white border-0 d-flex justify-content-end">
            {{ $bookings->links() }}
        </div>
    </div>
</div>
@endsection
