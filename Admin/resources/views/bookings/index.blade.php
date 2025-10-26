@extends('app')

@section('content')
<style>
    .btn-primary {
        --bs-btn-hover-bg: #2d6a2d;
        --bs-btn-hover-border-color: #2d6a2d;
    }

    .btn-accent {
        --bs-btn-bg: #f97316;
        --bs-btn-border-color: #f97316;
        --bs-btn-hover-bg: #ea580c;
        --bs-btn-hover-border-color: #ea580c;
        --bs-btn-color: #fff;
    }

    .table-primary-green {
        background-color: var(--bs-primary);
        color: #fff;
    }

    .badge.bg-draft {
        background-color: #f59e0b !important;
        color: #fff !important;
    }

    /* Màu chủ đạo */
    :root {
        --bs-primary-green: #348738;
        --bs-primary-green-dark: #2d6a2d;
    }

    .bg-primary-green {
        background-color: var(--bs-primary-green) !important;
    }

    .btn-primary-green {
        background-color: var(--bs-primary-green);
        border-color: var(--bs-primary-green);
        color: #fff;
    }

    .btn-primary-green:hover {
        background-color: var(--bs-primary-green-dark);
        border-color: var(--bs-primary-green-dark);
    }

    /* Modal chi tiết ticket */
    .ticket-detail-modal {
        border-radius: 12px;
        overflow: hidden;
    }

    .ticket-detail-modal .info-box {
        background-color: #fff;
        border-radius: 10px;
        border: 1px solid #e5e7eb;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }

    .ticket-detail-modal .info-box h6 {
        font-size: 0.9rem;
        font-weight: 600;
        color: #666;
    }

    .ticket-detail-modal .booking-item {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .ticket-detail-modal .booking-item:hover {
        transform: scale(1.01);
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }
</style>

<div class="mt-4">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white border-0 pb-0">
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <h1 class="h3 mb-0 fw-bold">Danh sách đơn đặt sân</h1>

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

        <div class="card-body pt-3 pb-0">
            @php
            $statusOptions = [
            '' => ['label' => 'Tất cả', 'class' => 'btn-outline-secondary'],
            'pending' => ['label' => 'Chờ xác nhận', 'class' => 'btn-warning text-white'],
            'confirmed' => ['label' => 'Đã xác nhận', 'class' => 'btn-success text-white'],
            'cancelled' => ['label' => 'Đã hủy', 'class' => 'btn-danger text-white'],
            'completed' => ['label' => 'Hoàn thành', 'class' => 'btn-primary text-white'],
            ];
            $currentStatus = request('status') ?? '';
            @endphp

            <div class="d-flex flex-wrap gap-2">
                @foreach($statusOptions as $key => $data)
                <a href="{{ route('admin.bookings.index', array_merge(request()->except('page'), ['status' => $key ?: null])) }}"
                    class="btn {{ $currentStatus === $key ? $data['class'] : 'btn-outline-secondary' }}">
                    {{ $data['label'] }}
                </a>
                @endforeach
            </div>
        </div>


        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover align-middle">
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
                            <th class="text-center">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tickets as $ticket)
                        <tr>
                            <td>{{ $ticket->id }}</td>
                            <td class="text-nowrap">{{ $ticket->user->name ?? 'N/A' }}</td>
                            <td class="text-nowrap">{{ number_format($ticket->subtotal, 0, '.', ',') }}₫</td>
                            <td class="text-nowrap">{{ number_format($ticket->discount_amount, 0, '.', ',') }}₫</td>
                            <td class="text-nowrap fw-bold text-end">{{ number_format($ticket->total_amount, 0, '.', ',') }}₫</td>

                            <td class="text-center">
                                <span class="badge
                                    @if($ticket->status == 'draft') bg-draft
                                    @elseif($ticket->status == 'confirmed') bg-success
                                    @elseif($ticket->status == 'cancelled') bg-danger
                                    @elseif($ticket->status == 'completed') bg-primary
                                    @else bg-secondary
                                    @endif">
                                    {{ ucfirst($ticket->status) }}
                                </span>
                            </td>

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

                            <td style="max-width: 150px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $ticket->notes ?? '' }}">
                                {{ $ticket->notes ?? '-' }}
                            </td>

                            <td class="text-nowrap">{{ $ticket->created_at->format('d-m-Y H:i') }}</td>

                            <td class="text-center">
                                <a href="" class="btn btn-sm btn-outline-primary w-75" data-bs-toggle="modal" data-bs-target="#ticketModal{{ $ticket->id }}">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>

                        <!-- Modal chi tiết Ticket -->
                        <div class="modal fade" id="ticketModal{{ $ticket->id }}" tabindex="-1" aria-labelledby="ticketModalLabel{{ $ticket->id }}" aria-hidden="true">
                            <div class="modal-dialog modal-xl modal-dialog-centered">
                                <div class="modal-content ticket-detail-modal border-0 shadow-lg">
                                    <div class="modal-header border-0">
                                        <h3 class="modal-title">
                                            <i class="fas fa-ticket-alt me-2"></i>Chi tiết Đơn #{{ $ticket->id }}
                                        </h3>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>

                                    <!-- ✅ Bắt đầu form -->
                                    <form method="POST" action="{{ route('admin.bookings.update', $ticket->id) }}">
                                        @csrf
                                        @method('PUT')

                                        <div class="modal-body bg-light">
                                            <!-- Thông tin khách hàng & trạng thái -->
                                            <div class="row g-3 mb-4">
                                                <div class="col-md-4">
                                                    <div class="info-box p-3 h-100">
                                                        <h6 class="text-muted mb-2"><i class="fas fa-user me-2"></i>Khách hàng</h6>
                                                        <p class="mb-1 fw-semibold">{{ $ticket->user->name ?? 'N/A' }}</p>
                                                        <p class="small mb-0 text-muted">{{ $ticket->user->email ?? '' }}</p>
                                                    </div>
                                                </div>

                                                <div class="col-md-4">
                                                    <div class="info-box p-3 h-100">
                                                        <h6 class="text-muted mb-2"><i class="fas fa-info-circle me-2"></i>Trạng thái đơn</h6>
                                                        @php
                                                        // Quy tắc chuyển trạng thái (đúng với Controller)
                                                        $allowedTransitions = [
                                                        'pending' => ['pending', 'confirmed', 'cancelled'],
                                                        'confirmed' => ['confirmed', 'completed', 'cancelled'],
                                                        'completed' => ['completed'], // khóa
                                                        'cancelled' => ['cancelled'], // khóa
                                                        ];
                                                        @endphp

                                                        <select name="status" class="form-select" {{ in_array($ticket->status, ['completed','cancelled']) ? 'disabled' : '' }}>
                                                            @foreach(['pending' => 'Chờ xác nhận', 'confirmed' => 'Đã xác nhận', 'cancelled' => 'Đã hủy', 'completed' => 'Hoàn thành'] as $value => $label)
                                                            @if(in_array($value, $allowedTransitions[$ticket->status]))
                                                            <option value="{{ $value }}" {{ $ticket->status == $value ? 'selected' : '' }}>{{ $label }}</option>
                                                            @endif
                                                            @endforeach
                                                        </select>

                                                        <h6 class="text-muted mb-2 mt-3"><i class="fas fa-wallet me-2"></i>Thanh toán</h6>
                                                        <select name="payment_status" class="form-select" {{ in_array($ticket->status, ['completed','cancelled']) ? 'disabled' : '' }}>
                                                            <option value="unpaid" {{ $ticket->payment_status == 'unpaid' ? 'selected' : '' }}>Chưa thanh toán</option>
                                                            <option value="paid" {{ $ticket->payment_status == 'paid' ? 'selected' : '' }}>Đã thanh toán</option>
                                                            <option value="refunded" {{ $ticket->payment_status == 'refunded' ? 'selected' : '' }}>Hoàn tiền</option>
                                                        </select>

                                                    </div>
                                                </div>

                                                <div class="col-md-4">
                                                    <div class="info-box p-3 h-100">
                                                        <h6 class="text-muted mb-2"><i class="fas fa-calendar me-2"></i>Ngày tạo</h6>
                                                        <p class="mb-3">{{ $ticket->created_at->format('d/m/Y H:i') }}</p>
                                                        <h6 class="text-muted mb-1"><i class="fas fa-money-bill-wave me-2"></i>Tổng tiền</h6>
                                                        <p class="fs-5 fw-bold text-danger mb-0">{{ number_format($ticket->total_amount, 0, '.', ',') }}₫</p>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Chi tiết Booking -->
                                            <h5 class="fw-bold mb-3"><i class="fas fa-calendar-check me-2 text-success"></i>Chi tiết Sân ({{ $ticket->items->count() }})</h5>

                                            <div class="booking-list">
                                                @forelse($ticket->items as $item)
                                                <div class="booking-item border rounded-3 p-3 mb-2 bg-white shadow-sm">
                                                    <div class="d-flex justify-content-between align-items-center flex-wrap">
                                                        <div>
                                                            <p class="fw-semibold mb-1">{{ $item->booking->court->name ?? 'N/A' }}</p>
                                                            <small class="text-muted">
                                                                {{ $item->booking->date ?? '-' }} | {{ $item->booking->timeSlot->label ?? '-' }}
                                                            </small>
                                                        </div>
                                                        <div class="text-end">
                                                            <p class="fw-bold text-dark mb-0">{{ number_format($item->unit_price, 0, '.', ',') }}₫</p>
                                                            @if($item->discount_amount > 0)
                                                            <small class="text-danger">(-{{ number_format($item->discount_amount, 0, '.', ',') }}₫)</small>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                                @empty
                                                <div class="alert alert-secondary text-center">Không có chi tiết booking.</div>
                                                @endforelse
                                            </div>

                                            <!-- Ghi chú -->
                                            <div class="mt-4">
                                                <h6 class="fw-bold mb-2"><i class="fas fa-sticky-note me-2 text-warning"></i>Ghi chú</h6>
                                                <p name="notes" class="form-control" rows="3">{{ $ticket->notes }}</p>
                                            </div>
                                        </div>

                                        <div class="modal-footer border-0 bg-white d-flex justify-content-end gap-2">
                                            <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">
                                                <i class="fas fa-times me-1"></i> Đóng
                                            </button>
                                            <button type="submit" class="btn btn-primary px-4">
                                                <i class="fas fa-save me-1"></i> Lưu thay đổi
                                            </button>
                                        </div>
                                    </form>
                                    <!-- ✅ Kết thúc form -->
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