@extends('app')

@section('content')

<div class="container-fluid mt-4">

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white border-0 py-3">
            <h3 class="mb-1">Tạo Đơn Đặt Sân Mới</h3>
            <div class="text-muted small">
                Tạo Booking cho khách hàng.
            </div>
        </div>

        <div class="card-body">
            @if ($errors->any())
            <div class="alert alert-danger">
                <strong>Có lỗi xảy ra!</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            @if (session('error'))
            <div class="alert alert-warning">
                <strong>Cảnh báo:</strong> {{ session('error') }}
            </div>
            @endif

            <form action="{{ route('owner.bookings.store') }}" method="POST" id="booking-form">
                @csrf

                <fieldset class="mb-4">
                    <legend class="h6 text-primary">1. Thông tin Khách hàng & Khuyến mãi</legend>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="user_id" class="form-label">Người tạo đơn <span class="text-danger">*</span></label>
                            <div class="form-control bg-light" style="cursor: not-allowed; opacity: 0.7;">
                                {{ $ownerName }}
                            </div>
                            <input type="hidden" name="user_id" value="{{ $currentUserId }}">
                            @error('user_id') <div class="text-danger small">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="promotion_id" class="form-label">Mã Khuyến mãi</label>

                            <select class="form-select" id="promotion_id" name="promotion_id">
                                <option value="" selected>-- Không áp dụng --</option>

                                @foreach ($promotions as $promotion)
                                <option
                                    value="{{ $promotion->id }}"
                                    data-type="{{ $promotion->type }}"
                                    data-value="{{ $promotion->value }}"
                                    data-max="{{ $promotion->max_discount_amount }}"
                                    {{ old('promotion_id') == $promotion->id ? 'selected' : '' }}>
                                    {{ $promotion->code }} -
                                    @if($promotion->type == '%')
                                    {{ number_format($promotion->value, 0) }}%
                                    @if($promotion->max_discount_amount)
                                    (Tối đa {{ number_format($promotion->max_discount_amount, 0) }}₫)
                                    @endif
                                    @else
                                    {{ number_format($promotion->value, 0) }}₫
                                    @endif
                                </option>
                                @endforeach
                            </select>

                            <input type="hidden" name="max_discount_amount" id="max_discount_amount" value="{{ old('max_discount_amount', 0) }}">
                        </div>
                    </div>
                </fieldset>

                <hr>

                <fieldset class="mb-4">
                    <legend class="h6 text-primary">2. Chi tiết Đặt Sân</legend>
                    <p class="text-muted small">
                        Chọn sân, ngày và khung giờ muốn đặt. **Bạn phải thêm ít nhất một mục đặt sân.**
                    </p>

                    <div class="table-responsive bg-light rounded border p-2">
                        <table class="table table-bordered align-middle mb-0">
                            <thead class="table-light small text-muted fw-bold">
                                <tr>
                                    <th style="width: 25%">Sân <span class="text-danger">*</span></th>
                                    <th style="width: 20%">Ngày <span class="text-danger">*</span></th>
                                    <th style="width: 20%">Khung giờ <span class="text-danger">*</span></th>
                                    <th style="width: 20%">Giá (VNĐ) <span class="text-danger">*</span></th>
                                    <th style="width: 15%" class="text-center">Hành động</th>
                                </tr>
                            </thead>
                            <tbody id="booking-items-container">
                                {{-- JS sẽ append các dòng vào đây --}}
                            </tbody>
                        </table>
                    </div>

                    <button type="button" id="add-booking-item-btn" class="btn btn-outline-primary mt-3">
                        <i class="fas fa-plus me-1"></i> Thêm mục đặt sân
                    </button>
                </fieldset>

                <hr>

                <div class="row">
                    <!-- ✅ CỘT TRÁI: Tổng kết thanh toán -->
                    <div class="col-md-6">
                        <fieldset class="mb-4">
                            <legend class="h6 text-primary">3. Tổng kết Thanh toán</legend>
                            <table class="table table-sm table-borderless small">
                                <tr>
                                    <td class="fw-semibold">Tổng phụ:</td>
                                    <td class="text-end" id="subtotal_display">{{ number_format(old('subtotal', 0)) }} VNĐ</td>
                                    <input type="hidden" name="subtotal" id="subtotal_input" value="{{ old('subtotal', 0) }}">
                                </tr>
                                <tr>
                                    <td class="fw-semibold text-danger">Giảm giá:</td>
                                    <td class="text-end text-danger" id="discount_display">- {{ number_format(old('discount_amount', 0)) }} VNĐ</td>
                                    <input type="hidden" name="discount_amount" id="discount_amount_input" value="{{ old('discount_amount', 0) }}">
                                </tr>
                                <tr class="fw-bold fs-5">
                                    <td class="text-primary">TỔNG TIỀN:</td>
                                    <td class="text-end text-primary" id="total_display">{{ number_format(old('total_amount', 0)) }} VNĐ</td>
                                    <input type="hidden" name="total_amount" id="total_amount" value="{{ old('total_amount', 0) }}">
                                </tr>
                            </table>
                        </fieldset>
                    </div>

                    <div class="col-md-6">
                        <fieldset class="mb-4">
                            <legend class="h6 text-primary">
                                4. Thanh toán MoMo (Tùy chọn)
                                <span class="badge bg-success">QR Code</span>
                            </legend>

                            <div id="qr-container" class="text-center border rounded p-3 bg-light" style="min-height: 200px; display: none;">
                                <div id="qr-loading" class="d-none">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                    <p class="mt-2 text-muted small">Đang tạo mã QR...</p>
                                </div>

                                <div id="qr-content" class="d-none">
                                    <img id="qr-image" src="" alt="MoMo QR Code" class="img-fluid mb-2" style="max-width: 250px;">
                                    <p class="mb-1 fw-bold text-success">
                                        <i class="fas fa-qrcode me-1"></i> Quét mã để thanh toán
                                    </p>
                                    <p class="small text-muted mb-2">
                                        Số tiền: <span id="qr-amount" class="fw-bold text-dark">0</span> VNĐ
                                    </p>
                                    <div id="payment-status" class="alert alert-warning small mb-0">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Chưa thanh toán. Quét mã QR để hoàn tất.
                                    </div>
                                </div>

                                <div id="qr-error" class="d-none text-danger">
                                    <i class="fas fa-exclamation-triangle me-1"></i>
                                    <p class="mb-0 small">Không thể tạo QR code</p>
                                </div>
                            </div>

                            <input type="hidden" name="payment_status" id="payment_status_input" value="unpaid">
                            <input type="hidden" name="temp_order_id" id="temp_order_id_input" value="">

                            <div id="qr-guide" class="alert alert-info small mt-2 mb-0 d-none">
                                <i class="fas fa-info-circle me-1"></i>
                                <strong>Hướng dẫn:</strong>
                                <ul class="mb-0 mt-1" style="font-size: 0.85rem;">
                                    <li>Nếu khách hàng <strong>quét QR và thanh toán</strong> → Đơn sẽ tự động chuyển sang "Đã thanh toán"</li>
                                    <li>Nếu khách hàng <strong>thanh toán sau</strong> → Đơn sẽ lưu với trạng thái "Chưa thanh toán"</li>
                                </ul>
                            </div>
                        </fieldset>
                    </div>
                </div>

                <hr>

                <div class="card-footer bg-white text-end border-0 px-0 pt-4">
                    <a href="{{ route('owner.bookings.index') }}" class="btn btn-secondary">Hủy bỏ</a>
                    <button type="submit" class="btn btn-primary" id="submit-btn">
                        <i class="fas fa-save me-1"></i> Tạo đơn
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    (function() {
        if (typeof $ === "undefined") {
            console.error("⚠ jQuery chưa được load!");
            return;
        }

        const container = $("#booking-items-container");
        let itemIndex = 0;
        let currentTempOrderId = null;
        let paymentCheckInterval = null;
        let checkCount = 0;
        const maxChecks = 100;

        const venuesData = @json($venuesJson);

        // --------------------------
        // Tạo option sân
        // --------------------------
        function buildCourtOptions() {
            let html = "";
            venuesData.forEach(v => {
                html += `<optgroup label="${v.name}">`;
                v.courts.forEach(c => {
                    html += `<option value="${c.id}">${c.name}</option>`;
                });
                html += `</optgroup>`;
            });
            return html;
        }

        // --------------------------
        // Tạo dòng đặt sân
        // --------------------------
        function createBookingItemRow() {
            const row = `
        <tr class="booking-item-row" data-index="${itemIndex}">
            <td>
                <select class="form-select form-select-sm court-select" 
                        name="bookings[${itemIndex}][court_id]" required>
                    <option value="" selected disabled>Chọn sân</option>
                    ${buildCourtOptions()}
                </select>
            </td>
            <td>
                <input type="date" class="form-control form-control-sm date-input"
                        name="bookings[${itemIndex}][date]"
                        min="{{ now()->format('Y-m-d') }}" required>
            </td>
            <td>
                <select class="form-select form-select-sm timeslot-select"
                        name="bookings[${itemIndex}][time_slot_id]"
                        required disabled>
                    <option value="" selected disabled>Chọn giờ</option>
                </select>
            </td>
            <td>
                <input type="text" class="form-control form-control-sm price-input"
                        name="bookings[${itemIndex}][unit_price]"
                        readonly required>
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-outline-danger remove-item-btn">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        </tr>
        `;
            itemIndex++;
            return row;
        }

        // --------------------------
        // Thêm dòng
        // --------------------------
        $("#add-booking-item-btn").on("click", function(e) {
            e.preventDefault();
            container.append(createBookingItemRow());
        });

        // --------------------------
        // Xóa dòng
        // --------------------------
        $(document).on("click", ".remove-item-btn", function(e) {
            e.preventDefault();
            $(this).closest(".booking-item-row").remove();
            calculateTotal();
        });

        // --------------------------
        // Load time slots
        // --------------------------
        function loadAvailableSlots($row) {
            const courtId = $row.find(".court-select").val();
            const date = $row.find(".date-input").val();
            const $slot = $row.find(".timeslot-select");
            const $price = $row.find(".price-input");

            $slot.prop("disabled", true).html(`<option>Đang tải...</option>`);
            $price.val("");

            if (!courtId || !date) {
                $slot.html(`<option selected disabled>Chọn sân & ngày</option>`);
                return;
            }

            $.ajax({
                url: "{{ route('owner.availabilities.get-slots') }}",
                method: "GET",
                data: {
                    court_id: courtId,
                    date: date
                },
                success: function(res) {
                    if (!res?.data) {
                        $slot.html(`<option disabled>Lỗi dữ liệu</option>`);
                        return;
                    }

                    $slot.empty().append(`<option selected disabled>Chọn giờ</option>`);

                    if (res.data.length === 0) {
                        $slot.append(`<option disabled>Không có khung giờ khả dụng</option>`);
                        $slot.prop("disabled", true);
                    } else {
                        res.data.forEach(function(s) {
                            if (s.status === "open") {
                                $slot.append(`
                                    <option value="${s.time_slot_id}" data-price="${s.price}">
                                        ${s.start_time.substring(0,5)} - ${s.end_time.substring(0,5)}
                                        (${Number(s.price).toLocaleString()} VNĐ)
                                    </option>
                                `);
                            }
                        });
                        $slot.prop("disabled", false);
                    }
                },
                error: function(xhr) {
                    console.error("❌ Lỗi load giờ:", xhr.responseText);
                    $slot.html(`<option disabled>Lỗi tải giờ</option>`);
                    $slot.prop("disabled", true);
                }
            });
        }

        // --------------------------
        // Khi chọn sân / ngày → load giờ
        // --------------------------
        $(document).on("change", ".court-select, .date-input", function() {
            const $row = $(this).closest(".booking-item-row");
            $row.find(".timeslot-select").prop("disabled", true).empty().append('<option value="" selected disabled>Chọn giờ</option>');
            $row.find(".price-input").val("");
            loadAvailableSlots($row);
        });

        // --------------------------
        // Khi chọn khung giờ → cập nhật giá
        // --------------------------
        $(document).on("change", ".timeslot-select", function() {
            const price = Number($(this).find("option:selected").data("price")) || 0;
            const $row = $(this).closest(".booking-item-row");
            $row.find(".price-input").val(price);
            calculateTotal();
        });
        // --------------------------
        // ✅ TẠO QR CODE MOMO
        // --------------------------
        function generateQRCode(totalAmount) {
            console.log("🔄 Gọi generateQRCode với total:", totalAmount);

            if (totalAmount < 1000) {
                $("#qr-container").hide();
                $("#qr-guide").addClass("d-none");
                return;
            }


            const tempOrderId = "temp_" + Date.now();
            currentTempOrderId = tempOrderId;
            $("#temp_order_id_input").val(tempOrderId);

            // ✅ Hiển thị container và loading
            $("#qr-container").show();
            $("#qr-loading").removeClass("d-none");
            $("#qr-content").addClass("d-none");
            $("#qr-error").addClass("d-none");

            console.log("📤 Gửi request tạo QR...", {
                tempOrderId: tempOrderId,
                amount: totalAmount
            });

            $.ajax({
                url: "{{ route('owner.bookings.generate-temp-qr') }}",
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    total_amount: totalAmount,
                    temp_order_id: tempOrderId
                },
                success: function(response) {
                    console.log("✅ Response từ server:", response);

                    if (response.success) {
                        const qrCodeBase64 = response.data.qr_code_url;
                        const payUrl = response.data.pay_url;
                        const orderId = response.data.order_id;
                        const amount = response.data.amount;

                        // ✅ CẬP NHẬT ĐÚNG ID
                        $('#qr-image').attr('src', qrCodeBase64);
                        $('#qr-amount').text(amount.toLocaleString('vi-VN'));

                        // ✅ Ẩn loading, hiển thị QR
                        $("#qr-loading").addClass("d-none");
                        $("#qr-content").removeClass("d-none");
                        $("#qr-guide").removeClass("d-none");
                        // ✅ Bắt đầu check thanh toán
                        startPaymentCheck(tempOrderId);

                        console.log("✅ QR Code đã hiển thị thành công!");
                    } else {
                        console.error("❌ Response lỗi:", response.message);
                        showQRError();
                        alert('Lỗi tạo QR: ' + response.message);
                    }
                },
                error: function(xhr) {
                    console.error("❌ AJAX Error:", xhr);
                    showQRError();

                    const errorMsg = xhr.responseJSON?.message || "Lỗi không xác định khi gọi Backend.";
                    alert('Lỗi hệ thống: ' + errorMsg);
                }
            });
        }

        function showQRError() {
            $("#qr-loading").addClass("d-none");
            $("#qr-content").addClass("d-none");
            $("#qr-error").removeClass("d-none");
        }

        // --------------------------
        // ✅ KIỂM TRA TRẠNG THÁI THANH TOÁN
        // --------------------------
        function startPaymentCheck(tempOrderId) {
            console.log("🔄 Bắt đầu check payment cho:", tempOrderId);

            // Dừng interval cũ nếu có
            if (paymentCheckInterval) {
                clearInterval(paymentCheckInterval);
            }

            checkCount = 0; // Reset counter

            paymentCheckInterval = setInterval(function() {
                checkCount++;
                console.log(`⏰ Check lần ${checkCount}/${maxChecks}...`);

                checkPaymentStatus(tempOrderId);

                // ✅ Timeout sau maxChecks lần
                if (checkCount >= maxChecks) {
                    clearInterval(paymentCheckInterval);
                    console.warn("⏰ Timeout: Dừng check payment.");
                }
            }, 3000); // Check mỗi 3 giây
        }

        function checkPaymentStatus(tempOrderId) {
            $.ajax({
                url: "{{ route('owner.bookings.check-temp-payment') }}",
                method: "GET",
                data: {
                    temp_order_id: tempOrderId
                },
                success: function(response) {
                    console.log("📥 Check payment response:", response);

                    if (response.success && response.paid) {
                        console.log("✅ ĐÃ THANH TOÁN!");

                        $("#payment-status")
                            .removeClass("alert-warning")
                            .addClass("alert-success")
                            .html('<i class="fas fa-check-circle me-1"></i> <strong>Đã thanh toán thành công!</strong>');

                        $("#payment_status_input").val("paid");

                        clearInterval(paymentCheckInterval);

                        // ✅ Tự động submit sau 2 giây
                        setTimeout(function() {
                            console.log("📤 Submit form...");
                            $("#booking-form").submit();
                        }, 2000);
                    }
                },
                error: function(xhr) {
                    console.error("❌ Lỗi check payment:", xhr.responseText);
                }
            });
        }

        // ✅ Dừng check khi submit form thủ công
        $("#booking-form").on("submit", function() {
            if (paymentCheckInterval) {
                clearInterval(paymentCheckInterval);
                console.log("⏹️ Dừng check payment do submit form");
            }
        });

        function showQRError() {
            $("#qr-loading").addClass("d-none");
            $("#qr-content").addClass("d-none");
            $("#qr-error").removeClass("d-none");
        }

        // --------------------------
        // ✅ KIỂM TRA TRẠNG THÁI THANH TOÁN
        // --------------------------
        function startPaymentCheck(tempOrderId) {
            // Dừng interval cũ nếu có
            if (paymentCheckInterval) {
                clearInterval(paymentCheckInterval);
            }
            checkCount = 0; // Reset counter
            paymentCheckInterval = setInterval(function() {
                checkCount++;
                checkPaymentStatus(tempOrderId);
                // ✅ Thêm timeout: Nếu quá maxChecks, dừng và submit unpaid
                if (checkCount >= maxChecks) {
                    clearInterval(paymentCheckInterval);
                    console.warn("⏰ Timeout: Dừng check payment sau " + maxChecks + " lần. Submit với unpaid.");
                    $("#payment_status_input").val("unpaid");
                    $("#booking-form").submit();
                }
            }, 3000);
        }

        function checkPaymentStatus(tempOrderId) {
            $.ajax({
                url: "{{ route('owner.bookings.check-temp-payment') }}",
                method: "GET",
                data: {
                    temp_order_id: tempOrderId
                },
                success: function(response) {
                    if (response.success && response.paid) {
                        $("#payment-status").removeClass("alert-warning").addClass("alert-success")
                            .html('<i class="fas fa-check-circle me-1"></i> <strong>Đã thanh toán thành công!</strong>');
                        $("#payment_status_input").val("paid");
                        clearInterval(paymentCheckInterval); // ✅ Dừng interval
                        setTimeout(function() {
                            $("#booking-form").submit();
                        }, 2000);
                    }
                    // Nếu không paid, tiếp tục (nhưng sẽ timeout)
                },
                error: function(xhr) {
                    console.error("Lỗi check payment:", xhr.responseText);
                }
            });
        }

        $("#booking-form").on("submit", function() {
            if (paymentCheckInterval) {
                clearInterval(paymentCheckInterval);
            }
        });

        // --------------------------
        // Tính tiền (Bao gồm Subtotal, Discount, Total)
        // --------------------------
        function calculateTotal() {
            let subtotal = 0;

            $(".price-input").each(function() {
                subtotal += Number($(this).val()) || 0;
            });

            const $promoOption = $("#promotion_id option:selected");
            const promoType = $promoOption.data("type");
            const promoValue = Number($promoOption.data("value")) || 0;
            const maxDiscount = Number($promoOption.data("max")) || 0;

            let discountAmount = 0;

            if (subtotal > 0 && promoValue > 0) {
                if (promoType === '%') {
                    discountAmount = subtotal * (promoValue / 100);
                    if (maxDiscount > 0 && discountAmount > maxDiscount) {
                        discountAmount = maxDiscount;
                    }
                } else {
                    discountAmount = promoValue;
                    if (discountAmount > subtotal) {
                        discountAmount = subtotal;
                    }
                }
            }

            discountAmount = Math.floor(discountAmount);
            const total = Math.max(subtotal - discountAmount, 0);

            $("#subtotal_display").text(subtotal.toLocaleString('vi-VN') + " VNĐ");
            $("#discount_display").text("- " + discountAmount.toLocaleString('vi-VN') + " VNĐ");
            $("#total_display").text(total.toLocaleString('vi-VN') + " VNĐ");

            $("#subtotal_input").val(subtotal);
            $("#discount_amount_input").val(discountAmount);
            $("#total_amount").val(total);

            // ✅ Tạo QR code khi có tổng tiền
            if (total >= 1000) {
                generateQRCode(total);
            } else {
                $("#qr-container").hide();
                $("#qr-guide").addClass("d-none");
            }
        }

        // --------------------------
        // Quản lý Khuyến mãi
        // --------------------------
        const select = document.getElementById('promotion_id');
        const inputMax = document.getElementById('max_discount_amount');

        function updateMaxValueAndCalculate() {
            const opt = select.options[select.selectedIndex];

            if (!opt.value) {
                inputMax.value = 0;
            } else {
                const type = opt.dataset.type;
                const value = Number(opt.dataset.value);
                const max = Number(opt.dataset.max);

                if (type === '%') {
                    inputMax.value = max || 0;
                } else {
                    inputMax.value = value;
                }
            }

            calculateTotal();
        }

        select.addEventListener('change', updateMaxValueAndCalculate);
        updateMaxValueAndCalculate();

        // --------------------------
        // Tự động thêm dòng đầu tiên
        // --------------------------
        if (container.children().length === 0) {
            $("#add-booking-item-btn").click();
        }

        calculateTotal();

        // ✅ Cleanup khi rời khỏi trang
        window.addEventListener('beforeunload', function() {
            if (paymentCheckInterval) {
                clearInterval(paymentCheckInterval);
            }
        });

    })();
</script>
@endpush