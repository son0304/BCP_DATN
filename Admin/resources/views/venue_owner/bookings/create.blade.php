@extends('app')

@section('content')
    <style>
        :root {
            --primary: #10b981;
            --primary-soft: #eff6ff;
            --emerald: #10b981;
            --emerald-soft: #ecfdf5;
            --momo: #d82d8b;
            --momo-soft: #fff1f7;
            --slate-50: #f8fafc;
            --slate-100: #f1f5f9;
            --slate-200: #e2e8f0;
            --slate-700: #334155;
            --slate-800: #1e293b;
            --card-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);
        }

        body { background-color: #f1f5f9; font-family: 'Inter', sans-serif; color: var(--slate-800); }

        /* Full-width Layout */
        .main-content { padding: 1.5rem 2rem; max-width: 100%; }

        /* Card Styling */
        .card { border-radius: 12px; border: 1px solid var(--slate-200); box-shadow: var(--card-shadow); transition: all 0.2s; }
        .card-header { background: #fff; border-bottom: 1px solid var(--slate-100); padding: 1.25rem 1.5rem; border-radius: 12px 12px 0 0 !important; }
        .card-header h5 { font-size: 1rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.025em; color: var(--slate-700); }

        /* Form Controls */
        .form-label { font-size: 0.75rem; font-weight: 700; color: var(--secondary); text-transform: uppercase; margin-bottom: 0.5rem; }
        .form-control, .form-select {
            border-radius: 8px; border: 1px solid var(--slate-200); padding: 0.6rem 0.8rem;
            font-size: 0.9rem; background-color: var(--slate-50);
        }
        .form-control:focus, .form-select:focus {
            background-color: #fff; border-color: var(--primary); box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        /* Select2 Override */
        .select2-container--bootstrap-5 .select2-selection {
            border-radius: 8px !important; height: 42px !important; border: 1px solid var(--slate-200) !important;
            background-color: var(--slate-50) !important;
        }
        .select2-container--bootstrap-5 .select2-selection__rendered { line-height: 42px !important; padding-left: 1rem !important; font-size: 0.9rem; }

        /* Payment Buttons */
        .payment-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem; }
        .payment-card {
            border: 2px solid var(--slate-100); border-radius: 10px; padding: 1rem; cursor: pointer;
            text-align: center; transition: all 0.2s ease; background: #fff;
        }
        .payment-card i { font-size: 1.25rem; margin-bottom: 0.5rem; display: block; }
        .payment-card span { font-size: 0.75rem; font-weight: 700; display: block; }

        .btn-check:checked + .payment-card[for="pay_cash"] { border-color: var(--primary); color: var(--primary); background: var(--primary-soft); }
        .btn-check:checked + .payment-card[for="pay_momo"] { border-color: var(--momo); color: var(--momo); background: var(--momo-soft); }

        /* Table */
        .table-custom { border-radius: 8px; overflow: hidden; border: 1px solid var(--slate-200); }
        .table-custom thead th { background: var(--slate-50); font-size: 0.7rem; color: var(--secondary); text-transform: uppercase; padding: 1rem; border: none; }
        .table-custom tbody td { padding: 1rem; vertical-align: middle; border-bottom: 1px solid var(--slate-100); background: #fff; }

        /* Receipt Sidebar */
        .billing-summary { background: var(--slate-50); border-radius: 10px; padding: 1.25rem; }
        .billing-item { display: flex; justify-content: space-between; margin-bottom: 0.75rem; font-size: 0.9rem; color: var(--slate-700); }
        .billing-total { display: flex; justify-content: space-between; align-items: center; margin-top: 1rem; padding-top: 1rem; border-top: 2px dashed var(--slate-200); }
        .total-label { font-weight: 800; color: var(--slate-800); }
        .total-value { font-size: 1.5rem; font-weight: 800; color: var(--primary); }

        /* Buttons */
        .btn-add-row { background: var(--emerald-soft); color: var(--emerald); border: none; font-weight: 700; font-size: 0.8rem; border-radius: 8px; transition: 0.2s; }
        .btn-add-row:hover { background: var(--emerald); color: #fff; }
        .btn-submit { background: var(--primary); color: #fff; border: none; border-radius: 10px; padding: 1rem; font-weight: 700; width: 100%; transition: 0.3s; }
        .btn-submit:hover { background: #10b981; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2); }
        .btn-submit:disabled { background: var(--slate-200); color: var(--secondary); transform: none; }

        /* Momo QR */
        #momo-area { border: 2px dashed var(--momo); border-radius: 12px; padding: 1.5rem; background: #fff; margin-top: 1rem; }
    </style>

    <div class="main-content container-fluid">
        <form action="{{ route('owner.bookings.store') }}" method="POST" id="booking-form">
            @csrf
            <div class="row g-4">
                {{-- KHỐI NHẬP LIỆU CHÍNH --}}
                <div class="col-xl-8 col-lg-7">
                    <div class="card h-100">
                        <div class="card-header d-flex align-items-center">
                            <i class="fas fa-plus-circle text-primary me-2"></i>
                            <h5 class="mb-0">Tạo đơn đặt sân mới</h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="row g-4 mb-4">
                                <div class="col-md-7">
                                    <label class="form-label">Thông tin khách hàng</label>
                                    <select class="form-select select2-customer" name="user_id" id="user_id" required>
                                        <option value="guest" selected>Khách vãng lai (Ghi chú tên/SĐT)</option>
                                        @foreach ($customers as $customer)
                                            <option value="{{ $customer->id }}">{{ $customer->name }} - {{ $customer->phone }}</option>
                                        @endforeach
                                    </select>

                                    <div id="guest-info-fields" class="mt-3 p-3 bg-light rounded-3 border">
                                        <div class="row g-2">
                                            <div class="col-6">
                                                <input type="text" name="guest_name" class="form-control" placeholder="Tên khách hàng...">
                                            </div>
                                            <div class="col-6">
                                                <input type="text" name="guest_phone" class="form-control" placeholder="Số điện thoại...">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label">Mã khuyến mãi (Coupon)</label>
                                    <select class="form-select" name="promotion_id" id="promotion_id" style="height: 42px;">
                                        <option value="">Không sử dụng mã</option>
                                        @foreach ($promotions as $p)
                                            <option value="{{ $p->id }}" data-type="{{ $p->type }}" data-value="{{ $p->value }}" data-max="{{ $p->max_discount_amount }}">{{ $p->code }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="table-custom">
                                <table class="table table-borderless mb-0">
                                    <thead>
                                        <tr>
                                            <th>Sân vận động</th>
                                            <th>Ngày đặt</th>
                                            <th>Khung giờ</th>
                                            <th class="text-end" style="width: 150px;">Đơn giá</th>
                                            <th style="width: 50px;"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="booking-items-container">
                                        {{-- Row generated by JS --}}
                                    </tbody>
                                </table>
                            </div>

                            <div class="mt-3">
                                <button type="button" id="add-item" class="btn btn-add-row py-2 px-4">
                                    <i class="fas fa-plus-circle me-2"></i>THÊM SÂN VÀO DANH SÁCH
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- KHỐI THANH TOÁN --}}
                <div class="col-xl-4 col-lg-5">
                    <div class="card">
                        <div class="card-header">
                            <i class="fas fa-file-invoice-dollar text-primary me-2"></i>
                            <h5 class="mb-0">Tổng kết thanh toán</h5>
                        </div>
                        <div class="card-body p-4">
                            <label class="form-label">Phương thức thanh toán</label>
                            <div class="payment-grid">
                                <div>
                                    <input type="radio" class="btn-check" name="payment_method" id="pay_cash" value="cash" checked>
                                    <label class="payment-card w-100" for="pay_cash">
                                        <i class="fas fa-money-bill-wave"></i>
                                        <span>TIỀN MẶT</span>
                                    </label>
                                </div>
                                <div>
                                    <input type="radio" class="btn-check" name="payment_method" id="pay_momo" value="momo">
                                    <label class="payment-card w-100" for="pay_momo">
                                        <i class="fas fa-qrcode"></i>
                                        <span>MOMO QR</span>
                                    </label>
                                </div>
                            </div>

                            <div class="billing-summary mb-4">
                                <div class="billing-item">
                                    <span>Tổng tiền sân (Tạm tính)</span>
                                    <span id="txt-subtotal" class="fw-bold text-dark">0đ</span>
                                </div>
                                <div class="billing-item">
                                    <span>Số tiền giảm giá</span>
                                    <span id="txt-discount" class="fw-bold text-danger">-0đ</span>
                                </div>
                                <div class="billing-total">
                                    <span class="total-label">TỔNG CỘNG</span>
                                    <span class="total-value" id="txt-total">0đ</span>
                                </div>
                            </div>

                            {{-- MOMO SECTION --}}
                            <div id="momo-area" class="text-center d-none">
                                <div id="qr-status-container" class="py-2">
                                    <div class="spinner-border text-pink spinner-border-sm" style="color: var(--momo)"></div>
                                    <p class="small text-muted mt-2 mb-0">Đang khởi tạo mã QR...</p>
                                </div>
                                <div id="qr-content" class="d-none">
                                    <img id="qr-image" src="" class="img-fluid border rounded shadow-sm mb-3" style="width: 150px;">
                                    <div class="alert alert-light border text-pink w-100 py-2" style="color: var(--momo)">
                                        <i class="fas fa-sync fa-spin me-2"></i> <span id="payment-status-text">Đang chờ quét...</span>
                                    </div>
                                </div>
                            </div>

                            <input type="hidden" name="subtotal" id="input-subtotal">
                            <input type="hidden" name="discount_amount" id="input-discount">
                            <input type="hidden" name="total_amount" id="input-total">
                            <input type="hidden" name="payment_status" id="input-payment-status" value="paid">
                            <input type="hidden" name="temp_order_id" id="input-temp-id">

                            <button type="submit" id="btn-submit" class="btn btn-submit shadow-sm mt-3">
                                <i class="fas fa-check-double me-2"></i>XÁC NHẬN & LƯU ĐƠN HÀNG
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        $(document).ready(function() {
            // Select2 Init
            $('.select2-customer').select2({
                theme: 'bootstrap-5',
                width: '100%'
            });

            // Logic giữ nguyên 100%
            $('#user_id').on('change', function() {
                if ($(this).val() === 'guest') $('#guest-info-fields').slideDown();
                else $('#guest-info-fields').slideUp();
            });

            let itemIndex = 0;
            let pollingInterval = null;
            let lastAmount = 0;

            $('input[name="payment_method"]').on('change', function() {
                const method = $(this).val();
                if (method === 'cash') {
                    $('#momo-area').addClass('d-none');
                    stopPolling();
                    $('#input-payment-status').val('paid');
                    $('#btn-submit').prop('disabled', false).html('<i class="fas fa-check-double me-2"></i>XÁC NHẬN & LƯU ĐƠN HÀNG');
                } else {
                    $('#momo-area').removeClass('d-none');
                    $('#input-payment-status').val('unpaid');
                    calculateTotal();
                }
            });

            function addItemRow() {
                const row = `<tr class="item-row">
                    <td>
                        <select name="bookings[${itemIndex}][court_id]" class="form-select court-select" required>
                            <option value="">-- Chọn sân --</option>
                            @foreach ($venuesJson as $venue)
                                <optgroup label="{{ $venue['name'] }}">
                                    @foreach ($venue['courts'] as $court)
                                        <option value="{{ $court['id'] }}">{{ $court['name'] }}</option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                    </td>
                    <td><input type="date" name="bookings[${itemIndex}][date]" class="form-control date-input" min="{{ date('Y-m-d') }}" required></td>
                    <td><select name="bookings[${itemIndex}][time_slot_id]" class="form-select slot-select" disabled required><option value="">-- Giờ --</option></select></td>
                    <td><input type="text" name="bookings[${itemIndex}][unit_price]" class="form-control text-end bg-light fw-bold price-input" readonly value="0"></td>
                    <td class="text-center">
                        <button type="button" class="btn btn-link text-danger btn-remove p-0" style="font-size: 1.2rem;">
                            <i class="fas fa-minus-circle"></i>
                        </button>
                    </td>
                </tr>`;
                $('#booking-items-container').append(row);
                itemIndex++;
            }

            $(document).on('change', '.court-select, .date-input', function() {
                const row = $(this).closest('.item-row');
                const courtId = row.find('.court-select').val();
                const date = row.find('.date-input').val();
                const slotSelect = row.find('.slot-select');
                if (courtId && date) {
                    slotSelect.prop('disabled', true).html('<option>...</option>');
                    $.get("{{ route('owner.availabilities.get-slots') }}", {
                        court_id: courtId,
                        date: date
                    }, function(res) {
                        let html = '<option value="">-- Chọn giờ --</option>';
                        res.data.forEach(s => {
                            if (s.status === 'open') html +=
                                `<option value="${s.time_slot_id}" data-price="${s.price}">${s.start_time.substring(0,5)} - ${s.end_time.substring(0,5)}</option>`;
                        });
                        slotSelect.html(html).prop('disabled', false);
                    });
                }
            });

            $(document).on('change', '.slot-select', function() {
                $(this).closest('.item-row').find('.price-input').val($(this).find(':selected').data('price') || 0);
                calculateTotal();
            });

            $(document).on('click', '.btn-remove', function() {
                $(this).closest('.item-row').remove();
                calculateTotal();
            });

            $('#add-item').click(addItemRow);

            function calculateTotal() {
                let subtotal = 0;
                $('.price-input').each(function() { subtotal += parseFloat($(this).val()) || 0; });
                const promo = $('#promotion_id option:selected');
                let discount = promo.val() ? (promo.data('type') === '%' ? (subtotal * promo.data('value') / 100) : promo.data('value')) : 0;
                if (promo.data('max') && discount > promo.data('max')) discount = promo.data('max');
                const total = Math.max(subtotal - discount, 0);

                $('#txt-subtotal').text(subtotal.toLocaleString() + 'đ');
                $('#txt-discount').text('-' + discount.toLocaleString() + 'đ');
                $('#txt-total').text(total.toLocaleString() + 'đ');
                $('#input-subtotal').val(subtotal);
                $('#input-discount').val(discount);
                $('#input-total').val(total);

                const currentMethod = $('input[name="payment_method"]:checked').val();
                if (currentMethod === 'momo' && total >= 1000) {
                    generateMomoQR(total);
                } else {
                    $('#momo-area').addClass('d-none');
                    stopPolling();
                }
            }

            function generateMomoQR(amount) {
                if (amount === lastAmount) return;
                lastAmount = amount;
                const tempId = "TEMP" + Date.now();
                $('#input-temp-id').val(tempId);
                $('#momo-area').removeClass('d-none');
                $('#qr-status-container').removeClass('d-none');
                $('#qr-content').addClass('d-none');
                $('#btn-submit').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>CHỜ THANH TOÁN MOMO...');

                $.post("{{ route('owner.bookings.generate-temp-qr') }}", {
                    _token: "{{ csrf_token() }}",
                    total_amount: amount,
                    temp_order_id: tempId
                }, function(res) {
                    if (res.success) {
                        $('#qr-image').attr('src', res.qr_code_url);
                        $('#qr-status-container').addClass('d-none');
                        $('#qr-content').removeClass('d-none');
                        startPolling(tempId);
                    }
                });
            }

            function startPolling(tempId) {
                stopPolling();
                pollingInterval = setInterval(() => {
                    $.get("{{ route('owner.bookings.check-temp-payment') }}", {
                        temp_order_id: tempId
                    }, function(res) {
                        if (res.paid) {
                            $('#payment-status-text').html('<b class="text-success">Thành công! Đang lưu đơn...</b>');
                            $('#input-payment-status').val('paid');
                            stopPolling();
                            setTimeout(() => $('#booking-form').submit(), 1000);
                        }
                    });
                }, 3000);
            }

            function stopPolling() { if (pollingInterval) clearInterval(pollingInterval); }

            addItemRow();
        });
    </script>
@endpush
