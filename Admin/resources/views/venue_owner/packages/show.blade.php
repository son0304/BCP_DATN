@extends('app')

@section('content')
    <style>
        :root {
            --primary-color: #1abc9c;
            /* Màu xanh chủ đạo */
            --primary-light: #e0f2f1;
            --momo-color: #d82d8b;
        }

        /* Card Sân */
        .venue-card {
            display: flex;
            align-items: center;
            padding: 12px 15px;
            background: #fff;
            border: 1px solid #e0e0e0;
            border-radius: 10px;
            margin-bottom: 10px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .venue-card:hover {
            border-color: var(--primary-color);
            background-color: #f9fcfb;
        }

        /* Trạng thái được chọn */
        .venue-card.active {
            border-color: var(--primary-color);
            background-color: var(--primary-light);
            box-shadow: 0 2px 4px rgba(26, 188, 156, 0.1);
        }

        .venue-card.active .fw-bold {
            color: #0e7862;
        }

        /* Checkbox */
        .custom-checkbox {
            width: 20px;
            height: 20px;
            cursor: pointer;
            border: 2px solid #ccc;
            border-radius: 4px;
            accent-color: var(--primary-color);
        }

        /* Phương thức thanh toán */
        .payment-method-label {
            border: 1px solid #e0e0e0;
            border-radius: 10px;
            padding: 15px;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #fff;
        }

        .payment-method-input:checked+.payment-method-label {
            border-color: var(--primary-color);
            border-width: 1px;
            box-shadow: 0 0 0 1px var(--primary-color);
        }

        .check-icon {
            display: none;
            color: var(--primary-color);
        }

        .payment-method-input:checked+.payment-method-label .check-icon {
            display: block;
        }

        /* Box Tổng tiền */
        .total-box {
            background-color: var(--primary-color);
            color: white;
            border-radius: 10px;
            padding: 20px;
        }

        /* Nút Submit Mặc định (Ví) */
        .btn-submit-custom {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            color: white;
            font-weight: bold;
            padding: 12px;
            border-radius: 8px;
            width: 100%;
            transition: 0.3s;
        }

        .btn-submit-custom:hover:not(:disabled) {
            background-color: #16a085;
            color: white;
        }

        /* Nút Submit Momo (Màu hồng) */
        .btn-momo-custom {
            background-color: var(--momo-color) !important;
            border-color: var(--momo-color) !important;
            color: white !important;
        }

        .btn-momo-custom:hover:not(:disabled) {
            background-color: #c21f7a !important;
        }

        .btn-submit-custom:disabled {
            background-color: #95a5a6;
            border-color: #95a5a6;
            cursor: not-allowed;
        }
    </style>

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card shadow-sm border-0 rounded-3">
                    <!-- Header -->
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start mb-4 border-bottom pb-3">
                            <div>
                                <h5 class="fw-bold text-dark mb-1">{{ $package->name }}</h5>
                                <small class="text-muted"><i class="far fa-clock me-1"></i>Thời hạn:
                                    {{ $package->duration_days }} ngày</small>
                            </div>
                            <div class="text-end">
                                <small class="text-muted d-block" style="font-size: 0.8rem">Đơn giá/sân</small>
                                <span class="text-success fw-bold fs-5"
                                    style="color: var(--primary-color) !important">{{ number_format($package->price) }}đ</span>
                                <input type="hidden" id="unit-price" value="{{ $package->price }}">
                            </div>
                        </div>

                        <form id="purchase-form" action="{{ route('owner.packages.store') }}" method="POST">
                            @csrf
                            <input type="hidden" name="package_id" value="{{ $package->id }}">
                            <input type="hidden" name="payment_method" id="input_payment_method" value="wallet">
                            <input type="hidden" name="momo_trans_id" id="input_momo_trans_id" value="">

                            <!-- 1. CHỌN SÂN -->
                            <div class="mb-4">
                                <label class="fw-bold small text-uppercase text-muted mb-2">1. Chọn sân áp dụng</label>

                                <div class="form-check mb-2 ml-3 ps-1">
                                    <input class="form-check-input custom-checkbox" type="checkbox" id="check-all">
                                    <label class="form-check-label fw-bold small ms-2 pt-1" for="check-all">Chọn tất
                                        cả</label>
                                </div>

                                <div style="max-height: 300px; overflow-y: auto;" class="pe-1 ">
                                    @forelse ($venues as $venue)
                                        <div class="venue-card px-4" id="card-{{ $venue->id }}"
                                            onclick="toggleItem({{ $venue->id }})">
                                            <input class="form-check-input  custom-checkbox venue-cb" type="checkbox"
                                                name="venue_ids[]" value="{{ $venue->id }}"
                                                id="venue_{{ $venue->id }}"
                                                onclick="event.stopPropagation(); updateUI();">
                                            <div class="ms-3 lh-1">
                                                <div class="fw-bold text-dark">{{ $venue->name }}</div>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="text-center py-3 text-muted small border rounded bg-light">
                                            Bạn chưa có sân nào để áp dụng.
                                        </div>
                                    @endforelse
                                </div>
                            </div>

                            <!-- 2. THANH TOÁN -->
                            <div class="mb-4">
                                <label class="fw-bold small text-uppercase text-muted mb-2">2. Thanh toán qua</label>

                                <!-- Ví -->
                                <div class="mb-2">
                                    <input type="radio" name="payment_opt" id="pay_wallet"
                                        class="d-none payment-method-input" value="wallet" checked
                                        onchange="changePaymentMethod('wallet')">
                                    <label for="pay_wallet" class="payment-method-label">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-wallet fa-lg me-3 text-secondary"></i>
                                            <div>
                                                <div class="fw-bold">Ví tài khoản</div>
                                                <small class="text-muted">Số dư:
                                                    {{ number_format(auth()->user()->wallet->balance ?? 0) }}đ</small>
                                            </div>
                                        </div>
                                        <i class="fas fa-check-circle fa-lg check-icon"></i>
                                    </label>
                                </div>

                                <!-- Momo -->
                                <div>
                                    <input type="radio" name="payment_opt" id="pay_momo"
                                        class="d-none payment-method-input" value="momo"
                                        onchange="changePaymentMethod('momo')">
                                    <label for="pay_momo" class="payment-method-label">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-qrcode"></i>
                                            <span class="fw-bold ml-2" style="color: #000">Ví Momo (Quét QR)</span>
                                        </div>
                                        <i class="fas fa-qrcode fa-lg check-icon"></i>
                                    </label>
                                </div>
                            </div>

                            <!-- 3. TỔNG TIỀN -->
                            <div class="total-box mb-3 d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="d-block small opacity-75">Đã chọn: <strong id="qty-text">0</strong>
                                        sân</span>
                                </div>
                                <div class="text-end">
                                    <small class="text-uppercase fw-bold opacity-75" style="font-size: 0.7rem">Tổng thanh
                                        toán</small>
                                    <h4 class="fw-bold mb-0" id="total-text">0đ</h4>
                                </div>
                            </div>

                            <button type="submit" id="btn-submit" class="btn btn-submit-custom" disabled>
                                Vui lòng chọn sân
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL QR MOMO -->
    <div class="modal fade" id="momoModal" data-bs-backdrop="static" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content rounded-4 text-center">
                <div class="modal-header border-0 pb-0">
                    <h6 class="modal-title fw-bold w-100">Quét mã MoMo</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="qr-loader" class="py-4">
                        <div class="spinner-border text-pink" style="color: var(--momo-color)" role="status"></div>
                        <p class="small text-muted mt-2 mb-0">Đang tạo mã QR...</p>
                    </div>
                    <div id="qr-content" class="d-none">
                        <img id="momo-qr-img" src="" class="img-fluid mb-3 rounded border shadow-sm"
                            style="width: 200px;">
                        <h5 class="fw-bold mb-1" style="color: var(--momo-color)" id="momo-amount"></h5>
                        <p class="small text-muted mb-3 text-truncate px-2" id="momo-content"></p>
                        <div
                            class="alert alert-warning small py-2 mb-0 border-0 bg-warning bg-opacity-10 text-warning fw-bold">
                            <i class="fas fa-sync fa-spin me-1"></i> Đang chờ xác nhận...
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const form = document.getElementById('purchase-form');
        let currentMethod = 'wallet';
        let currentTotal = 0;
        let tempOrderId = null;
        let pollingInterval = null;

        // --- 1. XỬ LÝ GIAO DIỆN ---
        function toggleItem(id) {
            const cb = document.getElementById('venue_' + id);
            cb.checked = !cb.checked;
            updateUI();
        }

        function changePaymentMethod(method) {
            currentMethod = method;
            document.getElementById('input_payment_method').value = method;
            updateUI(); // Cập nhật màu nút
        }

        function updateUI() {
            const unitPrice = parseFloat(document.getElementById('unit-price').value);
            const checkboxes = document.querySelectorAll('.venue-cb');
            const checkAll = document.getElementById('check-all');
            let count = 0;

            checkboxes.forEach(cb => {
                const card = document.getElementById('card-' + cb.value);
                if (cb.checked) {
                    count++;
                    card.classList.add('active');
                } else {
                    card.classList.remove('active');
                }
            });

            if (checkAll) checkAll.checked = (count > 0 && count === checkboxes.length);

            currentTotal = count * unitPrice;
            document.getElementById('qty-text').innerText = count;
            document.getElementById('total-text').innerText = new Intl.NumberFormat('vi-VN').format(currentTotal) + 'đ';

            updateSubmitButton(count);
        }

        function updateSubmitButton(count) {
            const btn = document.getElementById('btn-submit');

            if (count > 0) {
                btn.removeAttribute('disabled');

                if (currentMethod === 'wallet') {
                    btn.classList.remove('btn-momo-custom');
                    btn.innerHTML = 'Thanh toán ngay';
                } else {
                    btn.classList.add('btn-momo-custom');
                    btn.innerHTML = '<i class="fas fa-qrcode me-2"></i> Lấy mã QR Momo';
                }
            } else {
                btn.setAttribute('disabled', 'disabled');
                btn.classList.remove('btn-momo-custom');
                btn.innerHTML = 'Vui lòng chọn sân';
            }
        }

        document.getElementById('check-all')?.addEventListener('change', function() {
            document.querySelectorAll('.venue-cb').forEach(cb => cb.checked = this.checked);
            updateUI();
        });


        // --- 2. XỬ LÝ THANH TOÁN (Ajax) ---
        form.addEventListener('submit', function(e) {
            if (currentMethod === 'momo') {
                e.preventDefault();
                initMomoPayment();
            }
            // Nếu là wallet thì form tự submit bình thường
        });

        function initMomoPayment() {
            tempOrderId = 'PKG_' + Date.now(); // Tạo mã tạm

            // Hiện Modal
            const momoModal = new bootstrap.Modal(document.getElementById('momoModal'));
            document.getElementById('qr-loader').classList.remove('d-none');
            document.getElementById('qr-content').classList.add('d-none');
            momoModal.show();

            // GỌI API TẠO QR (Route đã sửa đúng)
            fetch("{{ route('payment.momo.temp-qr') }}", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": "{{ csrf_token() }}"
                    },
                    body: JSON.stringify({
                        total_amount: currentTotal,
                        temp_order_id: tempOrderId
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('qr-loader').classList.add('d-none');
                        document.getElementById('qr-content').classList.remove('d-none');
                        document.getElementById('momo-qr-img').src = data.qr_code_url;
                        document.getElementById('momo-amount').innerText = new Intl.NumberFormat('vi-VN').format(
                            currentTotal) + 'đ';
                        document.getElementById('momo-content').innerText = "ND: " + tempOrderId;

                        // Bắt đầu check trạng thái
                        startPolling();
                    } else {
                        alert(data.message || "Lỗi tạo mã QR");
                        momoModal.hide();
                    }
                })
                .catch(err => {
                    console.error(err);
                    alert("Lỗi kết nối Server");
                    momoModal.hide();
                });
        }

        function startPolling() {
            if (pollingInterval) clearInterval(pollingInterval);

            pollingInterval = setInterval(() => {
                // GỌI API CHECK STATUS
                fetch("{{ route('payment.momo.check-status') }}?temp_order_id=" + tempOrderId)
                    .then(res => res.json())
                    .then(data => {
                        if (data.paid) {
                            clearInterval(pollingInterval);

                            document.getElementById('qr-content').innerHTML = `
                            <div class="py-4 text-success">
                                <i class="fas fa-check-circle fa-4x mb-3"></i>
                                <h5 class="fw-bold">Thanh toán thành công!</h5>
                                <p class="mb-0">Đang kích hoạt gói dịch vụ...</p>
                            </div>
                        `;

                            // Gán trans_id và submit form chính
                            document.getElementById('input_momo_trans_id').value = tempOrderId;
                            setTimeout(() => {
                                form.submit();
                            }, 1500);
                        }
                    });
            }, 2500); // Check mỗi 2.5s
        }

        document.getElementById('momoModal').addEventListener('hidden.bs.modal', function() {
            if (pollingInterval) clearInterval(pollingInterval);
        });
    </script>
@endsection
