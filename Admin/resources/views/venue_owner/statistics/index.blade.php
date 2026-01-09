@extends('app')
@section('title', 'Báo cáo quản trị chủ sân')

@section('content')
    <div class="container-fluid py-4 bg-light min-vh-100">

        <!-- TOP FILTER BAR -->
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body py-3">
                <form action="" method="GET" class="row g-3 align-items-center">
                    <div class="col-lg-3">
                        <h5 class="fw-bold mb-0 text-primary"><i class="fas fa-chart-bar me-2"></i>Thống kê tổng quan</h5>
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
                        <div class="input-group">
                            <input type="date" name="date_from" class="form-control border-0 bg-light"
                                value="{{ $start->format('Y-m-d') }}">
                            <span class="input-group-text border-0 bg-light">đến</span>
                            <input type="date" name="date_to" class="form-control border-0 bg-light"
                                value="{{ $end->format('Y-m-d') }}">
                        </div>
                    </div>
                    <div class="col-lg-2">
                        <button type="submit" class="btn btn-primary w-100 fw-bold shadow-sm">Cập nhật dữ liệu</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- KPI CARDS -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm p-3 rounded-4 bg-white">
                    <small class="text-muted fw-bold text-uppercase">Doanh thu thực nhận</small>
                    <h3 class="fw-bold text-success mb-0 mt-1">{{ number_format($stats->total_net ?? 0) }}đ</h3>
                    <small class="text-muted">Phí sàn: {{ number_format($stats->total_fee ?? 0) }}đ</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm p-3 rounded-4 bg-white">
                    <small class="text-muted fw-bold text-uppercase">Số lượt booking</small>
                    <h3 class="fw-bold text-primary mb-0 mt-1">{{ number_format($stats->total_bookings ?? 0) }}</h3>
                    <small class="text-muted">Đơn đặt sân thành công</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm p-3 rounded-4 bg-white text-center">
                    <small class="text-muted fw-bold text-uppercase">Tỷ lệ lấp đầy</small>
                    <h3 class="fw-bold text-info mb-0 mt-1">{{ $occupancyRate }}%</h3>
                    <div class="progress mt-2" style="height: 6px;">
                        <div class="progress-bar bg-info" style="width: {{ $occupancyRate }}%"></div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm p-3 rounded-4 bg-white">
                    <small class="text-muted fw-bold text-uppercase">Giá trị TB đơn</small>
                    <h3 class="fw-bold text-warning mb-0 mt-1">
                        {{ $stats->total_bookings > 0 ? number_format($stats->total_net / $stats->total_bookings) : 0 }}đ
                    </h3>
                    <small class="text-muted">Tính trên mỗi lượt đặt</small>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <!-- BIỂU ĐỒ -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
                    <div class="card-header bg-white py-3 border-0 fw-bold">Biến động Doanh thu & Khách hàng</div>
                    <div class="card-body">
                        <canvas id="dailyChart" style="height: 350px;"></canvas>
                    </div>
                </div>

                <div class="card border-0 shadow-sm rounded-4 bg-white">
                    <div class="card-header bg-white py-3 border-0 d-flex justify-content-between">
                        <span class="fw-bold">Phân tích Khung giờ trống (Mật độ lãng phí)</span>
                    </div>
                    <div class="card-body">
                        <canvas id="vacancyChart" style="height: 300px;"></canvas>
                    </div>
                </div>
            </div>

            <!-- CỘT PHẢI: XẾP HẠNG -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
                    <div class="card-header bg-white py-3 border-0 fw-bold"><i
                            class="fas fa-trophy text-warning me-2"></i>Doanh thu theo Cơ sở</div>
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <tbody>
                                @foreach ($revenueByVenue as $v)
                                    <tr>
                                        <td class="ps-3 fw-bold">{{ $v->name }}</td>
                                        <td class="text-end pe-3 text-primary fw-bold">{{ number_format($v->total) }}đ</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card border-0 shadow-sm rounded-4 bg-white">
                    <div class="card-header bg-white py-3 border-0 fw-bold"><i class="fas fa-box text-info me-2"></i>Dịch vụ
                        bán chạy</div>
                    <div class="table-responsive">
                        <table class="table align-middle mb-0 small">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-3">Dịch vụ</th>
                                    <th class="text-center">Số lượng</th>
                                    <th class="text-end pe-3">Doanh thu</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($revenueByService as $s)
                                    <tr>
                                        <td class="ps-3 fw-bold">{{ $s->name }}</td>
                                        <td class="text-center">{{ $s->qty }}</td>
                                        <td class="text-end pe-3 text-success fw-bold">
                                            {{ number_format($s->total_revenue) }}đ</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- BẢNG LỊCH SỬ GIAO DỊCH -->
        <div class="card border-0 shadow-sm rounded-4 bg-white">
            <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0 text-dark"><i class="fas fa-history text-muted me-2"></i>Sổ cái dòng tiền (Money
                    Flow)</h5>
                <span class="badge bg-light text-muted fw-normal">{{ $transactions->total() }} giao dịch</span>
            </div>
            <div class="table-responsive">
                <table class="table align-middle table-hover mb-0">
                    <thead class="bg-light text-muted small text-uppercase">
                        <tr>
                            <th class="ps-4">ID / Nguồn tiền</th>
                            <th>Cơ sở</th>
                            <th class="text-end">Tổng tiền</th>
                            <th class="text-end text-danger">Khuyến mãi</th>
                            <th class="text-end">Phí sàn</th>
                            <th class="text-end text-success">Thực nhận</th>
                            <th class="text-center">Trạng thái</th>
                            <th class="pe-4 text-end">Thời gian</th>
                        </tr>
                    </thead>
                    <tbody style="font-size: 0.875rem;">
                        @forelse($transactions as $t)
                            <tr>
                                <td class="ps-4">
                                    <span class="fw-bold text-dark">#{{ $t->id }}</span><br>
                                    <small class="text-muted">
                                        @if ($t->money_flowable_type === \App\Models\Ticket::class)
                                            <i class="fas fa-ticket-alt me-1"></i>
                                            {{ $t->money_flowable->booking_code ?? 'Booking' }}
                                        @elseif($t->money_flowable_type === \App\Models\SponsoredVenue::class)
                                            <span class="badge bg-primary-soft text-primary"><i
                                                    class="fas fa-ad me-1"></i> QUẢNG CÁO</span>
                                        @else
                                            Nguồn khác
                                        @endif
                                    </small>
                                </td>
                                <td class="text-dark">{{ $t->venue->name ?? 'N/A' }}</td>
                                <td class="text-end fw-bold">{{ number_format($t->total_amount) }}đ</td>
                                <td class="text-end text-danger">-{{ number_format($t->promotion_amount) }}đ</td>
                                <td class="text-end text-muted">{{ number_format($t->admin_amount) }}đ</td>
                                <td class="text-end fw-bold text-success">{{ number_format($t->venue_owner_amount) }}đ
                                </td>
                                <td class="text-center">
                                    @if ($t->status == 'completed')
                                        <span class="badge rounded-pill bg-success-soft text-success px-3">Thành
                                            công</span>
                                    @elseif($t->status == 'pending')
                                        <span class="badge rounded-pill bg-warning-soft text-warning px-3">Chờ xử lý</span>
                                    @else
                                        <span
                                            class="badge rounded-pill bg-light text-muted px-3">{{ $t->status }}</span>
                                    @endif
                                </td>
                                <td class="pe-4 text-end text-muted">
                                    <div>{{ $t->created_at->format('d/m/Y') }}</div>
                                    <small>{{ $t->created_at->format('H:i') }}</small>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">Chưa có giao dịch phát sinh trong
                                    kỳ này.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-white border-0 py-3">
                {{ $transactions->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>

    <style>
        .bg-success-soft {
            background-color: #e6f7ef;
        }

        .bg-warning-soft {
            background-color: #fff8eb;
        }

        .bg-primary-soft {
            background-color: #e7f1ff;
        }

        .table-hover tbody tr:hover {
            background-color: #f8f9fa;
        }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // BIỂU ĐỒ DOANH THU
            new Chart(document.getElementById('dailyChart'), {
                type: 'bar',
                data: {
                    labels: @json($dailyData->pluck('date')),
                    datasets: [{
                            label: 'Doanh thu (đ)',
                            data: @json($dailyData->pluck('revenue')),
                            backgroundColor: '#198754',
                            borderRadius: 5,
                            yAxisID: 'y'
                        },
                        {
                            label: 'Lượt booking',
                            data: @json($dailyData->pluck('bookings')),
                            backgroundColor: '#0d6efd',
                            borderRadius: 5,
                            yAxisID: 'y1'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            type: 'linear',
                            position: 'left',
                            title: {
                                display: true,
                                text: 'Tiền (đ)'
                            }
                        },
                        y1: {
                            type: 'linear',
                            position: 'right',
                            grid: {
                                drawOnChartArea: false
                            },
                            title: {
                                display: true,
                                text: 'Lượt đặt'
                            }
                        }
                    }
                }
            });

            // BIỂU ĐỒ TRỐNG
            const vacancyVals = @json($vacancyValues);
            const maxV = Math.max(...vacancyVals);

            new Chart(document.getElementById('vacancyChart'), {
                type: 'bar',
                data: {
                    labels: @json($vacancyLabels),
                    datasets: [{
                        label: 'Lượt trống',
                        data: vacancyVals,
                        backgroundColor: vacancyVals.map(v => v / maxV > 0.7 ? '#dc3545' :
                            '#6c757d'),
                        borderRadius: 5
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
                            beginAtZero: true
                        }
                    }
                }
            });
        });
    </script>
@endsection
