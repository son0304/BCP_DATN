@extends('app')

@section('content')
    <div class="container-fluid py-4 bg-light" style="min-height: 100vh;">

        <!-- HEADER & FILTER -->
        <div class="row mb-4 align-items-center">
            <div class="col-md-6">
                <h4 class="fw-bold text-dark mb-1">
                    <i class="fas fa-user-shield me-2 text-primary"></i>Tổng quan Tài chính Hệ thống
                </h4>
                <p class="text-muted small mb-0">Theo dõi lợi nhuận thực tế sau khi trừ chi phí khuyến mãi</p>
            </div>
            <div class="col-md-6 text-end">
                <form class="d-inline-flex gap-2 bg-white p-2 rounded-3 shadow-sm border">
                    <input type="date" name="date_from" class="form-control form-control-sm border-0 bg-light"
                        value="{{ $start->format('Y-m-d') }}">
                    <input type="date" name="date_to" class="form-control form-control-sm border-0 bg-light"
                        value="{{ $end->format('Y-m-d') }}">
                    <button class="btn btn-primary btn-sm px-3 fw-bold">Cập nhật</button>
                </form>
            </div>
        </div>

        <!-- 1. KPI CARDS -->
        <div class="row g-3 mb-4 text-nowrap">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm rounded-4 bg-primary text-white h-100">
                    <div class="card-body">
                        <small class="opacity-75 fw-bold text-uppercase">Số dư Bank thực tế</small>
                        <h3 class="fw-bold my-1">{{ number_format($actualBankBalance) }}đ</h3>
                        <p class="small mb-0 opacity-75">Tiền mặt tổng các cổng thanh toán</p>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card border-0 shadow-sm rounded-4 bg-dark text-white h-100">
                    <div class="card-body">
                        <small class="text-success fw-bold text-uppercase">Lợi nhuận ròng Admin</small>
                        <h3 class="fw-bold my-1 text-success">{{ number_format($adminWallet) }}đ</h3>
                        <p class="small mb-0 text-white-50">Đã trừ voucher adimin đã chi: <span
                                class="text-danger">-{{ number_format($finance->admin_voucher_cost ?? 0) }}đ</span></p>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card border-0 shadow-sm rounded-4 bg-white border-start border-4 border-danger h-100">
                    <div class="card-body">
                        <small class="text-danger fw-bold text-uppercase">Nghĩa vụ nợ (Ví Chủ sân)</small>
                        <h3 class="fw-bold my-1 text-dark">{{ number_format($totalOwnerLiability) }}đ</h3>
                        <p class="small mb-0 text-muted">Tổng tiền Venue có thể rút</p>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card border-0 shadow-sm rounded-4 bg-white h-100">
                    <div class="card-body py-2">
                        <div class="d-flex justify-content-between border-bottom py-1">
                            <span class="small text-muted">Tổng GMV:</span>
                            <span class="small fw-bold text-dark">{{ number_format($finance->gmv ?? 0) }}đ</span>
                        </div>
                        <div class="d-flex justify-content-between border-bottom py-1">
                            <span class="small text-muted">Dòng tiền vào:</span>
                            <span class="small text-primary fw-bold">+{{ number_format($cashIn) }}</span>
                        </div>
                        <div class="d-flex justify-content-between py-1">
                            <span class="small text-muted">Dòng tiền ra:</span>
                            <span class="small text-danger fw-bold">-{{ number_format($cashOut) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. CHART SECTION -->
        <div class="row g-4 mb-4">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-header bg-white py-3 border-0">
                        <h6 class="fw-bold mb-0 text-dark">Phân bổ lợi nhuận ròng hàng ngày</h6>
                    </div>
                    <div class="card-body">
                        <div style="height: 300px;"><canvas id="profitChart"></canvas></div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-header bg-white py-3 border-0">
                        <h6 class="fw-bold mb-0 text-dark">Tỷ trọng nguồn thu</h6>
                    </div>
                    <div class="card-body d-flex flex-column align-items-center justify-content-center">
                        <div style="height: 200px; width: 100%;"><canvas id="structureChart"></canvas></div>
                        <div class="mt-4 w-100">
                            <div class="d-flex justify-content-between small mb-2">
                                <span><i class="fas fa-ticket-alt text-success me-2"></i>Booking</span>
                                <span class="fw-bold">{{ number_format($finance->commission_revenue ?? 0) }}đ</span>
                            </div>
                            <div class="d-flex justify-content-between small">
                                <span><i class="fas fa-ad text-warning me-2"></i>Quảng cáo</span>
                                <span class="fw-bold">{{ number_format($finance->ads_revenue ?? 0) }}đ</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. SỔ CÁI DÒNG TIỀN -->
        <div class="card border-0 shadow-sm rounded-4 bg-white">
            <div class="card-header bg-white border-0 py-3 d-flex justify-content-between">
                <h6 class="fw-bold mb-0">Chi tiết phân bổ dòng tiền giao dịch</h6>
                <span class="badge bg-light text-dark fw-normal">{{ $recentTransactions->total() }} bản ghi</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 small">
                    <thead class="bg-light text-muted text-uppercase">
                        <tr>
                            <th class="ps-4">Giao dịch / Cơ sở</th>
                            <th class="text-center">Loại đơn</th>
                            <th class="text-end">Doanh số</th>
                            <th class="text-end text-danger">Voucher Admin</th>
                            <th class="text-end text-primary">Lợi nhuận ròng</th>
                            <th class="text-end">Ví sân nhận</th>
                            <th class="pe-4 text-end">Thời gian</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($recentTransactions as $t)
                            @php
                                $isAdminVoucher = false;
                                if ($t->promotion && $t->promotion->creator && $t->promotion->creator->role) {
                                    if (str_contains(strtolower($t->promotion->creator->role->name), 'admin')) {
                                        $isAdminVoucher = true;
                                    }
                                }
                            @endphp
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-bold text-dark">
                                        #{{ $t->money_flowable->booking_code ?? 'SPONSOR-' . $t->id }}</div>
                                    <div class="text-muted" style="font-size: 0.75rem;">{{ $t->venue->name ?? 'Hệ thống' }}
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span
                                        class="badge rounded-pill {{ str_contains($t->money_flowable_type, 'Ticket') ? 'bg-info-subtle text-info' : 'bg-warning-subtle text-warning' }} border px-2">
                                        {{ str_contains($t->money_flowable_type, 'Ticket') ? 'Vé sân' : 'Quảng cáo' }}
                                    </span>
                                </td>
                                <td class="text-end fw-bold">{{ number_format($t->total_amount) }}đ</td>

                                <td class="text-end">
                                    @if ($isAdminVoucher)
                                        <span
                                            class="text-danger fw-bold">-{{ number_format($t->promotion_amount) }}đ</span>
                                    @elseif($t->promotion_id)
                                        <span class="text-muted small" title="Do chủ sân tài trợ">0đ*</span>
                                    @else
                                        <span class="text-muted">0đ</span>
                                    @endif
                                </td>

                                <td class="text-end fw-bold text-primary">{{ number_format($t->admin_amount) }}đ</td>
                                <td class="text-end text-success">{{ number_format($t->venue_owner_amount) }}đ</td>
                                <td class="pe-4 text-end text-muted">
                                    {{ $t->created_at->format('d/m') }} <br>
                                    <small>{{ $t->created_at->format('H:i') }}</small>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-white border-0 py-3 d-flex justify-content-center">
                {{ $recentTransactions->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Biểu đồ cột chồng
            new Chart(document.getElementById('profitChart'), {
                type: 'bar',
                data: {
                    labels: @json($chartData->pluck('date')),
                    datasets: [{
                            label: 'Hoa hồng',
                            data: @json($chartData->pluck('commission')),
                            backgroundColor: '#0dcaf0'
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
                            stacked: true,
                            beginAtZero: true
                        }
                    }
                }
            });

            // Biểu đồ tròn
            new Chart(document.getElementById('structureChart'), {
                type: 'doughnut',
                data: {
                    labels: ['Booking', 'Ads'],
                    datasets: [{
                        data: [{{ $finance->commission_revenue }}, {{ $finance->ads_revenue }}],
                        backgroundColor: ['#0dcaf0', '#ffc107'],
                        borderWidth: 0
                    }]
                },
                options: {
                    cutout: '75%',
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
