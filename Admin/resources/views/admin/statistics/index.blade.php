@extends('app')

@section('content')
    <div class="container-fluid py-4 bg-light" style="min-height: 100vh;">

        <!-- HEADER & FILTER -->
        <div class="row mb-4 align-items-center">
            <div class="col-md-6">
                <h4 class="fw-bold text-dark mb-1">
                    <i class="fas fa-chart-line me-2 text-primary"></i>Báo cáo Tài chính Hệ thống
                </h4>
                <p class="text-muted small mb-0">Theo dõi doanh thu từ Đặt sân và Gói quảng cáo (Sponsorship)</p>
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
                    <button class="btn btn-primary btn-sm px-4 fw-bold">Lọc</button>
                </form>
            </div>
        </div>

        <!-- KPI CARDS -->
        <div class="row g-3 mb-4">
            <!-- GMV -->
            <div class="col-md-3">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div class="icon-shape bg-primary-soft text-primary rounded-3 p-2">
                                <i class="fas fa-coins fa-lg"></i>
                            </div>
                            <span class="badge bg-soft-primary text-primary small">Tổng GMV</span>
                        </div>
                        <h3 class="fw-bold mb-0 text-dark">{{ number_format($finance->gmv ?? 0) }}đ</h3>
                        <p class="text-muted small mb-0 mt-1">Dòng tiền chảy qua hệ thống</p>
                    </div>
                </div>
            </div>
            <!-- PROFIT -->
            <div class="col-md-3">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div class="icon-shape bg-success-soft text-success rounded-3 p-2">
                                <i class="fas fa-wallet fa-lg"></i>
                            </div>
                            <span class="badge bg-soft-success text-success small">Thực thu Admin</span>
                        </div>
                        <h3 class="fw-bold mb-0 text-success">{{ number_format($finance->profit ?? 0) }}đ</h3>
                        <p class="text-muted small mb-0 mt-1">Lợi nhuận sau khi chia sẻ đối tác</p>
                    </div>
                </div>
            </div>
            <!-- TRANSACTIONS -->
            <div class="col-md-3">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div class="icon-shape bg-info-soft text-info rounded-3 p-2">
                                <i class="fas fa-receipt fa-lg"></i>
                            </div>
                            <span class="badge bg-soft-info text-info small">Giao dịch</span>
                        </div>
                        <h3 class="fw-bold mb-0 text-dark">{{ number_format($finance->txn_count ?? 0) }}</h3>
                        <p class="text-muted small mb-0 mt-1">Số đơn hàng thanh toán thành công</p>
                    </div>
                </div>
            </div>
            <!-- NEW USERS -->
            <div class="col-md-3">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div class="icon-shape bg-warning-soft text-warning rounded-3 p-2">
                                <i class="fas fa-user-plus fa-lg"></i>
                            </div>
                            <span class="badge bg-soft-warning text-warning small">User mới</span>
                        </div>
                        <h3 class="fw-bold mb-0 text-dark">{{ number_format($newUsers) }}</h3>
                        <p class="text-muted small mb-0 mt-1">Người dùng đăng ký trong kỳ</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- BIỂU ĐỒ CHÍNH (Bên trái) -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header bg-white py-3 border-0">
                        <h6 class="fw-bold mb-0">Biến động doanh thu & Lợi nhuận</h6>
                    </div>
                    <div class="card-body">
                        <div style="height: 350px;">
                            <canvas id="revenueLineChart"></canvas>
                        </div>
                    </div>
                </div>

                <div class="row g-3">
                    <!-- CƠ CẤU NGUỒN THU -->
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm rounded-4 h-100">
                            <div class="card-header bg-white py-3 border-0">
                                <h6 class="fw-bold mb-0">Cơ cấu Nguồn thu (Donut)</h6>
                            </div>
                            <div class="card-body">
                                <div style="height: 220px;">
                                    <canvas id="sourcePieChart"></canvas>
                                </div>
                                <div class="mt-4">
                                    @foreach ($revenueSources as $rs)
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <div class="d-flex align-items-center">
                                                <div
                                                    class="dot me-2 {{ $rs->source == 'Đặt sân' ? 'bg-primary' : 'bg-orange' }}">
                                                </div>
                                                <span class="text-muted small">{{ $rs->source }}</span>
                                            </div>
                                            <span class="fw-bold small">{{ number_format($rs->total) }}đ</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- THỐNG KÊ BỘ MÔN -->
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm rounded-4 h-100">
                            <div class="card-header bg-white py-3 border-0">
                                <h6 class="fw-bold mb-0">Hiệu quả theo Bộ môn</h6>
                            </div>
                            <div class="card-body">
                                @php $bookingTotal = $revenueSources->where('source', 'Đặt sân')->first()->total ?? 1; @endphp
                                @foreach ($sportStats as $sport)
                                    <div class="mb-4">
                                        <div class="d-flex justify-content-between small mb-1">
                                            <span class="fw-bold">{{ $sport->name }}</span>
                                            <span class="text-muted">{{ number_format($sport->value) }}đ</span>
                                        </div>
                                        <div class="progress rounded-pill" style="height: 10px;">
                                            <div class="progress-bar bg-info shadow-none" role="progressbar"
                                                style="width: {{ ($sport->value / $bookingTotal) * 100 }}%"></div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- CỘT PHẢI: TOP ĐỐI TÁC -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header bg-white py-3 border-0">
                        <h6 class="fw-bold mb-0 text-primary">Top Đối tác đóng góp cao</h6>
                    </div>
                    <div class="table-responsive">
                        <table class="table align-middle table-hover mb-0">
                            <thead class="bg-light">
                                <tr class="text-muted small">
                                    <th class="border-0 ps-3">Sân bóng</th>
                                    <th class="border-0 text-center">Đơn</th>
                                    <th class="border-0 text-end pe-3">Hoa hồng</th>
                                </tr>
                            </thead>
                            <tbody class="border-0">
                                @foreach ($topVenues as $v)
                                    <tr>
                                        <td class="ps-3 border-0">
                                            <div class="fw-bold text-dark">{{ $v->name }}</div>
                                        </td>
                                        <td class="text-center border-0 small">{{ $v->total_bookings }}</td>
                                        <td class="text-end pe-3 border-0 fw-bold text-success">
                                            {{ number_format($v->total_admin_profit) }}đ
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- ĐỊA PHƯƠNG & RECOMMENDATION -->
                <div class="card border-0 shadow-sm rounded-4 bg-primary text-white">
                    <div class="card-body p-4">
                        <h6 class="fw-bold mb-3"><i class="fas fa-lightbulb me-2"></i>Thị trường trọng điểm</h6>
                        <div class="d-flex flex-wrap gap-2 mb-4">
                            @foreach ($geoStats as $geo)
                                <span class="badge bg-white text-primary px-3 py-2 rounded-pill shadow-sm">
                                    {{ $geo->name }}
                                </span>
                            @endforeach
                        </div>
                        <div class="bg-white bg-opacity-10 p-3 rounded-3 border border-white border-opacity-25">
                            <p class="small mb-0">
                                Gợi ý: Khu vực <strong>{{ $geoStats->first()->name ?? '...' }}</strong> đang mang lại doanh
                                thu cao nhất. Bạn nên tập trung chương trình khuyến mãi cho các sân tại đây để tối ưu lợi
                                nhuận.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- CSS BỔ SUNG ĐỂ GIAO DIỆN MƯỢT HƠN -->
    <style>
        .bg-primary-soft {
            background-color: rgba(13, 110, 253, 0.1);
        }

        .bg-success-soft {
            background-color: rgba(25, 135, 84, 0.1);
        }

        .bg-info-soft {
            background-color: rgba(13, 202, 240, 0.1);
        }

        .bg-warning-soft {
            background-color: rgba(255, 193, 7, 0.1);
        }

        .bg-soft-primary {
            background-color: rgba(13, 110, 253, 0.05);
        }

        .bg-soft-success {
            background-color: rgba(25, 135, 84, 0.05);
        }

        .bg-soft-info {
            background-color: rgba(13, 202, 240, 0.05);
        }

        .bg-soft-warning {
            background-color: rgba(255, 193, 7, 0.05);
        }

        .dot {
            height: 10px;
            width: 10px;
            border-radius: 50%;
            display: inline-block;
        }

        .bg-orange {
            background-color: #fd7e14;
        }

        .card {
            transition: transform 0.2s ease;
        }

        .card:hover {
            transform: translateY(-3px);
        }
    </style>

    <!-- CHART JS SCRIPTS -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Biểu đồ đường (Revenue & Profit)
            const lineCtx = document.getElementById('revenueLineChart').getContext('2d');
            const profitGradient = lineCtx.createLinearGradient(0, 0, 0, 400);
            profitGradient.addColorStop(0, 'rgba(25, 135, 84, 0.1)');
            profitGradient.addColorStop(1, 'rgba(25, 135, 84, 0)');

            new Chart(lineCtx, {
                type: 'line',
                data: {
                    labels: @json($chartData->pluck('date')),
                    datasets: [{
                            label: 'Tổng GMV',
                            data: @json($chartData->pluck('gmv')),
                            borderColor: '#0d6efd',
                            borderWidth: 3,
                            pointBackgroundColor: '#fff',
                            pointBorderColor: '#0d6efd',
                            pointRadius: 4,
                            tension: 0.4,
                            fill: false
                        },
                        {
                            label: 'Lợi nhuận Admin',
                            data: @json($chartData->pluck('profit')),
                            borderColor: '#198754',
                            backgroundColor: profitGradient,
                            borderWidth: 2,
                            pointRadius: 0,
                            fill: true,
                            tension: 0.4
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: {
                                usePointStyle: true,
                                boxWidth: 6
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                borderDash: [5, 5],
                                drawBorder: false
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });

            // Biểu đồ tròn (Revenue Source)
            new Chart(document.getElementById('sourcePieChart'), {
                type: 'doughnut',
                data: {
                    labels: @json($revenueSources->pluck('source')),
                    datasets: [{
                        data: @json($revenueSources->pluck('total')),
                        backgroundColor: ['#0d6efd', '#fd7e14', '#6c757d'],
                        borderWidth: 0,
                        hoverOffset: 10
                    }]
                },
                options: {
                    cutout: '80%',
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
