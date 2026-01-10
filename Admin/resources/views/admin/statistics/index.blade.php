@extends('app')

@section('content')
    <div class="container-fluid py-4 bg-light" style="min-height: 100vh;">

        <!-- HEADER & FILTER -->
        <div class="row mb-4 align-items-center">
            <div class="col-md-6">
                <h4 class="fw-bold text-dark mb-1">
                    <i class="fas fa-chart-pie me-2 text-primary"></i>Thống kê Doanh thu Admin
                </h4>
                <p class="text-muted small mb-0">Báo cáo chi tiết nguồn thu từ Hoa hồng và Quảng cáo</p>
            </div>
            <div class="col-md-6">
                <form class="d-flex gap-2 justify-content-md-end bg-white p-2 rounded-3 shadow-sm border">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light border-0"><i class="fas fa-calendar-alt"></i></span>
                        <input type="date" name="date_from" class="form-control border-0 bg-light"
                            value="{{ $start->format('Y-m-d') }}">
                    </div>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light border-0">đến</span>
                        <input type="date" name="date_to" class="form-control border-0 bg-light"
                            value="{{ $end->format('Y-m-d') }}">
                    </div>
                    <button class="btn btn-primary btn-sm px-4 fw-bold">Xem báo cáo</button>
                </form>
            </div>
        </div>

        <!-- 1. KPI CARDS - TÁCH BIỆT DOANH THU -->
        <div class="row g-3 mb-4">
            <!-- TỔNG LỢI NHUẬN -->
            <div class="col-md-3">
                <div class="card border-0 shadow-sm rounded-4 bg-primary text-white h-100">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="badge bg-white text-primary bg-opacity-75">Tổng Thực Thu</span>
                            <i class="fas fa-wallet fa-lg opacity-50"></i>
                        </div>
                        <h3 class="fw-bold mb-0">{{ number_format($finance->total_profit ?? 0) }}đ</h3>
                        <p class="small mb-0 opacity-75 mt-1">Tổng lợi nhuận ròng của Admin</p>
                    </div>
                </div>
            </div>

            <!-- HOA HỒNG ĐẶT SÂN -->
            <div class="col-md-3">
                <div class="card border-0 shadow-sm rounded-4 h-100 border-start border-4 border-success">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-success fw-bold small text-uppercase">Hoa hồng Đặt sân</span>
                            <div class="icon-shape bg-success-soft text-success rounded-circle p-2">
                                <i class="fas fa-ticket-alt"></i>
                            </div>
                        </div>
                        <h4 class="fw-bold mb-0 text-dark">{{ number_format($finance->commission_revenue ?? 0) }}đ</h4>
                        <p class="text-muted small mb-0 mt-1">Từ {{ number_format($finance->total_txns ?? 0) }} giao dịch
                            booking</p>
                    </div>
                </div>
            </div>

            <!-- DOANH THU QUẢNG CÁO -->
            <div class="col-md-3">
                <div class="card border-0 shadow-sm rounded-4 h-100 border-start border-4 border-warning">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-warning fw-bold small text-uppercase">Gói Quảng cáo</span>
                            <div class="icon-shape bg-warning-soft text-warning rounded-circle p-2">
                                <i class="fas fa-ad"></i>
                            </div>
                        </div>
                        <h4 class="fw-bold mb-0 text-dark">{{ number_format($finance->ads_revenue ?? 0) }}đ</h4>
                        <p class="text-muted small mb-0 mt-1">Bán gói tài trợ/ưu tiên hiển thị</p>
                    </div>
                </div>
            </div>

            <!-- GMV -->
            <div class="col-md-3">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted small text-uppercase">Tổng GMV</span>
                            <i class="fas fa-exchange-alt text-muted"></i>
                        </div>
                        <h4 class="fw-bold mb-0 text-muted">{{ number_format($finance->gmv ?? 0) }}đ</h4>
                        <p class="text-muted small mb-0 mt-1">Dòng tiền qua hệ thống</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- 2. BIỂU ĐỒ STACKED (QUAN TRỌNG) -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                        <h6 class="fw-bold mb-0">Biểu đồ Lợi nhuận theo ngày</h6>
                    </div>
                    <div class="card-body">
                        <div style="height: 350px;">
                            <canvas id="profitChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 3. CƠ CẤU & TOP ĐỐI TÁC -->
            <div class="col-lg-4">
                <!-- Biểu đồ tròn cơ cấu -->
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-body">
                        <h6 class="fw-bold mb-3 small text-uppercase text-muted">Tỷ trọng nguồn thu</h6>
                        <div style="height: 200px; position: relative;">
                            <canvas id="structureChart"></canvas>
                        </div>
                        <div class="mt-3 text-center d-flex justify-content-center gap-3">
                            <div class="small"><span class="dot bg-success me-1"></span>Hoa hồng</div>
                            <div class="small"><span class="dot bg-warning me-1"></span>Quảng cáo</div>
                        </div>
                    </div>
                </div>

                <!-- Top Venue -->
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-header bg-white py-3 border-0">
                        <h6 class="fw-bold mb-0 small text-uppercase text-primary">Top Sân (Đóng góp Hoa hồng)</h6>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 small">
                            <thead class="bg-light">
                                <tr>
                                    <th class="border-0 ps-3">Tên sân</th>
                                    <th class="border-0 text-end pe-3">Hoa hồng</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($topVenues as $v)
                                    <tr>
                                        <td class="ps-3 border-0 fw-bold">{{ $v->name }}</td>
                                        <td class="text-end pe-3 border-0 text-success fw-bold">
                                            +{{ number_format($v->total_commission) }}đ
                                        </td>
                                    </tr>
                                @endforeach
                                @if ($topVenues->isEmpty())
                                    <tr>
                                        <td colspan="2" class="text-center py-3 text-muted">Chưa có dữ liệu</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .bg-success-soft {
            background-color: rgba(25, 135, 84, 0.1);
        }

        .bg-warning-soft {
            background-color: rgba(255, 193, 7, 0.1);
        }

        .dot {
            height: 10px;
            width: 10px;
            border-radius: 50%;
            display: inline-block;
        }

        .bg-warning {
            background-color: #ffc107 !important;
        }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // 1. BIỂU ĐỒ CỘT CHỒNG (STACKED BAR)
            const ctxBar = document.getElementById('profitChart').getContext('2d');
            new Chart(ctxBar, {
                type: 'bar',
                data: {
                    labels: @json($chartData->pluck('date')),
                    datasets: [{
                            label: 'Hoa hồng Booking',
                            data: @json($chartData->pluck('commission')),
                            backgroundColor: '#198754', // Xanh lá
                            borderRadius: 4,
                        },
                        {
                            label: 'Quảng cáo',
                            data: @json($chartData->pluck('ads')),
                            backgroundColor: '#ffc107', // Vàng
                            borderRadius: 4,
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: {
                            stacked: true,
                            grid: {
                                display: false
                            }
                        },
                        y: {
                            stacked: true,
                            beginAtZero: true,
                            grid: {
                                borderDash: [5, 5]
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            position: 'bottom'
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false
                        }
                    }
                }
            });

            // 2. BIỂU ĐỒ TRÒN (DOUGHNUT)
            const ctxPie = document.getElementById('structureChart').getContext('2d');
            new Chart(ctxPie, {
                type: 'doughnut',
                data: {
                    labels: ['Hoa hồng Booking', 'Quảng cáo'],
                    datasets: [{
                        data: [{{ $finance->commission_revenue ?? 0 }},
                            {{ $finance->ads_revenue ?? 0 }}
                        ],
                        backgroundColor: ['#198754', '#ffc107'],
                        borderWidth: 0,
                        hoverOffset: 4
                    }]
                },
                options: {
                    cutout: '70%',
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });
        });
    </script>
@endsection
