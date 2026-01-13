@extends('app')

@section('content')
    <style>
        /* --- Giao diện chung --- */
        .bg-main {
            background-color: #f4f7f6;
            min-height: 100vh;
        }

        .card-custom {
            border: none;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }

        /* --- Table Styles --- */
        .table-pro thead th {
            background-color: #fff;
            color: #8898aa;
            text-transform: uppercase;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 1px;
            padding: 15px;
            border-bottom: 1px solid #e9ecef;
        }

        .table-pro tbody td {
            padding: 15px;
            vertical-align: middle;
            font-size: 14px;
            color: #525f7f;
            background-color: #fff;
        }

        /* --- Modal Clean Style (Giống hình mẫu) --- */
        .modal-clean .modal-content {
            border-radius: 15px;
            overflow: hidden;
            border: none;
        }

        .modal-clean .modal-header {
            border-bottom: 1px solid #f1f1f1;
            padding: 20px 30px;
        }

        .info-label-sm {
            color: #adb5bd;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            margin-bottom: 4px;
        }

        .info-value-md {
            color: #2d3436;
            font-weight: 600;
            font-size: 15px;
        }

        .text-cyan-pro {
            color: #26ba99 !important;
        }

        /* Cột trái Modal */
        .modal-items-section {
            padding: 30px;
        }

        .table-items-list {
            width: 100%;
            margin-top: 15px;
        }

        .table-items-list th {
            font-size: 12px;
            color: #333;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }

        .table-items-list td {
            padding: 15px 0;
            border-bottom: 1px solid #f8f9fa;
        }

        .summary-box-light {
            background-color: #f1f6f7;
            border-radius: 10px;
            padding: 20px;
            margin-top: 25px;
        }

        /* Cột phải Modal (Trạng thái) */
        .status-sidebar {
            background-color: #f8f9fa;
            padding: 30px;
            height: 100%;
            border-left: 1px solid #eee;
        }

        .card-status-white {
            background: #fff;
            border: 1px solid #e9ecef;
            padding: 12px;
            border-radius: 8px;
            text-align: center;
            font-weight: 700;
            color: #495057;
            margin-bottom: 20px;
        }

        .card-payment-blue {
            background: #45b1d8;
            color: #fff;
            padding: 12px;
            border-radius: 8px;
            text-align: center;
            font-weight: 700;
            text-transform: uppercase;
            margin-bottom: 20px;
        }

        .btn-close-dark {
            background: #6c757d;
            color: #fff;
            width: 100%;
            padding: 12px;
            border-radius: 8px;
            font-weight: 600;
            border: none;
            transition: 0.3s;
        }

        .btn-close-dark:hover {
            background: #5a6268;
        }

        /* Badge Trạng thái tại bảng */
        .badge-soft {
            padding: 6px 12px;
            border-radius: 50px;
            font-weight: 500;
            font-size: 12px;
        }

        .badge-soft-success {
            background: #d1e7dd;
            color: #0f5132;
        }

        .badge-soft-warning {
            background: #fff3cd;
            color: #664d03;
        }

        .badge-soft-danger {
            background: #f8d7da;
            color: #842029;
        }

        .badge-soft-info {
            background: #cff4fc;
            color: #055160;
        }
    </style>

    <div class="bg-main py-4">
        <div class="container-fluid">
            {{-- HEADER --}}
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
                <div>
                    <h4 class="fw-bold mb-1">Quản lý đặt sân (Admin)</h4>
                    <p class="text-muted small mb-0">Hệ thống ghi nhận tổng cộng đơn hàng từ các chủ sân</p>
                </div>

                <form action="{{ url()->current() }}" method="GET" class="d-flex gap-2 mt-3 mt-md-0">
                    <select name="venue_id" class="form-select border-0 shadow-sm" style="min-width: 180px;"
                        onchange="this.form.submit()">
                        <option value="">-- Tất cả các sân --</option>
                        @foreach ($venues as $v)
                            <option value="{{ $v->id }}" {{ request('venue_id') == $v->id ? 'selected' : '' }}>
                                {{ $v->name }}</option>
                        @endforeach
                    </select>

                    <div class="input-group shadow-sm" style="min-width: 250px;">
                        <input type="text" name="search" class="form-control border-0"
                            placeholder="Tìm ID, Mã, Khách..." value="{{ request('search') }}">
                        <button class="btn btn-white bg-white text-muted border-0" type="submit">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </form>
            </div>

            {{-- DANH SÁCH --}}
            <div class="card card-custom overflow-hidden">
                <div class="table-responsive">
                    <table class="table table-pro mb-0">
                        <thead>
                            <tr>
                                <th class="text-center">Mã đơn</th>
                                <th>Khách hàng</th>
                                <th>Chi tiết đặt</th>
                                <th class="text-end">Tổng tiền</th>
                                <th class="text-center">Trạng thái</th>
                                <th class="text-center">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($tickets as $ticket)
                                <tr>
                                    <td class="text-center">
                                        <span class="fw-bold text-dark">#{{ $ticket->id }}</span><br>
                                        <small class="text-muted"
                                            style="font-size: 10px;">{{ $ticket->booking_code }}</small>
                                    </td>
                                    <td>
                                        @if ($ticket->guest)
                                            <div class="fw-bold text-dark">{{ explode(' - ', $ticket->guest)[0] }}</div>
                                            <div class="text-muted small">Khách vãng lai</div>
                                        @else
                                            <div class="fw-bold text-dark">{{ $ticket->user->name ?? 'N/A' }}</div>
                                            <div class="text-muted small">{{ $ticket->user->phone ?? '' }}</div>
                                        @endif
                                    </td>
                                    <td>
                                        @php $mainItem = $ticket->items->first(); @endphp
                                        @if ($mainItem && $mainItem->booking)
                                            <span class="text-primary fw-600">{{ $mainItem->booking->court->name }}</span>
                                            <div class="text-muted small">{{ $mainItem->booking->date }}</div>
                                        @else
                                            <span class="text-success">Dịch vụ lẻ</span>
                                        @endif
                                    </td>
                                    <td class="text-end fw-bold text-dark">{{ number_format($ticket->total_amount) }}₫</td>
                                    <td class="text-center">
                                        @php
                                            $statusMap = [
                                                'pending' => ['warning', 'Chờ xác nhận'],
                                                'confirmed' => ['info', 'Đã xác nhận'],
                                                'checkin' => ['primary', 'Đang đá'],
                                                'completed' => ['success', 'Hoàn thành'],
                                                'cancelled' => ['danger', 'Đã hủy'],
                                            ];
                                            $s = $statusMap[$ticket->status] ?? ['secondary', $ticket->status];
                                        @endphp
                                        <span class="badge-soft badge-soft-{{ $s[0] }}">{{ $s[1] }}</span>
                                    </td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-outline-primary rounded-pill px-3"
                                            data-bs-toggle="modal" data-bs-target="#ticketModal{{ $ticket->id }}">
                                            Chi tiết
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5">Không có dữ liệu đơn hàng.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="p-3">{{ $tickets->links('pagination::bootstrap-5') }}</div>
            </div>
        </div>
    </div>

    {{-- MODALS (GIỐNG HÌNH 1) --}}
    @foreach ($tickets as $ticket)
        <div class="modal fade modal-clean" id="ticketModal{{ $ticket->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="fw-bold mb-0">Chi tiết đơn #{{ $ticket->id }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-0">
                            {{-- Cột Trái --}}
                            <div class="col-md-7 modal-items-section">
                                <div class="d-flex justify-content-between mb-4">
                                    <div>
                                        <div class="info-label-sm">KHÁCH HÀNG</div>
                                        <div class="info-value-md">
                                            @if ($ticket->guest)
                                                {{ explode(' - ', $ticket->guest)[0] }}
                                            @else
                                                {{ $ticket->user->name ?? 'N/A' }}
                                            @endif
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <div class="info-label-sm">MÃ BOOKING</div>
                                        <div class="info-value-md text-cyan-pro">{{ $ticket->booking_code }}</div>
                                    </div>
                                </div>

                                <div class="info-label-sm mb-2">DANH SÁCH DỊCH VỤ</div>
                                <table class="table-items-list">
                                    <thead>
                                        <tr>
                                            <th>Tên</th>
                                            <th class="text-center">SL</th>
                                            <th class="text-end">Thành tiền</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($ticket->items as $item)
                                            <tr>
                                                <td>
                                                    <div class="fw-bold text-dark">
                                                        @if ($item->booking)
                                                            {{ $item->booking->court->name }}
                                                        @else
                                                            {{ $item->venueService->service->name ?? 'Dịch vụ lẻ' }}
                                                        @endif
                                                    </div>
                                                    @if ($item->booking)
                                                        <div class="text-muted small">{{ $item->booking->date }}
                                                            ({{ substr($item->booking->timeSlot->start_time, 0, 5) }})</div>
                                                    @endif
                                                </td>
                                                <td class="text-center">x{{ $item->quantity }}</td>
                                                <td class="text-end fw-bold text-dark">
                                                    {{ number_format($item->unit_price * $item->quantity) }}₫</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>

                                <div class="summary-box-light">
                                    <div class="d-flex justify-content-between mb-2 small text-muted">
                                        <span>Tạm tính:</span>
                                        <span>{{ number_format($ticket->total_amount + $ticket->discount_amount) }}₫</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-3 small text-danger">
                                        <span>Giảm giá:</span>
                                        <span>-{{ number_format($ticket->discount_amount) }}₫</span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                                        <span class="fw-bold fs-5">TỔNG CỘNG:</span>
                                        <span
                                            class="fw-bold fs-4 text-cyan-pro">{{ number_format($ticket->total_amount) }}₫</span>
                                    </div>
                                </div>
                            </div>

                            {{-- Cột Phải --}}
                            <div class="col-md-5">
                                <div class="status-sidebar">
                                    <div class="mb-4">
                                        <div class="info-label-sm mb-2">TRẠNG THÁI ĐƠN HÀNG</div>
                                        <div class="card-status-white shadow-sm">
                                            @php
                                                $labels = [
                                                    'pending' => 'Chờ xác nhận',
                                                    'confirmed' => 'Đã xác nhận',
                                                    'checkin' => 'Đang đá',
                                                    'completed' => 'Hoàn thành',
                                                    'cancelled' => 'Đã hủy',
                                                ];
                                            @endphp
                                            {{ $labels[$ticket->status] ?? $ticket->status }}
                                        </div>
                                    </div>

                                    <div class="mb-4">
                                        <div class="info-label-sm mb-2">THANH TOÁN</div>
                                        <div class="card-payment-blue shadow-sm"
                                            style="{{ $ticket->payment_status === 'paid' ? '' : 'background:#f8d7da; color:#842029' }}">
                                            {{ $ticket->payment_status === 'paid' ? 'ĐÃ THANH TOÁN' : 'CHƯA THANH TOÁN' }}
                                        </div>
                                    </div>

                                    <div class="mb-5">
                                        <div class="info-label-sm mb-1">NGÀY TẠO ĐƠN</div>
                                        <div class="text-muted small">{{ $ticket->created_at->format('d/m/Y H:i:s') }}
                                        </div>
                                    </div>

                                    <button class="btn-close-dark shadow-sm" data-bs-dismiss="modal">Đóng</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
@endsection
