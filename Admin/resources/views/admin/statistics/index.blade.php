@extends('app')

@section('content')
    <div class="container-fluid py-4 bg-light" style="min-height: 100vh;">

        <!-- HEADER & FILTER -->
        <div class="row mb-4 align-items-center">
            <div class="col-md-6">
                <h4 class="fw-bold text-dark mb-1">
                    <i class="fas fa-university me-2 text-primary"></i>Quản trị Tài chính Admin
                </h4>
                <p class="text-muted small mb-0">Đối soát Dòng tiền Bank, Lợi nhuận và Nghĩa vụ nợ</p>
            </div>
            <div class="col-md-6 text-end">
                <form class="d-inline-flex gap-2 bg-white p-2 rounded-3 shadow-sm border">
                    <input type="date" name="date_from" class="form-control form-control-sm border-0 bg-light"
                        value="{{ $start->format('Y-m-d') }}">
                    <input type="date" name="date_to" class="form-control form-control-sm border-0 bg-light"
                        value="{{ $end->format('Y-m-d') }}">
                    <button class="btn btn-primary btn-sm px-3 fw-bold">Lọc</button>
                </form>
            </div>
        </div>

        <!-- 1. KPI CARDS - BỐ CỤC ĐỐI SOÁT -->
        <div class="row g-3 mb-4">
            <!-- TIỀN MẶT TRONG BANK -->
            <div class="col-md-3">
                <div class="card border-0 shadow-sm rounded-4 bg-primary text-white h-100">
                    <div class="card-body">
                        <small class="opacity-75 fw-bold text-uppercase">Số dư Bank thực tế</small>
                        <h3 class="fw-bold my-1">{{ number_format($actualBankBalance) }}đ</h3>
                        <p class="small mb-0 opacity-75">Tiền mặt hiện có trong ví MoMo/VNPAY</p>
                    </div>
                </div>
            </div>

            <!-- LỢI NHUẬN RÒNG -->
            <div class="col-md-3">
                <div class="card border-0 shadow-sm rounded-4 bg-dark text-white h-100">
                    <div class="card-body">
                        <small class="text-success fw-bold text-uppercase">Lợi nhuận của bạn (Ví)</small>
                        <h3 class="fw-bold my-1 text-success">{{ number_format($adminWallet) }}đ</h3>
                        <p class="small mb-0 opacity-50">Sau khi trừ Voucher:
                            {{ number_format($finance->admin_voucher_cost ?? 0) }}đ</p>
                    </div>
                </div>
            </div>

            <!-- NỢ VENUE -->
            <div class="col-md-3">
                <div class="card border-0 shadow-sm rounded-4 bg-white border-start border-4 border-danger h-100">
                    <div class="card-body">
                        <small class="text-danger fw-bold text-uppercase">Đang giữ hộ Venue (Nợ)</small>
                        <h3 class="fw-bold my-1">{{ number_format($totalOwnerLiability) }}đ</h3>
                        <p class="small mb-0 text-muted">Số dư ví của tất cả các chủ sân</p>
                    </div>
                </div>
            </div>

            <!-- THU CHI TRONG KỲ -->
            <div class="col-md-3">
                <div class="card border-0 shadow-sm rounded-4 bg-white h-100">
                    <div class="card-body py-2">
                        <div class="d-flex justify-content-between border-bottom py-1">
                            <span class="small text-muted">Thu (In):</span>
                            <span class="small text-primary fw-bold">+{{ number_format($cashIn) }}</span>
                        </div>
                        <div class="d-flex justify-content-between border-bottom py-1">
                            <span class="small text-muted">Chi (Out):</span>
                            <span class="small text-danger fw-bold">-{{ number_format($cashOut) }}</span>
                        </div>
                        <div class="d-flex justify-content-between py-1">
                            <span class="small text-muted">GMV:</span>
                            <span class="small fw-bold text-dark">{{ number_format($finance->gmv ?? 0) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- 2. BIỂU ĐỒ DOANH THU THEO LOẠI -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-header bg-white py-3 border-0">
                        <h6 class="fw-bold mb-0 text-dark">Phân tích nguồn thu: Booking vs Quảng cáo</h6>
                    </div>
                    <div class="card-body">
                        <div style="height: 300px;">
                            <canvas id="profitChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 3. CƠ CẤU NGUỒN THU & TOP PARTNER -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-body">
                        <h6 class="fw-bold mb-3 small text-uppercase text-muted text-center">Tỷ trọng nguồn thu Admin</h6>
                        <div style="height: 180px;">
                            <canvas id="structureChart"></canvas>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-header bg-white py-3 border-0">
                        <h6 class="fw-bold mb-0 small text-uppercase text-primary">Top Sân Đóng Góp Hoa Hồng</h6>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 small text-nowrap">
                            <tbody>
                                @foreach ($topVenues as $v)
                                    <tr>
                                        <td class="ps-3 border-0 fw-bold">{{ $v->name }}</td>
                                        <td class="text-end pe-3 border-0 text-success fw-bold">
                                            +{{ number_format($v->total_commission) }}đ
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- 4. SỔ CÁI CHI TIẾT -->
        <div class="card border-0 shadow-sm rounded-4 bg-white mt-4">
            <div class="card-header bg-white border-0 fw-bold py-3">Chi tiết phân phối dòng tiền (Money Flow)</div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 small">
                    <thead class="bg-light text-muted text-uppercase">
                        <tr>
                            <th class="ps-4">Nguồn tiền</th>
                            <th class="text-end">Doanh số</th>
                            <th class="text-end text-danger">Voucher Admin</th>
                            <th class="text-end text-primary">Lợi nhuận ròng</th>
                            <th class="text-end">Ví sân nhận</th>
                            <th class="pe-4 text-end">Thời gian</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($recentTransactions as $t)
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-bold text-dark">{{ $t->money_flowable->booking_code ?? 'Tài trợ' }}
                                    </div>
                                    <div class="text-muted small">{{ $t->venue->name ?? 'Hệ thống' }}</div>
                                </td>
                                <td class="text-end fw-bold">{{ number_format($t->total_amount) }}đ</td>
                                <td class="text-end text-danger">-{{ number_format($t->promotion_amount) }}đ</td>
                                <td class="text-end fw-bold text-primary bg-light">{{ number_format($t->admin_amount) }}đ
                                </td>
                                <td class="text-end text-success">{{ number_format($t->venue_owner_amount) }}đ</td>
                                <td class="pe-4 text-end text-muted">{{ $t->created_at->format('d/m H:i') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-white border-0 py-3">{{ $recentTransactions->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // 1. BIỂU ĐỒ CỘT CHỒNG (STACKED BAR)
            new Chart(document.getElementById('profitChart'), {
                type: 'bar',
                data: {
                    labels: @json($chartData->pluck('date')),
                    datasets: [{
                            label: 'Hoa hồng Booking',
                            data: @json($chartData->pluck('commission')),
                            backgroundColor: '#198754'
                        },
                        {
                            label: 'Quảng cáo',
                            data: @json($chartData->pluck('ads')),
                            backgroundColor: '#ffc107'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: {
                            stacked: true
                        },
                        y: {
                            stacked: true
                        }
                    }
                }
            });

            // 2. BIỂU ĐỒ TRÒN
            new Chart(document.getElementById('structureChart'), {
                type: 'doughnut',
                data: {
                    labels: ['Booking', 'Quảng cáo'],
                    datasets: [{
                        data: [{{ $finance->commission_revenue ?? 0 }},
                            {{ $finance->ads_revenue ?? 0 }}
                        ],
                        backgroundColor: ['#198754', '#ffc107'],
                        borderWidth: 0
                    }]
                },
                options: {
                    cutout: '70%',
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        });
    </script>
@endsection
