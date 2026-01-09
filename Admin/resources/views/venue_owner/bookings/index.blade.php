@extends('app')

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">

<style>
    /* --- GIAO DIỆN CHUYÊN NGHIỆP (PRO UI) --- */
    body {
        background-color: #f8f9fa;
    }

    /* Font size nhỏ gọn */
    .fs-7 {
        font-size: 0.85rem !important;
    }

    .fs-8 {
        font-size: 0.75rem !important;
    }

    /* Badge màu Pastel (Dịu mắt) */
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
    .table-pro thead th {
        background-color: #f1f3f5;
        color: #6c757d;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.7rem;
        letter-spacing: 0.5px;
        border-bottom: 2px solid #dee2e6;
        vertical-align: middle;
        padding: 12px 10px;
    }

    .table-pro tbody td {
        vertical-align: middle;
        font-size: 0.85rem;
        padding: 10px;
        color: #343a40;
        border-bottom: 1px solid #f0f0f0;
    }

    .table-pro tbody tr:hover {
        background-color: #f8f9fa;
    }

    /* Avatar Avatar */
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
        font-size: 0.8rem;
        color: #6c757d;
        border: 1px solid #e9ecef;
        margin-right: 5px;
        border-radius: 6px;
        padding: 5px 12px;
        background: #fff;
    }

    .nav-pills-custom .nav-link.active {
        background-color: #0d6efd;
        color: #fff;
        border-color: #0d6efd;
        font-weight: 600;
    }

    /* Animation */
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

    /* Sửa lỗi hiển thị Modal */
    .modal-content {
        border: none !important;
        border-radius: 12px !important;
        overflow: hidden;
    }

    .modal-header {
        background-color: #f8f9fa;
        padding: 1rem 1.5rem;
    }

    .modal-body {
        background-color: #fff;
    }

    /* Đảm bảo bảng trong modal không bị dính style của table-pro bên ngoài */
    .modal-body .table {
        margin-bottom: 0;
    }

    .modal-body .table thead th {
        background-color: #f1f3f5;
        text-transform: none;
        font-size: 0.8rem;
        letter-spacing: normal;
    }

    .modal-backdrop.show {
        opacity: 0.4;
    }

    /* Fix lỗi font trong modal */
    .modal .fs-7 {
        font-size: 0.85rem !important;
    }

    .modal .fs-8 {
        font-size: 0.75rem !important;
    }
</style>

<div class="container-fluid py-4">
    {{-- 1. HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-dark mb-0"><i class="fas fa-tasks me-2 text-primary"></i>Quản lý Đặt sân</h3>
            <small class="text-muted">Theo dõi trạng thái và xử lý đơn hàng</small>
        </div>
        <div class="d-flex gap-2">
            <div class="input-group input-group-sm shadow-sm" style="width: 250px;">
                <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Tìm tên khách, SĐT..." value="{{ request('search') }}">
            </div>
            <a href="{{ route('owner.bookings.create') }}" class="btn btn-primary btn-sm fw-bold shadow-sm px-3">
                <i class="fas fa-plus me-1"></i> Tạo đơn mới
            </a>
        </div>
    </div>

    {{-- 2. TABS TRẠNG THÁI --}}
    <div class="mb-3">
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
                <button type="button"
                    class="nav-link status-tab {{ request('status') == $key ? 'active' : '' }}"
                    data-status="{{ $key }}">
                    {{ $label }}
                </button>
            </li>
            @endforeach
        </ul>
    </div>

    {{-- 3. BẢNG DỮ LIỆU --}}
    <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-pro mb-0">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 60px;">ID</th>
                        <th>Khách hàng</th>
                        <th>Chi tiết đặt sân</th>
                        <th class="text-end">Tổng tiền</th>
                        <th class="text-center">Trạng thái</th>
                        <th class="text-center">Thanh toán</th>
                        <th class="text-center" style="width: 120px;">Hành động</th>
                    </tr>
                </thead>
                <tbody id="booking-table-body">
                    @fragment('table-rows')
                    @forelse($tickets as $ticket)
                    <tr id="ticket-row-{{ $ticket->id }}">
                        {{-- ID --}}
                        <td class="text-center fw-bold text-secondary">{{ $ticket->id }}</td>

                        {{-- KHÁCH HÀNG --}}
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar-sm me-2">
                                    <i class="fas fa-user"></i>
                                </div>
                                <div>
                                    <div class="fw-bold text-dark fs-7">{{ $ticket->user->name ?? 'Khách vãng lai' }}</div>
                                    <div class="text-muted fs-8">{{ $ticket->user->phone ?? '---' }}</div>
                                </div>
                            </div>
                        </td>

                        {{-- CHI TIẾT --}}
                        <td>
                            @php
                            $bookingItem = $ticket->items->whereNotNull('booking_id')->first();
                            $itemCount = $ticket->items->count();
                            @endphp
                            @if ($bookingItem && $bookingItem->booking)
                            <div class="d-flex flex-column">
                                <span class="fw-bold text-primary fs-7">
                                    {{ $bookingItem->booking->court->name ?? 'Sân ?' }}
                                </span>
                                <span class="text-muted fs-8">
                                    <i class="far fa-calendar-alt me-1"></i>{{ \Carbon\Carbon::parse($bookingItem->booking->date)->format('d/m') }}
                                    <i class="far fa-clock ms-2 me-1"></i>{{ substr($bookingItem->booking->timeSlot->start_time ?? '', 0, 5) }}
                                </span>
                            </div>
                            @else
                            <span class="fw-bold text-success fs-7">Đơn dịch vụ lẻ</span>
                            @endif

                            @if ($itemCount > 1)
                            <span class="badge bg-light text-secondary border mt-1 fs-8">+{{ $itemCount - 1 }} món khác</span>
                            @endif
                        </td>

                        {{-- TIỀN --}}
                        <td class="text-end fw-bold text-dark fs-7">
                            {{ number_format($ticket->total_amount, 0) }}₫
                        </td>

                        {{-- TRẠNG THÁI --}}
                        <td class="text-center">
                            @php
                            $sColor = [
                            'pending' => 'warning', 'confirmed' => 'primary',
                            'checkin' => 'info', 'completed' => 'success', 'cancelled' => 'danger'
                            ][$ticket->status] ?? 'secondary';

                            $sText = [
                            'pending' => 'Chờ xác nhận', 'confirmed' => 'Đã xác nhận',
                            'checkin' => 'Đang đá', 'completed' => 'Hoàn thành', 'cancelled' => 'Đã hủy'
                            ][$ticket->status] ?? $ticket->status;
                            @endphp
                            <span class="badge badge-soft-{{ $sColor }} rounded-pill px-3 py-2 fw-normal">
                                {{ $sText }}
                            </span>
                        </td>

                        {{-- THANH TOÁN --}}
                        <td class="text-center">
                            @php
                            $pColor = ['paid' => 'success', 'unpaid' => 'danger', 'refunded' => 'info'][$ticket->payment_status] ?? 'secondary';
                            $pText = ['paid' => 'Đã thanh toán', 'unpaid' => 'Chưa thanh toán', 'refunded' => 'Hoàn tiền'][$ticket->payment_status] ?? $ticket->payment_status;
                            @endphp
                            <span class="badge badge-soft-{{ $pColor }} rounded-pill px-3 py-2 fw-normal">
                                {{ $pText }}
                            </span>
                        </td>

                        {{-- NÚT HÀNH ĐỘNG --}}
                        <td class="text-center">
                            <div class="d-flex justify-content-center gap-1">
                                {{-- Nút Check-in (Form POST chuẩn) --}}
                                @if (!in_array($ticket->status, ['checkin', 'completed', 'cancelled']))
                                <form action="{{ route('owner.bookings.checkin', $ticket->id) }}" method="POST" onsubmit="return confirm('Xác nhận khách vào sân?');">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success shadow-sm" data-bs-toggle="tooltip" title="Check-in vào sân">
                                        <i class="fas fa-play fs-8"></i>
                                    </button>
                                </form>
                                @endif

                                {{-- Nút Xem Modal --}}
                                <button class="btn btn-sm btn-light border shadow-sm text-primary" data-bs-toggle="modal" data-bs-target="#ticketModal{{ $ticket->id }}">
                                    <i class="fas fa-eye fs-8"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="fas fa-inbox fa-3x mb-3 opacity-25"></i><br>
                            Không có dữ liệu đơn hàng nào.
                        </td>
                    </tr>
                    @endforelse
                    @endfragment
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- 4. MODALS (Tách biệt logic xử lý) --}}
@foreach ($tickets as $ticket)
<div class="modal fade" id="ticketModal{{ $ticket->id }}" tabindex="-1" aria-hidden="true" style="display: none;">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-3">

            {{-- Form cập nhật --}}
            <form method="POST" action="{{ route('owner.bookings.update', $ticket->id) }}">
                @csrf
                @method('PUT')

                <div class="modal-header py-2 bg-light border-bottom">
                    <h6 class="modal-title fw-bold text-uppercase text-secondary fs-7">
                        <i class="fas fa-hashtag me-1"></i>Đơn hàng #{{ $ticket->id }}
                        <span class="fw-normal text-muted ms-2">| {{ $ticket->booking_code }}</span>
                    </h6>
                    <button type="button" class="btn-close btn-sm" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body p-0">
                    <div class="row g-0">
                        {{-- CỘT TRÁI: THÔNG TIN --}}
                        <div class="col-md-8 p-3">
                            {{-- Info Khách --}}
                            <div class="d-flex align-items-center mb-3 p-2 bg-light rounded border-0">
                                <div class="avatar-sm bg-white border text-primary me-3 fs-6"><i class="fas fa-user"></i></div>
                                <div>
                                    <div class="fw-bold fs-7">{{ $ticket->user->name ?? 'Khách vãng lai' }}</div>
                                    <div class="text-muted fs-8">{{ $ticket->user->phone ?? '---' }}</div>
                                </div>
                            </div>

                            {{-- Bảng chi tiết --}}
                            <div class="table-responsive border rounded-2">
                                <table class="table table-sm table-borderless mb-0 fs-7">
                                    <thead class="bg-light text-secondary border-bottom">
                                        <tr>
                                            <th class="ps-3">Dịch vụ</th>
                                            <th>Chi tiết</th>
                                            <th class="text-center">SL</th>
                                            <th class="text-end pe-3">Thành tiền</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($ticket->items as $item)
                                        <tr class="border-bottom border-light">
                                            <td class="ps-3 fw-semibold">
                                                @if($item->booking_id) <span class="text-primary">Thuê sân</span>
                                                @else <span class="text-success">Dịch vụ</span> @endif
                                            </td>
                                            <td>
                                                @if($item->booking)
                                                <div>{{ $item->booking->court->name ?? 'Sân ?' }}</div>
                                                <small class="text-muted fs-8">
                                                    {{ \Carbon\Carbon::parse($item->booking->date)->format('d/m') }} |
                                                    {{ substr($item->booking->timeSlot->start_time ?? '',0,5) }}-{{ substr($item->booking->timeSlot->end_time ?? '',0,5) }}
                                                </small>
                                                @else
                                                {{ $item->venueService->service->name ?? 'DV' }}
                                                @endif
                                            </td>
                                            <td class="text-center">x{{ $item->quantity }}</td>
                                            <td class="text-end pe-3">{{ number_format($item->unit_price * $item->quantity) }}₫</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot class="bg-light">
                                        <tr>
                                            <td colspan="3" class="text-end fw-bold">TỔNG CỘNG:</td>
                                            <td class="text-end pe-3 fw-bold text-danger">{{ number_format($ticket->total_amount) }}₫</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>

                        {{-- CỘT PHẢI: XỬ LÝ TRẠNG THÁI --}}
                        <div class="col-md-4 bg-light border-start p-3">
                            <h6 class="fw-bold fs-8 text-uppercase text-secondary mb-3">Cập nhật đơn</h6>

                            {{-- 1. TRẠNG THÁI --}}
                            <div class="mb-3">
                                <label class="form-label fs-8 fw-semibold text-muted">Trạng thái xử lý</label>

                                @if(in_array($ticket->status, ['completed', 'cancelled']))
                                <div class="alert {{ $ticket->status == 'completed' ? 'alert-success' : 'alert-danger' }} p-2 fs-7 mb-0 text-center fw-bold">
                                    {{ $ticket->status == 'completed' ? 'ĐÃ HOÀN THÀNH' : 'ĐÃ HỦY BỎ' }}
                                </div>
                                <input type="hidden" name="status" value="{{ $ticket->status }}">
                                @else
                                <select name="status" class="form-select form-select-sm fw-bold shadow-sm border-0">
                                    @if($ticket->status == 'checkin')
                                    {{-- Logic: Đang đá -> Quay lại (Confirmed) hoặc Kết thúc (Completed) --}}
                                    <option value="confirmed">⏪ Đã xác nhận (Lùi lại)</option>
                                    <option value="checkin" selected>▶ Đang đá (Hiện tại)</option>
                                    <option value="completed">✅ Hoàn thành (Kết thúc)</option>

                                    @elseif($ticket->status == 'confirmed')
                                    {{-- Logic: Đã xác nhận -> Checkin hoặc Hủy --}}
                                    <option value="pending">Chờ xác nhận</option>
                                    <option value="confirmed" selected>Đã xác nhận</option>
                                    <option value="checkin">▶ Vào sân (Check-in)</option>
                                    <option value="cancelled" class="text-danger">✖ Hủy đơn</option>

                                    @else
                                    <option value="pending" {{ $ticket->status == 'pending' ? 'selected' : '' }}>Chờ xác nhận</option>
                                    <option value="confirmed" {{ $ticket->status == 'confirmed' ? 'selected' : '' }}>Đã xác nhận</option>
                                    <option value="cancelled" {{ $ticket->status == 'cancelled' ? 'selected' : '' }}>Hủy đơn</option>
                                    @endif
                                </select>
                                @endif
                            </div>

                            {{-- 2. THANH TOÁN --}}
                            <div class="mb-4">
                                <label class="form-label fs-8 fw-semibold text-muted">Thanh toán</label>

                                @if($ticket->status === 'cancelled')
                                <input type="text"
                                    class="form-control form-control-sm bg-light"
                                    value="{{ $ticket->payment_status === 'paid' ? 'Đã thanh toán' : 'Chưa thanh toán' }}"
                                    disabled>

                                <input type="hidden" name="payment_status" value="{{ $ticket->payment_status }}">

                                @elseif($ticket->payment_status === 'paid')
                                <input type="text"
                                    class="form-control form-control-sm bg-light"
                                    value="Đã thanh toán"
                                    disabled>

                                <input type="hidden" name="payment_status" value="paid">

                                @else
                                <select name="payment_status"
                                    class="form-select form-select-sm shadow-sm border-0">
                                    <option value="unpaid" selected>Chưa thanh toán</option>
                                    <option value="paid">Đã thanh toán</option>
                                </select>
                                @endif
                            </div>

                            {{-- NÚT LƯU --}}
                            @if(!in_array($ticket->status, ['completed', 'cancelled']))
                            <button type="submit" class="btn btn-primary btn-sm w-100 fw-bold shadow-sm">
                                <i class="fas fa-save me-1"></i> Lưu thay đổi
                            </button>
                            @endif
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

{{-- SCRIPTS (Đã đồng bộ giao diện mới) --}}
<script>
    // Hàm render lại dòng khi có sự kiện Realtime
    function getTicketRowHtml(ticket) {
        const formatMoney = (amount) => new Intl.NumberFormat('vi-VN').format(amount) + '₫';

        // 1. Xử lý Chi tiết
        const bookingItem = ticket.items.find(i => i.booking_id != null);
        let detailHtml = '';
        if (bookingItem && bookingItem.booking) {
            const startTime = bookingItem.booking.time_slot?.start_time?.substring(0, 5) || '--:--';
            detailHtml = `
                    <div class="d-flex flex-column">
                        <span class="fw-bold text-primary fs-7">${bookingItem.booking.court?.name || 'Sân ?'}</span>
                        <span class="text-muted fs-8">
                            <i class="far fa-calendar-alt me-1"></i>${bookingItem.booking.date}
                            <i class="far fa-clock ms-2 me-1"></i>${startTime}
                        </span>
                    </div>`;
        } else {
            detailHtml = `<span class="fw-bold text-success fs-7">Đơn dịch vụ lẻ</span>`;
        }
        if (ticket.items.length > 1) {
            detailHtml += `<span class="badge bg-light text-secondary border mt-1 fs-8">+${ticket.items.length - 1} món khác</span>`;
        }

        // 2. Xử lý Status Badge
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
        const statusHtml = `<span class="badge badge-soft-${sMap[ticket.status] || 'secondary'} rounded-pill px-3 py-2 fw-normal">${sLabel[ticket.status] || ticket.status}</span>`;

        // 3. Xử lý Payment Badge
        const pMap = {
            'paid': 'success',
            'unpaid': 'danger',
            'refunded': 'info'
        };
        const pLabel = {
            'paid': 'Đã thanh toán',
            'unpaid': 'Chưa thanh toán',
            'refunded': 'Hoàn tiền'
        };
        const paymentHtml = `<span class="badge badge-soft-${pMap[ticket.payment_status] || 'secondary'} rounded-pill px-3 py-2 fw-normal">${pLabel[ticket.payment_status] || ticket.payment_status}</span>`;

        // 4. Xử lý Nút Check-in
        let checkinBtn = '';
        if (!['checkin', 'completed', 'cancelled'].includes(ticket.status)) {
            // Placeholder ID để JS không lỗi, URL này chỉ minh họa, thực tế Laravel route cần ID thật
            const checkinUrl = `/owner/bookings/${ticket.id}/check-in`;
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            checkinBtn = `
                <form action="${checkinUrl}" method="POST" onsubmit="return confirm('Xác nhận khách vào sân?');">
                    <input type="hidden" name="_token" value="${csrfToken}">
                    <button type="submit" class="btn btn-sm btn-success shadow-sm" data-bs-toggle="tooltip" title="Check-in vào sân">
                        <i class="fas fa-play fs-8"></i>
                    </button>
                </form>`;
        }

        return `
                <td class="text-center fw-bold text-secondary">${ticket.id}</td>
                <td>
                    <div class="d-flex align-items-center">
                        <div class="avatar-sm me-2"><i class="fas fa-user"></i></div>
                        <div>
                            <div class="fw-bold text-dark fs-7">${ticket.user?.name || 'Khách vãng lai'}</div>
                            <div class="text-muted fs-8">${ticket.user?.phone || '---'}</div>
                        </div>
                    </div>
                </td>
                <td>${detailHtml}</td>
                <td class="text-end fw-bold text-dark fs-7">${formatMoney(ticket.total_amount)}</td>
                <td class="text-center">${statusHtml}</td>
                <td class="text-center">${paymentHtml}</td>
                <td class="text-center">
                    <div class="d-flex justify-content-center gap-1">
                        ${checkinBtn}
                        <button class="btn btn-sm btn-light border shadow-sm text-primary" onclick="window.location.reload()">
                            <i class="fas fa-eye fs-8"></i>
                        </button>
                    </div>
                </td>
            `;
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Realtime Listener
        if (typeof Echo !== 'undefined') {
            const channel = Echo.channel('booking');
            channel.listen('.ticket.created', (e) => {
                const tbody = document.getElementById('booking-table-body');
                if (tbody.querySelector('td[colspan]')) tbody.innerHTML = ''; // Xóa dòng trống

                const row = document.createElement('tr');
                row.id = `ticket-row-${e.data.id}`;
                row.className = 'animate-new-row';
                row.innerHTML = getTicketRowHtml(e.data);
                tbody.prepend(row);
                setTimeout(() => row.classList.remove('animate-new-row'), 2000);
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

        // Tabs Filter
        const tabs = document.querySelectorAll('.status-tab');
        const tbody = document.getElementById('booking-table-body');

        tabs.forEach(tab => {
            tab.addEventListener('click', function() {
                // Cập nhật giao diện active
                tabs.forEach(t => t.classList.remove('active'));
                this.classList.add('active');

                const status = this.dataset.status;
                const url = new URL(window.location.href);
                if (status) url.searchParams.set('status', status);
                else url.searchParams.delete('status');

                // Đổi URL trên trình duyệt mà không reload trang (tùy chọn)
                window.history.pushState({}, '', url);

                fetch(url.toString(), {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(res => res.text())
                    .then(html => {
                        // Quan trọng: HTML trả về lúc này chỉ chứa các thẻ <tr>...</tr>
                        // nhờ vào logic trả về Fragment ở phía Controller
                        document.getElementById('booking-table-body').innerHTML = html;
                    })
                    .catch(err => console.error('Lỗi:', err));
            });
        });

        // Tooltip Bootstrap (Optional)
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        })
    });
</script>
@endsection