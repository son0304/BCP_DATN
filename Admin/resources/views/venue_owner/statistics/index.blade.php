@extends('app')
@section('title', 'Báo cáo quản trị chủ sân')

@section('content')
    <div class="container-fluid py-4 bg-light min-vh-100">

        <!-- 1. BỘ LỌC THỜI GIAN -->
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body py-3">
                <form action="" method="GET" class="row g-3 align-items-center">
                    <div class="col-lg-3">
                        <h5 class="fw-bold mb-0 text-primary"><i class="fas fa-chart-line me-2"></i>Thống kê tổng quan</h5>
                    </div>
                    <div class="col-lg-3">
                        <select name="venue_id" class="form-select border-0 bg-light">
                            <option value="">Tất cả cơ sở</option>
                            @foreach ($allMyVenues as $v)
                                <option value="{{ $v->id }}" {{ request('venue_id') == $v->id ? 'selected' : '' }}>
                                    {{ $v->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-4">
                        <div class="input-group small">
                            <input type="date" name="date_from" class="form-control border-0 bg-light"
                                value="{{ $start->format('Y-m-d') }}">
                            <span class="input-group-text border-0 bg-light">đến</span>
                            <input type="date" name="date_to" class="form-control border-0 bg-light"
                                value="{{ $end->format('Y-m-d') }}">
                        </div>
                    </div>
                    <div class="col-lg-2">
                        <button type="submit" class="btn btn-primary w-100 fw-bold">Cập nhật</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- 2. KPI CARDS -->
        <div class="row g-3 mb-4 text-dark text-nowrap">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm p-3 rounded-4 bg-white border-start border-5 border-success h-100">
                    <small class="text-muted fw-bold text-uppercase">Doanh thu thực nhận</small>
                    <h3 class="fw-bold text-success mb-0 mt-1">{{ number_format($stats->total_net ?? 0) }}đ</h3>
                    <small class="text-muted fw-bold">Đã trừ voucher sân đã chi::
                        <span class="text-danger">-{{ number_format($stats->total_owner_voucher ?? 0) }}đ</span>
                    </small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm p-3 rounded-4 bg-white h-100">
                    <small class="text-muted fw-bold text-uppercase">Số lượt booking</small>
                    <h3 class="fw-bold text-primary mb-0 mt-1">{{ number_format($stats->total_bookings ?? 0) }}</h3>
                    <small class="text-muted small">Đơn thành công trong kỳ</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm p-3 rounded-4 bg-white text-center h-100">
                    <small class="text-muted fw-bold text-uppercase">Tỷ lệ lấp đầy</small>
                    <h3 class="fw-bold text-info mb-0 mt-1">{{ $occupancyRate }}%</h3>
                    <div class="progress mt-2" style="height: 6px;">
                        <div class="progress-bar bg-info" style="width: {{ $occupancyRate }}%"></div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm p-3 rounded-4 bg-white h-100 text-end">
                    <small class="text-muted fw-bold text-uppercase">Giá trị TB thực nhận</small>
                    <h3 class="fw-bold text-warning mb-0 mt-1">
                        {{ $stats->total_bookings > 0 ? number_format($stats->total_net / $stats->total_bookings) : 0 }}đ
                    </h3>
                    <small class="text-muted small">Trên mỗi đơn thành công</small>
                </div>
            </div>
        </div>

        <!-- 3. BIỂU ĐỒ -->
        <div class="card border-0 shadow-sm rounded-4 bg-white p-4 mb-4">
            <h6 class="fw-bold mb-4 text-dark"><i class="fas fa-chart-area me-2 text-success"></i>Biến động Doanh thu thực
                nhận theo ngày</h6>
            <div style="height: 300px;">
                <canvas id="revenueChart"></canvas>
            </div>
        </div>

        <!-- 4. BẢNG CHI TIẾT DÒNG TIỀN -->
        <div class="card border-0 shadow-sm rounded-4 bg-white text-dark">
            <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0 text-dark"><i class="fas fa-history text-muted me-2"></i>Sổ cái giao dịch</h5>
                <span class="badge bg-light text-muted fw-normal">{{ $transactions->total() }} giao dịch</span>
            </div>
            <div class="table-responsive">
                <table class="table align-middle table-hover mb-0">
                    <thead class="bg-light text-muted small text-uppercase">
                        <tr>
                            <th class="ps-4">Mã đơn / Cơ sở</th>
                            <th class="text-center">Trạng thái</th>
                            <th class="text-end">Doanh số đơn</th>
                            <th class="text-end text-danger">Voucher Sân</th>
                            <th class="text-end">Phí sàn</th>
                            <th class="text-end text-success">Thực nhận</th>
                            <th class="pe-4 text-end">Thời gian</th>
                        </tr>
                    </thead>
                    <tbody style="font-size: 0.875rem;">
                        @foreach ($transactions as $t)
                            @php
                                // Logic check Voucher của ai
                                $isOwnerVoucher = false;
                                if ($t->promotion && $t->promotion->creator && $t->promotion->creator->role) {
                                    $roleName = strtolower($t->promotion->creator->role->name);
                                    if (!str_contains($roleName, 'admin')) {
                                        $isOwnerVoucher = true;
                                    }
                                }

                                // Logic định dạng nhãn trạng thái
                                $statusLabel = [
                                    'completed' => [
                                        'text' => 'Thành công',
                                        'class' => 'bg-success-subtle text-success border border-success-subtle',
                                    ],
                                    'pending' => [
                                        'text' => 'Chờ xử lý',
                                        'class' =>
                                            'bg-warning-subtle text-warning-emphasis border border-warning-subtle',
                                    ],
                                    'failed' => [
                                        'text' => 'Thất bại',
                                        'class' => 'bg-danger-subtle text-danger border border-danger-subtle',
                                    ],
                                    'cancelled' => [
                                        'text' => 'Đã hủy',
                                        'class' => 'bg-secondary-subtle text-secondary border border-secondary-subtle',
                                    ],
                                ];
                                $currentStatus = $statusLabel[$t->status] ?? [
                                    'text' => $t->status,
                                    'class' => 'bg-light text-dark border',
                                ];
                            @endphp
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-bold text-dark">#{{ $t->money_flowable->booking_code ?? 'N/A' }}</div>
                                    <small class="text-muted">{{ $t->venue->name ?? 'N/A' }}</small>
                                </td>

                                <td class="text-center">
                                    <span class="badge {{ $currentStatus['class'] }} rounded-pill px-3 fw-normal"
                                        style="font-size: 0.75rem;">
                                        {{ $currentStatus['text'] }}
                                    </span>
                                </td>

                                <td class="text-end fw-bold">{{ number_format($t->total_amount) }}đ</td>

                                <td class="text-end">
                                    @if ($isOwnerVoucher)
                                        <span
                                            class="text-danger fw-bold">-{{ number_format($t->promotion_amount) }}đ</span>
                                    @elseif($t->promotion_id)
                                        <span class="text-muted small" title="Hệ thống tài trợ">0đ*</span>
                                    @else
                                        <span class="text-muted">0đ</span>
                                    @endif
                                </td>

                                <td class="text-end text-muted">{{ number_format($t->admin_amount) }}đ</td>
                                <td class="text-end fw-bold text-success">{{ number_format($t->venue_owner_amount) }}đ</td>
                                <td class="pe-4 text-end text-muted">
                                    <div>{{ $t->created_at->format('d/m/Y') }}</div>
                                    <small>{{ $t->created_at->format('H:i') }}</small>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-white border-0 py-3 d-flex justify-content-center">
                {{ $transactions->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            new Chart(document.getElementById('revenueChart'), {
                type: 'line',
                data: {
                    labels: @json($dailyData->pluck('date')),
                    datasets: [{
                        label: 'Doanh thu (đ)',
                        data: @json($dailyData->pluck('revenue')),
                        borderColor: '#198754',
                        backgroundColor: 'rgba(25, 135, 84, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.3
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: v => v.toLocaleString() + 'đ'
                            }
                        }
                    }
                }
            });
        });
    </script>
@endsection
