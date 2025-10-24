@extends('app')

{{-- Thêm 1 <style> nhỏ để định nghĩa màu xanh lá/cam --}}
@section('content')
<style>
    /* 1. Định nghĩa màu Xanh Lá làm màu "Primary" của Bootstrap */
    :root {
        --bs-primary: #348738;
        --bs-primary-rgb: 52, 135, 56;
        --bs-primary-dark: #2d6a2d; /* Màu đậm hơn khi hover */
        --bs-primary-border-subtle: #d1e7dd; /* Màu nhạt cho border/bg */
    }

    /* 2. Ghi đè nút "Primary" để dùng màu đậm hơn khi hover */
    .btn-primary {
        --bs-btn-hover-bg: #2d6a2d;
        --bs-btn-hover-border-color: #2d6a2d;
    }

    /* 3. Tạo 1 class mới cho màu Cam (CTA) */
    .btn-accent {
        --bs-btn-bg: #f97316; /* Tailwind orange-500 */
        --bs-btn-border-color: #f97316;
        --bs-btn-hover-bg: #ea580c; /* Tailwind orange-600 */
        --bs-btn-hover-border-color: #ea580c;
        --bs-btn-color: #fff;
    }

    /* 4. Tùy chỉnh màu header của bảng */
    .table-primary-green {
        background-color: var(--bs-primary);
        color: #fff;
    }

    /* 5. Tùy chỉnh màu badge "draft" (nháp) sang màu vàng/cam cho dễ thấy */
    .badge.bg-draft {
        background-color: #f59e0b !important; /* Tailwind amber-500 */
        color: #fff !important;
    }
</style>

<div class="mt-4">

    {{-- Đóng gói mọi thứ trong Card cho gọn gàng --}}
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white border-0 pb-0">
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <h1 class="h4 mb-3 mb-md-0">Dashboard Tickets</h1>

                <form method="GET" class="mb-3" style="min-width: 300px;">
                    <div class="input-group">
                        <input type="text" name="search" class="form-control" placeholder="Tìm theo tên khách hàng" value="{{ $search ?? '' }}">
                        <button class="btn btn-primary" type="submit">
                            <i class="fas fa-search"></i> Tìm
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover align-middle">

                    {{-- Header bảng (màu xanh lá) --}}
                    <thead class="table-primary-green text-nowrap">
                        <tr>
                            <th>ID</th>
                            <th>Khách hàng</th>
                            <th>Subtotal</th>
                            <th>Discount</th>
                            <th class="text-end">Total</th>
                            <th class="text-center">Trạng thái</th>
                            <th class="text-center">Thanh toán</th>
                            <th>Ghi chú</th>
                            <th>Ngày tạo</th>
                            <th class="text-center">Chi tiết</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tickets as $ticket)
                        <tr>
                            <td>#{{ $ticket->id }}</td>
                            <td class="text-nowrap">{{ $ticket->user->name ?? 'N/A' }}</td>
                            <td class="text-nowrap">{{ number_format($ticket->subtotal, 0, '.', ',') }}₫</td>
                            <td class="text-nowrap">{{ number_format($ticket->discount_amount, 0, '.', ',') }}₫</td>
                            <td class="text-nowrap fw-bold text-end">{{ number_format($ticket->total_amount, 0, '.', ',') }}₫</td>

                            {{-- Trạng thái Ticket --}}
                            <td class="text-center">
                                <span class="badge
                                    @if($ticket->status == 'draft') bg-draft
                                    @elseif($ticket->status == 'confirmed') bg-success
                                    @elseif($ticket->status == 'cancelled') bg-danger
                                    @else bg-secondary
                                    @endif">
                                    {{ ucfirst($ticket->status) }}
                                </span>
                            </td>

                            {{-- Trạng thái Thanh toán --}}
                            <td class="text-center">
                                <span class="badge
                                    @if($ticket->payment_status == 'unpaid') bg-danger
                                    @elseif($ticket->payment_status == 'paid') bg-success
                                    @elseif($ticket->payment_status == 'refunded') bg-info text-dark
                                    @else bg-secondary
                                    @endif">
                                    {{ ucfirst($ticket->payment_status) }}
                                </span>
                            </td>

                            {{-- Cắt bớt Ghi chú dài cho gọn --}}
                            <td style="max-width: 150px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $ticket->notes ?? '' }}">
                                {{ $ticket->notes ?? '-' }}
                            </td>

                            <td class="text-nowrap">{{ $ticket->created_at->format('d-m-Y H:i') }}</td>

                            {{-- Nút "Xem" (màu cam) để mở Modal --}}
                            <td class="text-center">
                                <button class="btn btn-sm btn-accent"
                                        data-bs-toggle="modal"
                                        data-bs-target="#ticketModal{{ $ticket->id }}">
                                    Xem
                                </button>
                            </td>
                        </tr>

                        {{-- MODAL CHO MỖI TICKET (Đặt ngay sau <tr>) --}}
                        <div class="modal fade" id="ticketModal{{ $ticket->id }}" tabindex="-1" aria-labelledby="ticketModalLabel{{ $ticket->id }}" aria-hidden="true">
                            <div class="modal-dialog modal-lg modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="ticketModalLabel{{ $ticket->id }}">
                                            Chi tiết Ticket #{{ $ticket->id }}
                                        </h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <strong>Khách hàng:</strong> {{ $ticket->user->name ?? 'N/A' }}<br>
                                            <strong>Tổng tiền:</strong>
                                            {{-- Dùng màu xanh lá cho tổng tiền --}}
                                            <span class="fw-bold fs-5 text-primary">{{ number_format($ticket->total_amount, 0, '.', ',') }}₫</span>
                                        </div>

                                        <h6 class="mt-4">Chi tiết Bookings ({{ $ticket->items->count() }})</h6>
                                        <ul class="list-group list-group-flush">
                                            @forelse($ticket->items as $item)
                                            <li class="list-group-item d-flex justify-content-between align-items-center p-2">
                                                <div>
                                                    <strong>Sân:</strong> {{ $item->booking->court->name ?? 'N/A' }}
                                                    <small class="d-block text-muted">
                                                        {{ $item->booking->date ?? '-' }} | {{ $item->booking->timeSlot->label ?? '-' }} ({{ $item->booking->timeSlot->start_time ?? '-' }} - {{ $item->booking->timeSlot->end_time ?? '-' }})
                                                    </Gmall>
                                                </div>
                                                <div class="text-end text-nowrap">
                                                    <strong>{{ number_format($item->unit_price, 0, '.', ',') }}₫</strong>
                                                    @if($item->discount_amount > 0)
                                                        <br><small class="text-danger">(-{{ number_format($item->discount_amount, 0, '.', ',') }}₫)</small>
                                                    @endif
                                                </div>
                                            </li>
                                            @empty
                                            <li class="list-group-item">Không có chi tiết booking.</li>
                                            @endforelse
                                        </ul>

                                        @if($ticket->notes)
                                        <h6 class="mt-4">Ghi chú</h6>
                                        {{-- Dùng màu xanh lá nhạt cho nền ghi chú --}}
                                        <p class="p-2 bg-primary-subtle rounded border border-primary-subtle">{{ $ticket->notes }}</p>
                                        @endif
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                                        {{-- Nút hành động chính (nếu có) sẽ là màu xanh lá --}}
                                        {{-- <button type="button" class="btn btn-primary">Xác nhận</button> --}}
                                    </div>
                                </div>
                            </div>
                        </div>

                        @empty
                        <tr>
                            <td colspan="10" class="text-center p-4">
                                <p class="mb-0">Không tìm thấy ticket nào.</p>
                                @if($search)
                                    <p class="mb-0 text-muted">Không có kết quả cho tìm kiếm: "{{ $search }}"</p>
                                @endif
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($tickets->hasPages())
                <div class="d-flex justify-content-center mt-3">
                    {{ $tickets->withQueryString()->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
