@extends('app') {{-- Hoặc layouts.admin tùy bạn --}}

@section('content')
    <div class="container-fluid py-4 bg-light">

        {{-- Header & Filter --}}
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <div>
                <h4 class="fw-bold text-dark mb-0">Thống kê Sàn</h4>
                <small class="text-muted">Tổng quan hiệu quả kinh doanh</small>
            </div>

            <form action="" method="GET" class="d-flex gap-2 shadow-sm p-2 bg-white rounded">
                <input type="date" name="date_from" class="form-control form-control-sm border-0 bg-light"
                    value="{{ $start->format('Y-m-d') }}">
                <span class="align-self-center text-muted">-</span>
                <input type="date" name="date_to" class="form-control form-control-sm border-0 bg-light"
                    value="{{ $end->format('Y-m-d') }}">
                <button type="submit" class="btn btn-primary btn-sm px-3"><i class="fas fa-filter"></i> Lọc</button>
            </form>
        </div>

        {{-- KPI CARDS --}}
        <div class="row g-3 mb-4">
            {{-- Card 1: Lợi nhuận --}}
            <div class="col-xl-3 col-md-6">
                <div class="card border-0 shadow-sm h-100 border-start border-4 border-success">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-muted text-uppercase small fw-bold mb-1">Lợi nhuận ròng (Net)</p>
                                <h3 class="fw-bold text-dark mb-0">{{ number_format($netProfit, 0, ',', '.') }} <span
                                        class="fs-6 text-muted">đ</span></h3>
                            </div>
                            <div class="bg-success bg-opacity-10 p-3 rounded-circle text-success">
                                <i class="fas fa-coins fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Card 2: GMV --}}
            <div class="col-xl-3 col-md-6">
                <div class="card border-0 shadow-sm h-100 border-start border-4 border-primary">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-muted text-uppercase small fw-bold mb-1">Tổng dòng tiền (GMV)</p>
                                <h3 class="fw-bold text-dark mb-0">{{ number_format($totalGMV, 0, ',', '.') }} <span
                                        class="fs-6 text-muted">đ</span></h3>
                            </div>
                            <div class="bg-primary bg-opacity-10 p-3 rounded-circle text-primary">
                                <i class="fas fa-chart-line fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Card 3: Trả đối tác --}}
            <div class="col-xl-3 col-md-6">
                <div class="card border-0 shadow-sm h-100 border-start border-4 border-warning">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-muted text-uppercase small fw-bold mb-1">Phải trả Chủ sân</p>
                                <h3 class="fw-bold text-dark mb-0">{{ number_format($partnerPay, 0, ',', '.') }} <span
                                        class="fs-6 text-muted">đ</span></h3>
                            </div>
                            <div class="bg-warning bg-opacity-10 p-3 rounded-circle text-warning">
                                <i class="fas fa-hand-holding-usd fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Card 4: Số đơn --}}
            <div class="col-xl-3 col-md-6">
                <div class="card border-0 shadow-sm h-100 border-start border-4 border-info">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-muted text-uppercase small fw-bold mb-1">Tổng giao dịch</p>
                                <h3 class="fw-bold text-dark mb-0">{{ number_format($totalTxn) }}</h3>
                            </div>
                            <div class="bg-info bg-opacity-10 p-3 rounded-circle text-info">
                                <i class="fas fa-receipt fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3">
            {{-- LEFT: CHART --}}
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white py-3 border-0">
                        <h6 class="mb-0 fw-bold"><i class="fas fa-chart-area me-2 text-primary"></i>Biểu đồ Lợi nhuận</h6>
                    </div>
                    <div class="card-body">
                        <canvas id="adminProfitChart" style="max-height: 350px;"></canvas>
                    </div>
                </div>
            </div>

            {{-- RIGHT: TOP VENUES --}}
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white py-3 border-0">
                        <h6 class="mb-0 fw-bold"><i class="fas fa-trophy me-2 text-warning"></i>Top Sân Hiệu Quả</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light text-secondary small text-uppercase">
                                    <tr>
                                        <th class="ps-4">Tên sân</th>
                                        <th class="text-end pe-4">Đóng góp</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($topVenues as $venue)
                                        <tr>
                                            <td class="ps-4 fw-bold text-secondary">{{ Str::limit($venue->name, 20) }}</td>
                                            <td class="text-end pe-4 text-success fw-bold">
                                                +{{ number_format($venue->profit_contribution, 0, ',', '.') }}đ
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="2" class="text-center py-4 text-muted">Chưa có dữ liệu</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- SCRIPT --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const ctx = document.getElementById('adminProfitChart').getContext('2d');
            const data = @json($chartData);

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: data.map(d => {
                        const date = new Date(d.date);
                        return date.toLocaleDateString('vi-VN', {
                            day: '2-digit',
                            month: '2-digit'
                        });
                    }),
                    datasets: [{
                        label: 'Lợi nhuận (VNĐ)',
                        data: data.map(d => d.profit),
                        borderColor: '#198754',
                        backgroundColor: 'rgba(25, 135, 84, 0.1)',
                        borderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6,
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
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false,
                            callbacks: {
                                label: function(context) {
                                    return context.parsed.y.toLocaleString('vi-VN') + ' đ';
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                borderDash: [2, 4]
                            },
                            ticks: {
                                callback: function(value) {
                                    return value.toLocaleString('vi-VN') + ' đ';
                                }
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
        });
    </script>
@endsection
