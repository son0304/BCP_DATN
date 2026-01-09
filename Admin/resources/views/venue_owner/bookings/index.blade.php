@extends('app')

@section('content')
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>
        /* --- GLOBAL STYLES --- */
        body {
            background-color: #f4f6f9;
            font-family: 'Segoe UI', sans-serif;
        }

        .fs-7 {
            font-size: 0.9rem !important;
        }

        .fs-8 {
            font-size: 0.8rem !important;
        }

        /* Badge Colors */
        .badge-soft-success {
            background-color: #d1e7dd;
            color: #0f5132;
        }

        .badge-soft-warning {
            background-color: #fff3cd;
            color: #664d03;
        }

        .badge-soft-info {
            background-color: #cff4fc;
            color: #055160;
        }

        .badge-soft-danger {
            background-color: #f8d7da;
            color: #842029;
        }

        .badge-soft-primary {
            background-color: #cfe2ff;
            color: #052c65;
        }

        .badge-soft-secondary {
            background-color: #e2e3e5;
            color: #41464b;
        }

        /* Table Styles */
        .table-pro {
            background: #fff;
        }

        .table-pro thead th {
            background-color: #f8f9fa;
            color: #6c757d;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #e9ecef;
            vertical-align: middle;
            padding: 14px 10px;
        }

        .table-pro tbody td {
            vertical-align: middle;
            font-size: 0.9rem;
            padding: 12px 10px;
            color: #333;
            border-bottom: 1px solid #f1f1f1;
        }

        .table-pro tbody tr:hover {
            background-color: #fafafa;
        }

        .avatar-sm {
            width: 32px;
            height: 32px;
            background-color: #e9ecef;
            color: #495057;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
        }

        /* Nav Tabs */
        .nav-pills-custom .nav-link {
            font-size: 0.85rem;
            color: #6c757d;
            border: 1px solid #e9ecef;
            margin-right: 8px;
            border-radius: 20px;
            padding: 6px 16px;
            background: #fff;
            font-weight: 500;
        }

        .nav-pills-custom .nav-link.active {
            background-color: #0d6efd;
            color: #fff;
            border-color: #0d6efd;
            box-shadow: 0 2px 4px rgba(13, 110, 253, 0.2);
        }

        .btn-action {
            width: 34px;
            height: 34px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
            border: 1px solid transparent;
            transition: all 0.2s;
        }

        .btn-action:hover {
            transform: translateY(-2px);
        }

        /* Modal Style */
        .modal-clean .modal-content {
            border: none;
            border-radius: 12px;
            overflow: hidden;
        }

        .modal-clean .modal-header {
            background-color: #fff;
            border-bottom: 1px solid #e9ecef;
            padding: 18px 25px;
        }

        .info-group {
            margin-bottom: 12px;
        }

        .info-label {
            font-size: 0.7rem;
            color: #adb5bd;
            text-transform: uppercase;
            font-weight: 700;
            margin-bottom: 2px;
        }

        .info-value {
            font-size: 0.95rem;
            color: #212529;
            font-weight: 600;
        }

        .control-panel {
            background-color: #f8f9fa;
            border-left: 1px solid #e9ecef;
        }

        .animate-new-row {
            animation: highlightRow 2s ease-out;
        }

        @keyframes highlightRow {
            0% {
                background-color: #d1e7dd;
            }

            100% {
                background-color: transparent;
            }
        }
    </style>

    <div class="container-fluid py-4">
        {{-- HEADER --}}
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-4 gap-3">
            <div>
                <h4 class="fw-bold text-dark mb-1">Quản lý Đặt sân</h4>
                <small class="text-muted">Theo dõi đơn hàng từ hệ thống và khách vãng lai</small>
            </div>

            <div class="d-flex gap-2">
                <form action="{{ url()->current() }}" method="GET" class="d-flex shadow-sm rounded overflow-hidden">
                    <div class="input-group">
                        <button type="submit" class="btn btn-white bg-white border-end-0 border ps-3 text-muted">
                            <i class="fas fa-search"></i>
                        </button>
                        <input type="text" name="search" class="form-control border-start-0 border-end-0 ps-0"
                            placeholder="Tìm khách, mã..." value="{{ request('search') }}" style="width: 220px;">
                    </div>
                </form>
                <a href="{{ route('owner.bookings.create') }}" class="btn btn-primary fw-bold shadow-sm px-3">
                    <i class="fas fa-plus me-1"></i> Tạo đơn
                </a>
            </div>
        </div>

        {{-- TABS TRẠNG THÁI --}}
        <div class="mb-4">
            <ul class="nav nav-pills nav-pills-custom">
                @php
                    $tabs = [
                        '' => 'Tất cả',
                        'pending' => 'Chờ xác nhận',
                        'confirmed' => 'Đã xác nhận',
                        'checkin' => 'Đang đá',
                        'completed' => 'Hoàn thành',
                        'cancelled' => 'Đã hủy',
                    ];
                @endphp
                @foreach ($tabs as $key => $label)
                    <li class="nav-item">
                        <a href="{{ request()->fullUrlWithQuery(['status' => $key, 'page' => 1]) }}"
                            class="nav-link {{ request('status') == $key ? 'active' : '' }}">
                            {{ $label }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>

        {{-- BẢNG DỮ LIỆU --}}
        <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
            <div class="table-responsive">
                <table class="table table-pro mb-0">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 100px;">Mã #</th>
                            <th>Khách hàng</th>
                            <th>Chi tiết đặt sân</th>
                            <th class="text-end">Tổng tiền</th>
                            <th class="text-center">Trạng thái</th>
                            <th class="text-center" style="width: 120px;">Hành động</th>
                        </tr>
                    </thead>
                    <tbody id="booking-table-body">
                        @forelse($tickets as $ticket)
                            <tr id="ticket-row-{{ $ticket->id }}">
                                <td class="text-center">
                                    <span class="fw-bold text-secondary">{{ $ticket->id }}</span><br>
                                    <small class="text-muted fs-8">{{ $ticket->booking_code }}</small>
                                </td>

                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm me-2">
                                            <i
                                                class="fas {{ $ticket->guest ? 'fa-user-tag text-primary' : 'fa-user' }}"></i>
                                        </div>
                                        <div>
                                            @if ($ticket->guest)
                                                @php $gInfo = explode(' - ', $ticket->guest); @endphp
                                                <div class="fw-bold text-dark fs-7">{{ $gInfo[0] ?? 'Khách vãng lai' }}
                                                </div>
                                                <div class="text-muted fs-8">
                                                    {{ $gInfo[1] ?? '---' }}
                                                    <span class="badge badge-soft-secondary ms-1"
                                                        style="font-size: 9px">Khách vãng lai</span>
                                                </div>
                                            @else
                                                <div class="fw-bold text-dark fs-7">{{ $ticket->user->name ?? 'N/A' }}
                                                </div>
                                                <div class="text-muted fs-8">{{ $ticket->user->phone ?? '---' }}</div>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    @php
                                        $bookingItem = $ticket->items->whereNotNull('booking_id')->first();
                                        $itemCount = $ticket->items->count();
                                    @endphp
                                    @if ($bookingItem && $bookingItem->booking)
                                        <div class="d-flex flex-column">
                                            <span
                                                class="fw-bold text-primary fs-7">{{ $bookingItem->booking->court->name ?? 'Sân ?' }}</span>
                                            <span class="text-muted fs-8">
                                                {{ \Carbon\Carbon::parse($bookingItem->booking->date)->format('d/m') }}
                                                <span class="mx-1">|</span>
                                                {{ substr($bookingItem->booking->timeSlot->start_time ?? '', 0, 5) }}
                                            </span>
                                        </div>
                                    @else
                                        <span class="fw-bold text-success fs-7">Đơn dịch vụ lẻ</span>
                                    @endif
                                    @if ($itemCount > 1)
                                        <span class="badge bg-light text-secondary border mt-1 fs-8"
                                            style="font-weight: 400">+{{ $itemCount - 1 }} món khác</span>
                                    @endif
                                </td>

                                <td class="text-end fw-bold text-dark fs-7">
                                    {{ number_format($ticket->total_amount, 0) }}₫
                                </td>

                                <td class="text-center">
                                    @php
                                        $sMap = [
                                            'pending' => ['warning', 'Chờ xác nhận'],
                                            'confirmed' => ['primary', 'Đã xác nhận'],
                                            'checkin' => ['info', 'Đang đá'],
                                            'completed' => ['success', 'Hoàn thành'],
                                            'cancelled' => ['danger', 'Đã hủy'],
                                        ];
                                        $statusInfo = $sMap[$ticket->status] ?? ['secondary', $ticket->status];
                                    @endphp
                                    <span class="badge badge-soft-{{ $statusInfo[0] }} rounded-pill px-3 py-2 fw-normal">
                                        {{ $statusInfo[1] }}
                                    </span>
                                </td>

                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-2">
                                        @if (!in_array($ticket->status, ['checkin', 'completed', 'cancelled']))
                                            <form action="{{ route('owner.bookings.checkin', $ticket->id) }}"
                                                method="POST">
                                                @csrf
                                                <button type="submit" class="btn btn-success btn-action shadow-sm"
                                                    data-bs-toggle="tooltip" title="Check-in ngay"
                                                    onclick="return confirm('Xác nhận khách vào sân?')">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            </form>
                                        @endif

                                        <button type="button" class="btn btn-primary btn-action shadow-sm"
                                            data-bs-toggle="modal" data-bs-target="#ticketModal{{ $ticket->id }}"
                                            title="Xem chi tiết">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted"><i
                                        class="fas fa-box-open fa-3x mb-3 opacity-25"></i><br> Không có dữ liệu đơn hàng.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-center py-3">{{ $tickets->links('pagination::bootstrap-5') }}</div>
        </div>
    </div>

    {{-- MODALS CHI TIẾT --}}
    @foreach ($tickets as $ticket)
        <div class="modal fade modal-clean" id="ticketModal{{ $ticket->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content shadow-lg border-0">
                    <form method="POST" action="{{ route('owner.bookings.update', $ticket->id) }}">
                        @csrf @method('PUT')
                        <div class="modal-header">
                            <div>
                                <h5 class="modal-title fw-bold text-dark">Chi tiết đơn hàng #{{ $ticket->id }}</h5>
                                <span class="text-muted fs-8">Mã booking: <strong>{{ $ticket->booking_code }}</strong> |
                                    Ngày tạo: {{ $ticket->created_at->format('d/m/Y H:i') }}</span>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>

                        <div class="modal-body p-0">
                            <div class="row g-0">
                                <div class="col-md-8 p-4 bg-white">
                                    <div class="row mb-4">
                                        <div class="col-6 border-end">
                                            <div class="info-group">
                                                <div class="info-label">Khách hàng</div>
                                                <div class="info-value">
                                                    @if ($ticket->guest)
                                                        {{ explode(' - ', $ticket->guest)[0] ?? 'Khách vãng lai' }}
                                                        <span class="badge badge-soft-secondary ms-1"
                                                            style="font-size: 9px">Khách vãng lai</span>
                                                    @else
                                                        {{ $ticket->user->name ?? 'N/A' }}
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-6 ps-4">
                                            <div class="info-group">
                                                <div class="info-label">Số điện thoại</div>
                                                <div class="info-value">
                                                    @if ($ticket->guest)
                                                        {{ explode(' - ', $ticket->guest)[1] ?? '---' }}
                                                    @else
                                                        {{ $ticket->user->phone ?? '---' }}
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <h6 class="fw-bold text-dark fs-7 mb-3">DỊCH VỤ ĐÃ ĐẶT</h6>
                                    <div class="table-responsive">
                                        <table class="table table-sm table-borderless align-middle">
                                            <thead class="bg-light fs-8 text-muted">
                                                <tr>
                                                    <th class="ps-2 py-2">Tên dịch vụ</th>
                                                    <th class="text-center">SL</th>
                                                    <th class="text-end">Đơn giá</th>
                                                    <th class="text-end">Giảm giá</th>
                                                    <th class="text-end pe-2">Thành tiền</th>
                                                </tr>
                                            </thead>
                                            <tbody class="fs-7">
                                                @php
                                                    $subTotal = 0;
                                                    $totalDiscount = 0;
                                                @endphp
                                                @foreach ($ticket->items as $item)
                                                    @php
                                                        $itemPrice = $item->unit_price * $item->quantity;
                                                        $subTotal += $itemPrice;
                                                        $totalDiscount += $item->discount_amount;
                                                    @endphp
                                                    <tr class="border-bottom">
                                                        <td class="ps-2 py-3">
                                                            @if ($item->booking)
                                                                <div class="fw-bold text-primary">
                                                                    {{ $item->booking->court->name ?? 'Sân ?' }}</div>
                                                                <div class="text-muted fs-8">
                                                                    {{ \Carbon\Carbon::parse($item->booking->date)->format('d/m/Y') }}
                                                                    ({{ substr($item->booking->timeSlot->start_time ?? '', 0, 5) }}
                                                                    -
                                                                    {{ substr($item->booking->timeSlot->end_time ?? '', 0, 5) }})
                                                                </div>
                                                            @else
                                                                <div class="fw-bold text-dark">
                                                                    {{ $item->venueService->service->name ?? 'Dịch vụ lẻ' }}
                                                                </div>
                                                            @endif
                                                        </td>
                                                        <td class="text-center">x{{ $item->quantity }}</td>
                                                        <td class="text-end">{{ number_format($item->unit_price) }}₫</td>
                                                        <td class="text-end text-danger">
                                                            -{{ number_format($item->discount_amount) }}₫</td>
                                                        <td class="text-end pe-2 fw-bold text-dark">
                                                            {{ number_format($itemPrice - $item->discount_amount) }}₫</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="mt-3 p-3 rounded-3"
                                        style="background-color: #fcfcfc; border: 1px dashed #eee;">
                                        <div class="d-flex justify-content-between mb-1 fs-7"><span class="text-muted">Tạm
                                                tính:</span><span>{{ number_format($subTotal) }}₫</span></div>
                                        <div class="d-flex justify-content-between mb-1 fs-7"><span
                                                class="text-muted">Giảm giá đơn:</span><span
                                                class="text-danger">-{{ number_format($ticket->discount_amount) }}₫</span>
                                        </div>
                                        <hr>
                                        <div class="d-flex justify-content-between align-items-center"><span
                                                class="fw-bold text-dark">TỔNG CỘNG:</span><span
                                                class="fw-bold text-primary fs-5">{{ number_format($ticket->total_amount) }}₫</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4 control-panel p-4">
                                    <h6 class="fw-bold text-dark fs-7 mb-3">XỬ LÝ ĐƠN HÀNG</h6>
                                    <div class="mb-4">
                                        <label class="form-label info-label">Trạng thái đơn</label>
                                        @if (in_array($ticket->status, ['completed', 'cancelled']))
                                            <div
                                                class="form-control bg-white fw-bold {{ $ticket->status == 'completed' ? 'text-success' : 'text-danger' }}">
                                                {{ $ticket->status == 'completed' ? 'HOÀN THÀNH' : 'ĐÃ HỦY' }}</div>
                                            <input type="hidden" name="status" value="{{ $ticket->status }}">
                                        @else
                                            <select name="status" class="form-select shadow-sm fw-bold">
                                                @if ($ticket->status === 'checkin')
                                                    <option value="checkin" selected>Đang đá (Check-in)</option>
                                                    <option value="completed" class="text-success">Hoàn thành đơn</option>
                                                @else
                                                    <option value="pending"
                                                        {{ $ticket->status == 'pending' ? 'selected' : '' }}>Chờ xác nhận
                                                    </option>
                                                    <option value="confirmed"
                                                        {{ $ticket->status == 'confirmed' ? 'selected' : '' }}>Đã xác nhận
                                                    </option>
                                                    <option value="checkin"
                                                        {{ $ticket->status == 'checkin' ? 'selected' : '' }}>Check-in khách
                                                    </option>
                                                    <option value="cancelled" class="text-danger">Hủy đơn</option>
                                                @endif
                                            </select>
                                        @endif
                                    </div>

                                    <div class="p-3 bg-white rounded-3 border mb-4 text-center">
                                        <span
                                            class="badge {{ $ticket->payment_status === 'paid' ? 'badge-soft-success' : 'badge-soft-danger' }} w-100 py-2 mb-2">
                                            {{ $ticket->payment_status === 'paid' ? 'ĐÃ THANH TOÁN' : 'CHƯA THANH TOÁN' }}
                                        </span>
                                        <small class="text-muted fs-8">Sân vận hành bởi: {{ Auth::user()->name }}</small>
                                    </div>

                                    @if (!in_array($ticket->status, ['completed', 'cancelled']))
                                        <button type="submit" class="btn btn-primary w-100 fw-bold shadow-sm py-2">Lưu
                                            thay đổi</button>
                                    @else
                                        <button type="button" class="btn btn-secondary w-100 fw-bold shadow-sm"
                                            data-bs-dismiss="modal">Đóng</button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endforeach

    <script>
        function getTicketRowHtml(ticket) {
            const formatMoney = (amount) => new Intl.NumberFormat('vi-VN').format(amount) + '₫';
            const bookingItem = ticket.items.find(i => i.booking_id != null);
            let detailHtml = '';

            if (bookingItem && bookingItem.booking) {
                const startTime = bookingItem.booking.time_slot?.start_time?.substring(0, 5) || '--:--';
                detailHtml = `
                    <div class="d-flex flex-column">
                        <span class="fw-bold text-primary fs-7">${bookingItem.booking.court?.name || 'Sân ?'}</span>
                        <span class="text-muted fs-8">${bookingItem.booking.date} <span class="mx-1">|</span> ${startTime}</span>
                    </div>`;
            } else {
                detailHtml = `<span class="fw-bold text-success fs-7">Đơn dịch vụ lẻ</span>`;
            }
            if (ticket.items.length > 1) {
                detailHtml +=
                    `<span class="badge bg-light text-secondary border mt-1 fs-8">+${ticket.items.length - 1} món khác</span>`;
            }

            // Logic xử lý tên khách hàng vãng lai trong JS
            let customerHtml = '';
            if (ticket.guest) {
                const parts = ticket.guest.split(' - ');
                customerHtml = `
                    <div class="fw-bold text-dark fs-7">${parts[0] || 'Khách vãng lai'}</div>
                    <div class="text-muted fs-8">${parts[1] || '---'} <span class="badge badge-soft-secondary ms-1" style="font-size:9px">GUEST</span></div>
                `;
            } else {
                customerHtml = `
                    <div class="fw-bold text-dark fs-7">${ticket.user?.name || 'N/A'}</div>
                    <div class="text-muted fs-8">${ticket.user?.phone || '---'}</div>
                `;
            }

            const sMap = {
                'pending': 'warning',
                'confirmed': 'primary',
                'checkin': 'info',
                'completed': 'success',
                'cancelled': 'danger'
            };
            const sLabel = {
                'pending': 'Chờ xác nhận',
                'confirmed': 'Đã xác nhận',
                'checkin': 'Đang đá',
                'completed': 'Hoàn thành',
                'cancelled': 'Đã hủy'
            };
            const statusHtml =
                `<span class="badge badge-soft-${sMap[ticket.status] || 'secondary'} rounded-pill px-3 py-2 fw-normal">${sLabel[ticket.status] || ticket.status}</span>`;

            let checkinBtn = '';
            if (!['checkin', 'completed', 'cancelled'].includes(ticket.status)) {
                checkinBtn = `
                <form action="/owner/bookings/${ticket.id}/check-in" method="POST">
                    <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]').content}">
                    <button type="submit" class="btn btn-success btn-action shadow-sm" title="Check-in ngay" onclick="return confirm('Xác nhận khách vào sân?')">
                        <i class="fas fa-check"></i>
                    </button>
                </form>`;
            }

            return `
                <td class="text-center">
                    <span class="fw-bold text-secondary">${ticket.id}</span><br>
                    <small class="text-muted fs-8">${ticket.booking_code || ''}</small>
                </td>
                <td>
                    <div class="d-flex align-items-center">
                        <div class="avatar-sm me-2"><i class="fas ${ticket.guest ? 'fa-user-tag text-primary' : 'fa-user'}"></i></div>
                        <div>${customerHtml}</div>
                    </div>
                </td>
                <td>${detailHtml}</td>
                <td class="text-end fw-bold text-dark fs-7">${formatMoney(ticket.total_amount)}</td>
                <td class="text-center">${statusHtml}</td>
                <td class="text-center">
                    <div class="d-flex justify-content-center gap-2">
                        ${checkinBtn}
                        <button type="button" class="btn btn-primary btn-action shadow-sm" onclick="window.location.reload()">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </td>`;
        }

        document.addEventListener('DOMContentLoaded', function() {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            tooltipTriggerList.map(function(el) {
                return new bootstrap.Tooltip(el)
            });

            if (typeof Echo !== 'undefined') {
                const channel = Echo.channel('booking');
                channel.listen('.ticket.created', (e) => {
                    const tbody = document.getElementById('booking-table-body');
                    const row = document.createElement('tr');
                    row.id = `ticket-row-${e.data.id}`;
                    row.className = 'animate-new-row';
                    row.innerHTML = getTicketRowHtml(e.data);
                    tbody.prepend(row);
                });
                channel.listen('.ticket.updated', (e) => {
                    const row = document.getElementById(`ticket-row-${e.data.id}`);
                    if (row) {
                        row.innerHTML = getTicketRowHtml(e.data);
                        row.style.backgroundColor = '#fff3cd';
                        setTimeout(() => row.style.backgroundColor = '', 1500);
                    }
                });
            }
        });
    </script>
@endsection
