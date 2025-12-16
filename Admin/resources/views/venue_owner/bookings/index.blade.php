@extends('app')

@section('content')

<style>
    .badge.bg-draft {
        background-color: #f59e0b !important;
        color: #fff !important;
    }

    .bg-warning-subtle {
        background-color: #fff3cd !important;
    }

    .text-warning-emphasis {
        color: #664d03 !important;
    }

    .bg-success-subtle {
        background-color: #d1e7dd !important;
    }

    .text-success-emphasis {
        color: #0f5132 !important;
    }

    .bg-danger-subtle {
        background-color: #f8d7da !important;
    }

    .text-danger-emphasis {
        color: #842029 !important;
    }

    .bg-primary-subtle {
        background-color: #cfe2ff !important;
    }

    .text-primary-emphasis {
        color: #052c65 !important;
    }

    .bg-secondary-subtle {
        background-color: #e2e3e5 !important;
    }

    .text-secondary-emphasis {
        color: #41464b !important;
    }

    .table-primary-green th {
        /* Giữ màu xanh làm nổi bật tiêu đề bảng */
        background-color: var(--bs-primary-green) !important;
        color: #fff !important;
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

    .ticket-detail-modal .booking-item {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .option-completed-hidden {
        display: none !important;
    }
</style>

<div class="container-fluid py-4">

    @php
    function ownerUrlWithStatus($status) {
    return request()->fullUrlWithQuery(['status' => $status, 'page' => 1, 'search' => request('search')]);
    }
    $statusAdminMap = [
    'pending' => ['bg' => 'bg-warning-subtle', 'text' => 'text-warning-emphasis', 'label' => 'Chờ xác nhận'],
    'confirmed' => ['bg' => 'bg-primary-subtle', 'text' => 'text-primary-emphasis', 'label' => 'Đã xác nhận'],
    'completed' => ['bg' => 'bg-success-subtle', 'text' => 'text-success-emphasis', 'label' => 'Hoàn thành'],
    'cancelled' => ['bg' => 'bg-danger-subtle', 'text' => 'text-danger-emphasis', 'label' => 'Đã hủy'],
    ];

    $paymentStatusLabels = [
    'unpaid' => 'Chưa thanh toán',
    'paid' => 'Đã thanh toán',
    'refunded' => 'Hoàn tiền'
    ];
    @endphp

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
        <div>
            <h1 class="fw-bold text-dark mb-1">Quản lý đặt sân</h1>
            <p class="text-muted small mb-0">Quản lý đơn đặt sân tại các sân của bạn.</p>
        </div>

        <div class="mt-3 mt-md-0 d-flex gap-2 align-items-center">
            <a href="{{ route('owner.bookings.create') }}" class="btn btn-success shadow-sm">
                <i class="fas fa-plus me-1"></i> Tạo đơn
            </a>
            <form id="search-form" action="{{ route('owner.bookings.index') }}" method="GET" class="d-flex gap-2">
                @if (request('status'))
                <input type="hidden" name="status" value="{{ request('status') }}">
                @endif

                <div class="input-group shadow-sm" style="width: 250px;">
                    <input type="text" name="search" class="form-control" placeholder="Tên khách, SĐT..."
                        value="{{ request('search') }}">
                    <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i></button>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body py-2 px-3">
            <ul class="nav nav-pills nav-fill gap-2 p-1 bg-light rounded">
                @php
                $tabs = [
                null => ['label' => 'Tất cả', 'icon' => 'fa-list', 'class' => 'bg-white text-primary'],
                'pending' => ['label' => 'Chờ xác nhận', 'icon' => 'fa-clock', 'class' => 'bg-warning text-dark'],
                'confirmed' => ['label' => 'Đã xác nhận', 'icon' => 'fa-check-circle', 'class' => 'bg-primary text-white'],
                'completed' => ['label' => 'Hoàn thành', 'icon' => 'fa-check-double', 'class' => 'bg-success text-white'],
                'cancelled' => ['label' => 'Đã hủy', 'icon' => 'fa-times-circle', 'class' => 'bg-danger text-white'],
                ];
                @endphp

                @foreach($tabs as $key => $tab)
                <li class="nav-item">
                    <button
                        type="button"
                        class="nav-link status-tab
        {{ request('status') == $key ? 'active '.$tab['class'].' shadow-sm' : 'text-muted' }}"
                        data-status="{{ $key }}"
                        data-active-class="{{ $tab['class'] }}">
                        <i class="fas {{ $tab['icon'] }} me-1"></i> {{ $tab['label'] }}
                    </button>

                </li>
                @endforeach
            </ul>

        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr class="text-secondary small fw-bold text-uppercase text-center">
                            <th style="width: 80px;">ID</th>
                            <th class="text-start">Khách hàng</th>
                            <th class="text-start">Sân / Thời gian</th>
                            <th class="text-center" style="width: 150px;">Tổng tiền</th>
                            <th style="width: 140px;">Trạng thái</th>
                            <th style="width: 140px;">Thanh toán</th>
                            <th style="width: 100px;">Chi tiết</th>
                        </tr>
                    </thead>
                    <tbody id="booking-table-body">
                        @forelse($tickets as $ticket)
                        @php
                        $allItems = $ticket->items;
                        $firstItem = $allItems->first();
                        $itemCount = $allItems->count();
                        $s = $statusAdminMap[$ticket->status] ?? ['bg' => 'bg-secondary-subtle', 'text' => 'text-secondary-emphasis', 'label' => ucfirst($ticket->status)];
                        @endphp

                        <tr>
                            <td class="text-center text-muted fw-bold">{{ $ticket->id }}</td>

                            <td>
                                <div class="d-flex align-items-center">

                                    <div>
                                        <span
                                            class="d-block fw-bold text-dark">{{ $ticket->user->name ?? 'N/A' }}</span>
                                        <small class="text-muted">{{ $ticket->user->phone ?? '---' }}</small>
                                    </div>
                                </div>
                            </td>

                            <td>
                                @if ($firstItem)
                                <div class="fw-bold text-dark mb-1">
                                    {{ $firstItem->booking->court->venue->name ?? 'N/A' }}
                                    <span class="fw-normal text-muted">-
                                        {{ $firstItem->booking->court->name ?? 'N/A' }}</span>
                                </div>
                                <div class="small text-muted">
                                    <i class="far fa-calendar-alt me-1"></i>
                                    {{ \Carbon\Carbon::parse($firstItem->booking->booking_date)->format('d/m/Y') }}
                                    <span class="mx-1">|</span>
                                    <i class="far fa-clock me-1"></i>
                                    {{ \Carbon\Carbon::parse($firstItem->booking->timeSlot->start_time)->format('H:i') }}
                                    -
                                    {{ \Carbon\Carbon::parse($firstItem->booking->timeSlot->end_time)->format('H:i') }}
                                </div>
                                @if ($itemCount > 1)
                                <span class="badge bg-secondary-subtle text-secondary rounded-pill mt-1">
                                    +{{ $itemCount - 1 }} sân khác
                                </span>
                                @endif
                                @else
                                <span class="text-muted">Không có dữ liệu sân</span>
                                @endif
                            </td>

                            <td class="text-center">
                                <span class="fw-bold text-danger fs-6">
                                    {{ number_format($ticket->total_amount, 0, ',', '.') }} đ
                                </span>
                            </td>

                            <td class="text-center">
                                <span
                                    class="badge {{ $s['bg'] }} {{ $s['text'] }} border border-opacity-10 rounded-pill px-3 py-2">
                                    {{ $s['label'] }}
                                </span>
                            </td>

                            <td class="text-center">
                                @php
                                $payClasses = [
                                'unpaid' => 'bg-danger-subtle text-danger-emphasis',
                                'paid' => 'bg-success-subtle text-success-emphasis',
                                'refunded' => 'bg-info-subtle text-info-emphasis',
                                ];
                                $pay = $payClasses[$ticket->payment_status] ?? 'bg-secondary-subtle text-secondary-emphasis';
                                @endphp
                                <span class="badge {{ $pay }} border border-opacity-10 rounded-pill px-3 py-2">
                                    {{ $paymentStatusLabels[$ticket->payment_status] ?? ucfirst($ticket->payment_status) }}
                                </span>
                            </td>

                            <td class="text-center">
                                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal"
                                    data-bs-target="#ticketModal{{ $ticket->id }}">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </td>

                            <div class="modal fade" id="ticketModal{{ $ticket->id }}" tabindex="-1"
                                aria-labelledby="ticketModalLabel{{ $ticket->id }}" aria-hidden="true">
                                <div class="modal-dialog modal-xl modal-dialog-centered">
                                    <div class="modal-content ticket-detail-modal border-0 shadow-lg">
                                        <div class="modal-header border-0">
                                            <h3 class="modal-title">
                                                <i class="fas fa-ticket-alt me-2"></i>Chi tiết Đơn #{{ $ticket->id }}
                                            </h3>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>

                                        <form method="POST" action="{{ route('owner.bookings.update', $ticket->id) }}">
                                            @csrf
                                            @method('PUT')

                                            <div class="modal-body bg-light">
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
                                                            $statusOrder = [
                                                            'pending' => 1, 'confirmed' => 2, 'completed' => 3, 'cancelled' => 99
                                                            ];
                                                            @endphp

                                                            <select name="status" class="form-select status-select-{{ $ticket->id }}"
                                                                data-original-status="{{ $ticket->status }}"
                                                                {{ in_array($ticket->status, ['completed','cancelled']) ? 'disabled' : '' }}>

                                                                @foreach(['pending' => 'Chờ xác nhận', 'confirmed' => 'Đã xác nhận', 'cancelled' => 'Đã hủy', 'completed' => 'Hoàn thành'] as $value => $label)
                                                                @php
                                                                $currentOrder = $statusOrder[$ticket->status] ?? 0;
                                                                $optionOrder = $statusOrder[$value] ?? 0;

                                                                $canTransition = ($value == $ticket->status) ||
                                                                ($value == 'cancelled' && $ticket->status != 'completed') ||
                                                                ($optionOrder > $currentOrder && $value != 'cancelled');

                                                                if (in_array($ticket->status, ['completed', 'cancelled']) && $value != $ticket->status) {
                                                                $canTransition = false;
                                                                }

                                                                $isCompletedOption = ($value == 'completed');
                                                                $hideCompletedOption = ($isCompletedOption && $ticket->payment_status == 'unpaid' && $ticket->status != 'completed');
                                                                @endphp

                                                                @if($canTransition)
                                                                <option
                                                                    value="{{ $value }}"
                                                                    {{ $ticket->status == $value ? 'selected' : '' }}
                                                                    class="{{ $isCompletedOption ? 'option-completed' : '' }} {{ $hideCompletedOption ? 'option-completed-hidden' : '' }}"
                                                                    data-initial-hidden="{{ $hideCompletedOption ? 'true' : 'false' }}">
                                                                    {{ $label }}
                                                                </option>
                                                                @endif
                                                                @endforeach
                                                            </select>

                                                            <small class="text-warning d-block mt-2 message-unpaid-confirmed-{{ $ticket->id }}" style="display: {{ ($ticket->status == 'confirmed' && $ticket->payment_status == 'unpaid') ? 'block' : 'none' }};">
                                                                <i class="fas fa-info-circle me-1"></i>
                                                                Vui lòng xác nhận thanh toán trước khi hoàn thành
                                                            </small>

                                                            <h6 class="text-muted mb-2 mt-3"><i class="fas fa-wallet me-2"></i>Thanh toán</h6>
                                                            <select name="payment_status" class="form-select payment-select-{{ $ticket->id }}"
                                                                data-ticket-id="{{ $ticket->id }}"
                                                                data-initial-payment-status="{{ $ticket->payment_status }}"
                                                                data-initial-order-status="{{ $ticket->status }}"
                                                                {{ in_array($ticket->status, ['completed','cancelled']) ? 'disabled' : '' }}>
                                                                @php
                                                                $paymentOrder = [ 'unpaid' => 1, 'paid' => 2, 'refunded' => 3 ];
                                                                $currentPaymentOrder = $paymentOrder[$ticket->payment_status] ?? 0;
                                                                @endphp

                                                                @foreach(['unpaid' => 'Chưa thanh toán', 'paid' => 'Đã thanh toán', 'refunded' => 'Hoàn tiền'] as $payValue => $payLabel)
                                                                @php
                                                                $optionPaymentOrder = $paymentOrder[$payValue] ?? 0;
                                                                $canChangePayment = ($payValue == $ticket->payment_status) || ($optionPaymentOrder > $currentPaymentOrder);

                                                                if ($payValue == 'refunded' && $ticket->payment_status == 'unpaid') {
                                                                $canChangePayment = false;
                                                                }
                                                                @endphp

                                                                @if($canChangePayment)
                                                                <option value="{{ $payValue }}" {{ $ticket->payment_status == $payValue ? 'selected' : '' }}>{{ $payLabel }}</option>
                                                                @endif
                                                                @endforeach
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

                                                <h5 class="fw-bold mb-3"><i class="fas fa-calendar-check me-2 text-success"></i>Chi tiết Sân ({{ $ticket->items->count() }})</h5>

                                                <div class="booking-list">
                                                    @forelse($ticket->items as $item)
                                                    <div class="booking-item border rounded-3 p-3 mb-2 bg-white shadow-sm">
                                                        <div class="d-flex justify-content-between align-items-center flex-wrap">
                                                            <div>
                                                                <p class="fw-semibold mb-1">{{ $item->booking->court->name ?? 'N/A' }}</p>
                                                                <small class="text-muted">
                                                                    {{ $item->booking->booking_date ?? '-' }} |
                                                                    {{ \Carbon\Carbon::parse($item->booking->timeSlot->start_time)->format('H:i') }} -
                                                                    {{ \Carbon\Carbon::parse($item->booking->timeSlot->end_time)->format('H:i') }}
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

                                                <div class="mt-4">
                                                    <h6 class="fw-bold mb-2"><i class="fas fa-sticky-note me-2 text-warning"></i>Ghi chú</h6>
                                                    <textarea name="notes" class="form-control" style="min-height: 80px;">{{ $ticket->notes }}</textarea>
                                                </div>
                                            </div>

                                            <div class="modal-footer border-0 bg-white d-flex justify-content-end gap-2">
                                                <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">
                                                    <i class="fas fa-times me-1"></i> Đóng
                                                </button>

                                                @if(!in_array($ticket->status, ['completed','cancelled']))
                                                <button type="submit" class="btn btn-primary px-4">
                                                    <i class="fas fa-save me-1"></i> Lưu thay đổi
                                                </button>
                                                @else
                                                <button type="button" class="btn btn-light px-4" disabled>
                                                    Đơn hàng đã khóa
                                                </button>
                                                @endif
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <div class="text-muted opacity-50">
                                    <i class="fas fa-clipboard-list fa-3x mb-3"></i>
                                    <h5>Không có dữ liệu đặt sân</h5>
                                    <p class="small">Hệ thống chưa ghi nhận đơn nào.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if ($tickets->hasPages())
        <div class="card-footer bg-white border-0 py-3">
            <div class="d-flex justify-content-center">
                {{ $tickets->withQueryString()->links('pagination::bootstrap-5') }}
            </div>
        </div>
        @endif
    </div>

</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Lặp qua tất cả các modal (tickets)
        @foreach($tickets as $ticket)
            (function() {
                var ticketId = {
                    {
                        $ticket - > id
                    }
                };
                var $paymentSelect = document.querySelector('.payment-select-' + ticketId);
                var $statusSelect = document.querySelector('.status-select-' + ticketId);
                var $completedOption = $statusSelect ? $statusSelect.querySelector('.option-completed') : null;
                var $unpaidConfirmedMessage = document.querySelector('.message-unpaid-confirmed-' + ticketId);
                var initialStatus = $statusSelect ? $statusSelect.dataset.originalStatus : '';

                if (!$paymentSelect || !$statusSelect) {
                    return;
                }

                // Nếu đơn hàng đã hoàn thành hoặc đã hủy, không cần theo dõi thay đổi
                if (initialStatus === 'completed' || initialStatus === 'cancelled') {
                    return;
                }

                function updateStatusOptions() {
                    var paymentStatus = $paymentSelect.value;
                    var currentOrderStatus = $statusSelect.value;

                    // 1. Logic cho tùy chọn "Hoàn thành"
                    if ($completedOption) {
                        if (paymentStatus === 'paid' || paymentStatus === 'refunded') {
                            // Nếu đã thanh toán/hoàn tiền: Hiển thị/Bật "Hoàn thành"
                            $completedOption.classList.remove('option-completed-hidden');
                        } else if (paymentStatus === 'unpaid') {
                            // Nếu chưa thanh toán: Ẩn/Vô hiệu hóa "Hoàn thành"

                            // Chỉ ẩn nếu trạng thái hiện tại chưa phải là 'completed' (để tránh lỗi)
                            if (currentOrderStatus !== 'completed') {
                                $completedOption.classList.add('option-completed-hidden');

                                // Nếu người dùng đang chọn 'completed' mà chuyển về 'unpaid', thì chuyển lại về 'confirmed' hoặc giữ nguyên 'pending'
                                if (currentOrderStatus === 'completed') {
                                    // Cố gắng đặt lại trạng thái về trạng thái hợp lệ cao nhất hiện tại (thường là confirmed/pending)
                                    var confirmedOption = $statusSelect.querySelector('option[value="confirmed"]');
                                    var pendingOption = $statusSelect.querySelector('option[value="pending"]');

                                    // Nếu có thể chuyển về confirmed, thì chuyển
                                    if (confirmedOption) {
                                        $statusSelect.value = 'confirmed';
                                    } else if (pendingOption) {
                                        $statusSelect.value = 'pending';
                                    }
                                }
                            }
                        }
                    }

                    // 2. Logic cho thông báo cảnh báo "unpaid/confirmed"
                    if ($unpaidConfirmedMessage) {
                        if (paymentStatus === 'unpaid' && $statusSelect.value === 'confirmed') {
                            $unpaidConfirmedMessage.style.display = 'block';
                        } else {
                            $unpaidConfirmedMessage.style.display = 'none';
                        }
                    }
                }

                // Xử lý sự kiện thay đổi trạng thái thanh toán
                $paymentSelect.addEventListener('change', updateStatusOptions);

                // Xử lý sự kiện thay đổi trạng thái đơn (để kiểm tra hiển thị thông báo)
                $statusSelect.addEventListener('change', function() {
                    var paymentStatus = $paymentSelect.value;
                    var currentOrderStatus = $statusSelect.value;

                    // Cập nhật thông báo
                    if ($unpaidConfirmedMessage) {
                        if (paymentStatus === 'unpaid' && currentOrderStatus === 'confirmed') {
                            $unpaidConfirmedMessage.style.display = 'block';
                        } else {
                            $unpaidConfirmedMessage.style.display = 'none';
                        }
                    }
                });

                // Chạy lần đầu tiên để đảm bảo trạng thái đúng sau khi load
                // updateStatusOptions(); // Không cần thiết nếu PHP đã xử lý đúng data-initial-hidden
            })();
        @endforeach
    });
</script>
<script>
    let tabs, tbody, searchInput;

    function loadBookings(status = null, page = 1) {
        const params = new URLSearchParams();

        if (status) params.append('status', status);
        if (searchInput && searchInput.value) params.append('search', searchInput.value);
        params.append('page', page);

        fetch("{{ route('owner.bookings.index') }}?" + params.toString(), {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.text())
        .then(html => {
            const temp = document.createElement('div');
            temp.innerHTML = html;

            const newTbody = temp.querySelector('#booking-table-body');
            if (newTbody) {
                tbody.innerHTML = newTbody.innerHTML;
            }

            // Update URL không reload
            const newUrl = "{{ route('owner.bookings.index') }}" + (params.toString() ? '?' + params.toString() : '');
            window.history.pushState({}, '', newUrl);
        });
    }

    document.addEventListener('DOMContentLoaded', function () {

        tabs = document.querySelectorAll('.status-tab');
        tbody = document.getElementById('booking-table-body');
        searchInput = document.querySelector('input[name="search"]');

        // Click tab
        tabs.forEach(tab => {
            tab.addEventListener('click', function () {

                tabs.forEach(t => {
                    t.classList.remove(
                        'active','shadow-sm','bg-warning','bg-primary',
                        'bg-success','bg-danger','bg-white','text-white','text-dark'
                    );
                    t.classList.add('text-muted');
                });

                const activeClass = this.dataset.activeClass;
                this.classList.add('active', 'shadow-sm');
                this.classList.remove('text-muted');

                if (activeClass) {
                    activeClass.split(' ').forEach(c => this.classList.add(c));
                }

                loadBookings(this.dataset.status || null, 1);
            });
        });

        // Submit search không reload
        const searchForm = document.getElementById('search-form');
        if (searchForm) {
            searchForm.addEventListener('submit', function (e) {
                e.preventDefault();
                const activeTab = document.querySelector('.status-tab.active');
                loadBookings(activeTab ? activeTab.dataset.status : null, 1);
            });
        }

        // Search realtime (debounce)
        let searchTimeout = null;
        if (searchInput) {
            searchInput.addEventListener('input', function () {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    const activeTab = document.querySelector('.status-tab.active');
                    loadBookings(activeTab ? activeTab.dataset.status : null, 1);
                }, 400);
            });
        }
    });
</script>

@endsection