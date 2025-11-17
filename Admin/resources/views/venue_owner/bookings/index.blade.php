@extends('app')

@section('content')

<div class="mt-4">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white border-0 pb-0">
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <h1 class="h3 mb-0 fw-bold">Danh sách đơn đặt sân</h1>

                <form method="GET" class="mb-3 row g-2">
                    <div class="col-12 col-md">
                        <input type="text" name="search" class="form-control" placeholder="Tìm theo tên khách hàng" value="{{ $search ?? '' }}">
                    </div>
                  
                    <div class="col-12 col-md-auto">
                        <button class="btn btn-primary w-100" type="submit"><i class="fas fa-search"></i> Tìm</button>
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
                <a href="{{ route('owner.bookings.index', array_merge(request()->except('page'), ['status' => $key ?: null])) }}"
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
                        <tr class="text-center">
                            <th>ID</th>
                            <th>Khách hàng</th>
                            <th>Sân</th>
                            <th>Giá</th>
                            <th>Giảm giá</th>
                            <th>Tổng</th>
                            <th>Trạng thái</th>
                            <th>Thanh toán</th>
                            <th>Ghi chú</th>
                            <th>Ngày tạo</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tickets as $ticket)
                        <tr class="text-center">
                            <td>{{ $ticket->id }}</td>
                            <td class="text-nowrap text-center">
                                <span class="px-2 py-1 text-success fw-bold">
                                    {{ $ticket->user->name ?? 'N/A' }}
                                </span>
                            </td>
                            <td>
                                @php
                                $uniqueVenues = $ticket->items
                                ->pluck('booking.court.venue')
                                ->filter() // loại bỏ null
                                ->unique('id'); // lọc trùng theo id
                                @endphp

                                @foreach($uniqueVenues as $venue)
                                <span class="badge bg-warning text-dark mb-1">
                                    {{ $venue->name }}
                                </span>
                                @endforeach
                            </td>
                            <td class="text-nowrap text-center">{{ number_format($ticket->subtotal,0,'.',',') }}₫</td>
                            <td class="text-nowrap text-center" style="color: red;">{{ number_format($ticket->discount_amount,0,'.',',') }}₫</td>
                            <td class="text-nowrap fw-bold text-center">{{ number_format($ticket->total_amount,0,'.',',') }}₫</td>
                            <td>
                                @php
                                $statusLabels = [
                                'pending' => 'Chờ xác nhận',
                                'confirmed' => 'Đã xác nhận',
                                'completed' => 'Hoàn thành',
                                'cancelled' => 'Đã hủy',
                                ];
                                $statusText = $statusLabels[$ticket->status] ?? 'Không xác định';
                                @endphp

                                <span class="badge
        @if($ticket->status=='pending') bg-warning
        @elseif($ticket->status=='confirmed') bg-success
        @elseif($ticket->status=='completed') bg-primary
        @elseif($ticket->status=='cancelled') bg-danger
        @else bg-secondary @endif">
                                    {{ $statusText }}
                                </span>
                            </td>

                            <td>
                                @php
                                $paymentLabels = [
                                'unpaid' => 'Chưa thanh toán',
                                'paid' => 'Đã thanh toán',
                                'refunded' => 'Đã hoàn tiền',
                                ];
                                $paymentText = $paymentLabels[$ticket->payment_status] ?? 'Không xác định';
                                @endphp

                                <span class="badge
        @if($ticket->payment_status=='unpaid') bg-danger
        @elseif($ticket->payment_status=='paid') bg-success
        @elseif($ticket->payment_status=='refunded') bg-info text-dark
        @else bg-secondary @endif">
                                    {{ $paymentText }}
                                </span>
                            </td>
                            <td style="max-width:150px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;" title="{{ $ticket->notes ?? '' }}">
                                {{ $ticket->notes ?? '-' }}
                            </td>
                            <td class="text-nowrap text-center">{{ $ticket->created_at->format('d-m-Y H:i') }}</td>
                            <td>
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
                                        <h3 class="modal-title"><i class="fas fa-ticket-alt me-2"></i>Chi tiết Đơn #{{ $ticket->id }}</h3>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <form method="POST" action="{{ route('owner.bookings.update', $ticket->id) }}">
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
                                                        $allowedTransitions = [
                                                        'pending'=>['pending','confirmed','cancelled'],
                                                        'confirmed'=>['confirmed','completed','cancelled'],
                                                        'completed'=>['completed'],
                                                        'cancelled'=>['cancelled'],
                                                        ];
                                                        @endphp
                                                        <select name="status" class="form-select" {{ in_array($ticket->status,['completed','cancelled'])?'disabled':'' }}>
                                                            @foreach(['pending'=>'Chờ xác nhận','confirmed'=>'Đã xác nhận','completed'=>'Hoàn thành','cancelled'=>'Hủy'] as $v=>$label)
                                                            @if(in_array($v,$allowedTransitions[$ticket->status]))
                                                            <option value="{{ $v }}" {{ $ticket->status==$v?'selected':'' }}>{{ $label }}</option>
                                                            @endif
                                                            @endforeach
                                                        </select>
                                                        <h6 class="text-muted mb-2 mt-3"><i class="fas fa-wallet me-2"></i>Thanh toán</h6>
                                                        <select name="payment_status" class="form-select" {{ in_array($ticket->status,['completed','cancelled'])?'disabled':'' }}>
                                                            <option value="unpaid" {{ $ticket->payment_status=='unpaid'?'selected':'' }}>Chưa thanh toán</option>
                                                            <option value="paid" {{ $ticket->payment_status=='paid'?'selected':'' }}>Đã thanh toán</option>
                                                            <option value="refunded" {{ $ticket->payment_status=='refunded'?'selected':'' }}>Hoàn tiền</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="info-box p-3 h-100">
                                                        <h6 class="text-muted mb-2"><i class="fas fa-calendar me-2"></i>Ngày tạo</h6>
                                                        <p class="mb-3">{{ $ticket->created_at->format('d/m/Y H:i') }}</p>

                                                        <h6 class="text-muted mb-1"><i class="fas fa-money-bill me-2"></i>Giá gốc</h6>
                                                        <p class="mb-2 text-secondary fw-semibold">
                                                            {{ number_format($ticket->subtotal ?? 0, 0, '.', ',') }}₫
                                                        </p>

                                                        <h6 class="text-muted mb-1"><i class="fas fa-tags me-2"></i>Giảm giá</h6>
                                                        <p class="mb-2 text-danger fw-semibold">
                                                            -{{ number_format($ticket->discount_amount ?? 0, 0, '.', ',') }}₫
                                                        </p>

                                                        <h6 class="text-muted mb-1"><i class="fas fa-money-bill-wave me-2"></i>Tổng tiền</h6>
                                                        <p class="fs-5 fw-bold text-success mb-0">
                                                            {{ number_format($ticket->total_amount ?? 0, 0, '.', ',') }}₫
                                                        </p>
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
                                                            <small class="text-muted">{{ $item->booking->date ?? '-' }} | {{ $item->booking->timeSlot->label ?? '-' }}</small>
                                                        </div>
                                                        <div class="text-end">
                                                            <p class="fw-bold text-dark mb-0">{{ number_format($item->unit_price,0,'.',',') }}₫</p>
                                                            @if($item->discount_amount>0)
                                                            <small class="text-danger">(-{{ number_format($item->discount_amount,0,'.',',') }}₫)</small>
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
                                            <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal"><i class="fas fa-times me-1"></i> Đóng</button>
                                            <button type="submit" class="btn btn-primary px-4"><i class="fas fa-save me-1"></i> Lưu thay đổi</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        @empty
                        <tr>
                            <td colspan="11" class="text-center p-4">
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
