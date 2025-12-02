@extends('app') {{-- Hoặc layouts.owner --}}

@section('content')
    <div class="container-fluid py-4 bg-light">

        {{-- Header & Filter --}}
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <div>
                <h4 class="fw-bold text-dark mb-0">Tổng quan Thu nhập</h4>
                <small class="text-muted">Theo dõi hiệu quả kinh doanh của bạn</small>
            </div>

            <form action="" method="GET" class="d-flex gap-2 shadow-sm p-2 bg-white rounded align-items-center">
                {{-- Kiểm tra biến tồn tại để tránh lỗi nếu controller chưa truyền --}}
                <input type="date" name="date_from" class="form-control form-control-sm border-0 bg-light"
                    value="{{ isset($start) ? $start->format('Y-m-d') : '' }}">
                <span class="align-self-center text-muted">-</span>
                <input type="date" name="date_to" class="form-control form-control-sm border-0 bg-light"
                    value="{{ isset($end) ? $end->format('Y-m-d') : '' }}">
                <button type="submit" class="btn btn-primary btn-sm px-3"><i class="fas fa-filter"></i> Xem</button>
            </form>
        </div>

        {{-- KPI Cards --}}
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white h-100 shadow-sm">
                    <div class="card-body">
                        <h6 class="card-title opacity-75">Tổng số dư khả dụng</h6>
                        <h3 class="fw-bold mb-0">{{ number_format($totalComplete ?? 0, 0, ',', '.') }} VND</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-dark h-100 shadow-sm">
                    <div class="card-body">
                        <h6 class="card-title opacity-75">Đang chờ xử lý</h6>
                        <h3 class="fw-bold mb-0">{{ number_format($totalPending ?? 0, 0, ',', '.') }} VND</h3>
                    </div>
                </div>
            </div>
        </div>

        {{-- Table Statistics --}}
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 text-gray-800">Lịch sử giao dịch</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">ID</th>
                            <th>Mã Booking</th>
                            <th class="text-end">Tổng đơn</th>
                            <th class="text-end">Voucher</th>
                            <th class="text-end">Phí sàn</th>
                            <th class="text-end">Thực nhận</th>
                            <th>Sân (Venue)</th>
                            <th>Thời gian</th>
                            <th>Trạng thái</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- SỬA LỖI 1: @forelse phải bọc lấy thẻ <tr> --}}
                        @forelse($historyPayments as $historyPayment)
                            <tr>
                                <td class="ps-3">#{{ $historyPayment->id }}</td>

                                {{-- SỬA LỖI 2: Gọi đúng tên cột trong DB --}}
                                <td class="fw-bold text-primary">{{ $historyPayment->booking_id }}</td>

                                <td class="text-end">{{ number_format($historyPayment->total_amount, 0, ',', '.') }}</td>

                                <td class="text-end text-danger">
                                    {{ $historyPayment->promotion_amount > 0 ? '-' . number_format($historyPayment->promotion_amount, 0, ',', '.') : '0' }}
                                </td>

                                <td class="text-end text-danger">
                                    -{{ number_format($historyPayment->admin_amount, 0, ',', '.') }}
                                </td>

                                <td class="text-end fw-bold text-success">
                                    +{{ number_format($historyPayment->venue_owner_amount, 0, ',', '.') }}
                                </td>

                                {{-- Giả sử bạn đã relationship 'venue' trong model MoneyFlow --}}
                                <td>{{ $historyPayment->venue->name ?? 'N/A' }}</td>

                                <td>{{ \Carbon\Carbon::parse($historyPayment->created_at)->format('d/m/Y H:i') }}</td>

                                <td>
                                    @if ($historyPayment->status == 'completed')
                                        <span class="badge bg-success">Thành công</span>
                                    @elseif($historyPayment->status == 'pending')
                                        <span class="badge bg-warning text-dark">Đang chờ</span>
                                    @else
                                        <span class="badge bg-danger">Hủy/Lỗi</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            {{-- Hiển thị khi không có dữ liệu --}}
                            <tr>
                                <td colspan="9" class="text-center py-4 text-muted">
                                    <i class="fas fa-inbox fa-2x mb-2"></i><br>
                                    Không có dữ liệu giao dịch nào trong khoảng thời gian này.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Phân trang (Nếu controller có paginate) --}}
            @if (method_exists($historyPayments, 'links'))
                <div class="d-flex justify-content-end p-3">
                    {{ $historyPayments->appends(request()->query())->links() }}
                </div>
            @endif
        </div>

    </div>
@endsection
