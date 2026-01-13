@extends('app')

@section('content')
    <div class="container-fluid py-4 px-4">
        {{-- Header --}}
        <div class="row mb-4 align-items-center">
            <div class="col-md-6">
                <h4 class="fw-bold text-dark mb-1"><i class="fas fa-university me-2 text-primary"></i>Quản lý Rút tiền</h4>
                <p class="text-muted small mb-0">Hệ thống phê duyệt rút tiền tự động qua VietQR</p>
            </div>
        </div>

        {{-- Bảng danh sách --}}
        <div class="card shadow-sm border-0 overflow-hidden">
            <div class="table-responsive">
                <table class="table align-middle table-hover mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4 py-3 border-0">ID</th>
                            <th class="border-0">Người yêu cầu</th>
                            <th class="border-0 text-end">Số tiền rút</th>
                            <th class="border-0 text-end">Phí / Thực nhận</th>
                            <th class="border-0 text-center">Trạng thái</th>
                            <th class="border-0 text-end pe-4">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($withdrawalRequests as $req)
                            <tr>
                                <td class="ps-4 fw-bold text-muted">#{{ $req->id }}</td>
                                <td>
                                    <div class="fw-bold text-dark">{{ $req->user->name }}</div>
                                    <div class="small text-muted">{{ $req->user->phone }}</div>
                                </td>
                                <td class="text-end fw-bold text-dark">{{ number_format($req->amount) }}đ</td>
                                <td class="text-end">
                                    <div class="small text-muted">Phí: {{ number_format($req->fee) }}đ</div>
                                    <div class="fw-bold text-danger">{{ number_format($req->actual_amount) }}đ</div>
                                </td>
                                <td class="text-center">
                                    @php
                                        $st = match ($req->status) {
                                            'pending' => ['bg' => 'warning', 'txt' => 'Chờ duyệt'],
                                            'approved' => ['bg' => 'success', 'txt' => 'Thành công'],
                                            'rejected' => ['bg' => 'danger', 'txt' => 'Đã từ chối'],
                                            default => ['bg' => 'secondary', 'txt' => $req->status],
                                        };
                                    @endphp
                                    <span
                                        class="badge bg-{{ $st['bg'] }}-subtle text-{{ $st['bg'] }} px-3 rounded-pill border border-{{ $st['bg'] }}-subtle">
                                        {{ $st['txt'] }}
                                    </span>
                                </td>
                                <td class="text-end pe-4">
                                    <button type="button"
                                        class="btn btn-sm btn-outline-primary rounded-pill px-3 btn-view-detail"
                                        data-withdraw="{{ json_encode($req) }}">
                                        Xem chi tiết
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">Không có dữ liệu.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- MODAL CHI TIẾT --}}
    <div class="modal fade" id="withdrawModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold">Yêu cầu rút tiền #<span id="md-id"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body pt-0">
                    <div class="alert alert-secondary border-0 mb-3 d-flex justify-content-between align-items-center">
                        <span class="small">Số tiền thực tế cần chuyển:</span>
                        <strong class="fs-4 text-danger" id="md-actual-amount">0đ</strong>
                    </div>

                    <!-- Tabs -->
                    <ul class="nav nav-pills nav-justified mb-3 bg-light rounded p-1" id="withdrawTabs" role="tablist">
                        <li class="nav-item">
                            <button class="nav-link active rounded shadow-none" data-bs-toggle="tab"
                                data-bs-target="#tab-bank">Tài khoản</button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link rounded shadow-none" data-bs-toggle="tab" data-bs-target="#tab-qr">Mã
                                VietQR</button>
                        </li>
                    </ul>

                    <div class="tab-content border rounded p-3 mb-4">
                        <!-- Tab Thông tin -->
                        <div class="tab-pane fade show active" id="tab-bank">
                            <div class="mb-2">
                                <label class="text-muted small d-block">Ngân hàng</label>
                                <span class="fw-bold" id="md-bank-name">---</span>
                            </div>
                            <div class="mb-2">
                                <label class="text-muted small d-block">Số tài khoản</label>
                                <span class="fw-bold text-primary fs-5" id="md-bank-number">---</span>
                            </div>
                            <div>
                                <label class="text-muted small d-block">Chủ tài khoản</label>
                                <span class="fw-bold text-uppercase" id="md-bank-user">---</span>
                            </div>
                        </div>
                        <!-- Tab Mã QR -->
                        <div class="tab-pane fade text-center" id="tab-qr">
                            <img id="md-qr-img" src="" class="img-fluid rounded border shadow-sm"
                                style="max-height: 280px;">
                            <p class="x-small text-muted mt-2 mb-0">Dùng App Ngân hàng quét để thanh toán nhanh</p>
                        </div>
                    </div>

                    <div id="md-form-area">
                        <form id="updateForm" method="POST">
                            @csrf
                            <input type="hidden" name="status" id="md-input-status" value="approved">
                            <div id="box-approve" class="mb-3">
                                <label class="form-label small fw-bold">Mã giao dịch (Transaction Code)</label>
                                <input type="text" name="transaction_code" class="form-control"
                                    placeholder="Nhập mã FT... từ ngân hàng">
                            </div>
                            <div id="box-reject" class="mb-3 d-none">
                                <label class="form-label small fw-bold text-danger">Lý do từ chối</label>
                                <textarea name="admin_note" class="form-control" rows="2" placeholder="Ví dụ: Sai số tài khoản..."></textarea>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="button" id="btn-toggle-reject" class="btn btn-outline-danger w-50">Từ
                                    chối</button>
                                <button type="submit" id="btn-submit" class="btn btn-success w-50 fw-bold">Đã chuyển
                                    tiền</button>
                            </div>
                        </form>
                    </div>

                    <div id="md-result-area" class="d-none border-top pt-3 text-center">
                        <h6 class="fw-bold">Thông tin xử lý</h6>
                        <p class="mb-1 small">Mã GD: <span id="res-code">---</span></p>
                        <p class="small">Ghi chú: <span id="res-note">---</span></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .bg-warning-subtle {
            background-color: #fff3cd !important;
            color: #856404;
        }

        .bg-success-subtle {
            background-color: #d4edda !important;
            color: #155724;
        }

        .bg-danger-subtle {
            background-color: #f8d7da !important;
            color: #721c24;
        }

        .nav-pills .nav-link.active {
            background-color: #fff;
            color: #0d6efd;
            font-weight: bold;
        }

        .x-small {
            font-size: 11px;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const modalEl = document.getElementById('withdrawModal');
            const modal = new bootstrap.Modal(modalEl);
            const updateForm = document.getElementById('updateForm');

            // --- HÀM CHUẨN HÓA MÃ NGÂN HÀNG CHO VIETQR ---
            function getBankVietQRId(bankName) {
                const name = bankName.toLowerCase();
                if (name.includes('vietcombank') || name.includes('vcb')) return 'VCB';
                if (name.includes('vietinbank')) return 'ICB';
                if (name.includes('techcombank')) return 'TCB';
                if (name.includes('mbbank') || name.includes('mb')) return 'MB';
                if (name.includes('bidv')) return 'BIDV';
                if (name.includes('agribank')) return 'VBA';
                if (name.includes('acb')) return 'ACB';
                if (name.includes('tpbank')) return 'TPB';
                if (name.includes('vpbank')) return 'VPB';
                if (name.includes('sacombank')) return 'STB';
                if (name.includes('hdbank')) return 'HDB';
                if (name.includes('shb')) return 'SHB';
                if (name.includes('vib')) return 'VIB';
                // Nếu không khớp, trả về chính nó nhưng xóa khoảng trắng
                return bankName.replace(/\s/g, '').toUpperCase();
            }

            document.querySelectorAll('.btn-view-detail').forEach(btn => {
                btn.addEventListener('click', function() {
                    const data = JSON.parse(this.getAttribute('data-withdraw'));

                    document.getElementById('md-id').innerText = data.id;
                    document.getElementById('md-actual-amount').innerText = new Intl.NumberFormat(
                        'vi-VN').format(data.actual_amount) + 'đ';
                    document.getElementById('md-bank-name').innerText = data.bank_name;
                    document.getElementById('md-bank-number').innerText = data.bank_account_number;
                    document.getElementById('md-bank-user').innerText = data.bank_account_name;

                    // --- ĐOẠN FIX MÃ QR ---
                    const bankId = getBankVietQRId(data.bank_name);
                    const amount = data.actual_amount;
                    const content = encodeURIComponent(`Rut tien id ${data.id}`);

                    // Sử dụng template qr_only của VietQR để hiển thị tốt nhất
                    const qrUrl =
                        `https://img.vietqr.io/image/${bankId}-${data.bank_account_number}-compact.png?amount=${amount}&addInfo=${content}&accountName=${encodeURIComponent(data.bank_account_name)}`;

                    const qrImg = document.getElementById('md-qr-img');
                    qrImg.src = qrUrl;

                    // Xử lý ẩn hiện form
                    updateForm.action = `/admin/withdrawal-requests/${data.id}/process`;
                    if (data.status === 'pending') {
                        document.getElementById('md-form-area').classList.remove('d-none');
                        document.getElementById('md-result-area').classList.add('d-none');
                        resetFormState();
                    } else {
                        document.getElementById('md-form-area').classList.add('d-none');
                        document.getElementById('md-result-area').classList.remove('d-none');
                        document.getElementById('res-code').innerText = data.transaction_code ||
                            '---';
                        document.getElementById('res-note').innerText = data.admin_note || '---';
                    }

                    modal.show();
                });
            });

            // Toggle logic
            const btnToggle = document.getElementById('btn-toggle-reject');
            const boxApprove = document.getElementById('box-approve');
            const boxReject = document.getElementById('box-reject');
            const inputStatus = document.getElementById('md-input-status');
            const btnSubmit = document.getElementById('btn-submit');

            btnToggle.addEventListener('click', function() {
                if (inputStatus.value === 'approved') {
                    inputStatus.value = 'rejected';
                    boxApprove.classList.add('d-none');
                    boxReject.classList.remove('d-none');
                    btnSubmit.className = 'btn btn-danger w-50 fw-bold';
                    btnSubmit.innerText = 'Xác nhận Từ chối';
                    this.innerText = 'Quay lại';
                } else {
                    resetFormState();
                }
            });

            function resetFormState() {
                inputStatus.value = 'approved';
                boxApprove.classList.remove('d-none');
                boxReject.classList.add('d-none');
                btnSubmit.className = 'btn btn-success w-50 fw-bold';
                btnSubmit.innerText = 'Đã chuyển tiền';
                btnToggle.innerText = 'Từ chối';
                updateForm.reset();
            }
        });
    </script>
@endsection
