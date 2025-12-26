@extends('app')

@section('content')
    {{-- CSS FLATPICKR --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

    <style>
        /* CSS tuỳ chỉnh cho Validate giống form trước */
        .is-invalid { border-color: #dc3545 !important; background-image: none !important; }
        .invalid-feedback.custom-error-msg { display: block; font-size: 0.875em; margin-top: 0.25rem; color: #dc3545; }
        .time-slot-table .form-control.is-invalid { padding-right: 5px; }
        /* Style cho bảng khung giờ */
        .time-slot-table th { font-weight: 600; background-color: #f8f9fa; }
    </style>

    <div class="container-fluid mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="h4 mb-0">Thêm sân mới</h2>
                <div class="text-muted small">
                    Thuộc thương hiệu: <span class="fw-bold text-success">{{ $venue->name }}</span>
                </div>
            </div>
            <div>
                <a href="{{ route('owner.venues.show', $venue->id) }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Quay lại chi tiết
                </a>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-4">
                        {{-- Hiển thị lỗi tổng từ Server nếu có --}}
                        @if ($errors->any())
                            <div class="alert alert-danger mb-4">
                                <i class="fas fa-exclamation-triangle me-1"></i> Vui lòng kiểm tra lại dữ liệu bên dưới.
                            </div>
                        @endif

                        <form id="create-court-form" action="{{ route('owner.venues.courts.store', ['venue' => $venue->id]) }}" method="POST" novalidate autocomplete="off">
                            @csrf
                            <input type="hidden" name="venue_id" value="{{ $venue->id }}">

                            {{-- 1. THÔNG TIN CƠ BẢN --}}
                            <h5 class="text-primary mb-3 border-bottom pb-2">1. Thông tin cơ bản</h5>
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label class="form-label fw-bold">Tên sân <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                        value="{{ old('name') }}" placeholder="Ví dụ: Sân 1, Sân VIP..." required>
                                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label fw-bold">Loại hình <span class="text-danger">*</span></label>
                                    <select name="venue_type_id" id="venue_type_id" class="form-select @error('venue_type_id') is-invalid @enderror" required>
                                        <option value="">-- Chọn loại hình --</option>
                                        @foreach ($venue->venueTypes as $venueType)
                                            <option value="{{ $venueType->id }}" {{ old('venue_type_id') == $venueType->id ? 'selected' : '' }}>
                                                {{ $venueType->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('venue_type_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label fw-bold">Trong nhà / Ngoài trời</label>
                                    <select name="is_indoor" class="form-select">
                                        <option value="1" {{ old('is_indoor', '1') == '1' ? 'selected' : '' }}>Trong nhà</option>
                                        <option value="0" {{ old('is_indoor') == '0' ? 'selected' : '' }}>Ngoài trời</option>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label fw-bold">Mặt sân</label>
                                    <input type="text" name="surface" class="form-control" value="{{ old('surface') }}" placeholder="Cỏ nhân tạo, Sàn gỗ...">
                                </div>
                            </div>

                            {{-- 2. KHUNG GIỜ VÀ GIÁ --}}
                            <h5 class="text-primary mb-3 mt-4 border-bottom pb-2 d-flex justify-content-between align-items-center">
                                <span>2. Khung giờ hoạt động & Giá</span>
                                <button type="button" id="add-time-slot-btn" class="btn btn-sm btn-success">
                                    <i class="fas fa-plus me-1"></i> Thêm khung giờ
                                </button>
                            </h5>

                            <div class="table-responsive">
                                <table class="table table-bordered align-middle time-slot-table">
                                    <thead>
                                        <tr>
                                            <th style="width: 30%">Giờ bắt đầu <span class="text-danger">*</span></th>
                                            <th style="width: 30%">Giờ kết thúc <span class="text-danger">*</span></th>
                                            <th style="width: 30%">Giá (VNĐ) <span class="text-danger">*</span></th>
                                            <th style="width: 10%" class="text-center">Xóa</th>
                                        </tr>
                                    </thead>
                                    <tbody id="time-slots-container">
                                        {{-- Dữ liệu cũ (nếu validate fail) hoặc dòng mặc định sẽ được JS thêm vào --}}
                                    </tbody>
                                </table>
                            </div>
                            @error('time_slots') <div class="text-danger small mb-2">{{ $message }}</div> @enderror
                            <div class="form-text text-muted mb-4">
                                <i class="fas fa-info-circle"></i> Mẹo: Nhập khoảng thời gian lớn (vd: 07:00 - 10:00), hệ thống sẽ gợi ý tự động chia nhỏ thành các khung 1 tiếng.
                            </div>

                            <div class="text-end">
                                <a href="{{ route('owner.venues.show', $venue->id) }}" class="btn btn-secondary me-2">Hủy bỏ</a>
                                <button type="submit" class="btn btn-primary px-4">
                                    <i class="fas fa-save me-1"></i> Lưu sân mới
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Data ẩn để JS khôi phục lại form khi validate lỗi --}}
    <div id="old-time-slots" data-slots='@json(old('time_slots', []))'></div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // --- UTILS ---
        function initFlatpickr(selector) {
            flatpickr(selector, {
                enableTime: true,
                noCalendar: true,
                dateFormat: "H:i",
                time_24hr: true,
                minuteIncrement: 30,
                allowInput: true
            });
        }

        function timeToMinutes(str) { if(!str) return 0; const [h, m] = str.split(':').map(Number); return h * 60 + m; }
        function minutesToTime(mins) {
            if(mins === 1440 || mins === 0) return "00:00";
            let h = Math.floor(mins / 60);
            let m = mins % 60;
            if(h >= 24) h -= 24;
            return `${String(h).padStart(2,'0')}:${String(m).padStart(2,'0')}`;
        }

        $(document).ready(function() {
            const container = $('#time-slots-container');

            // Hàm tạo HTML cho 1 dòng
            function createRowHtml(index, start='', end='', price='') {
                // index ngẫu nhiên để tránh trùng ID khi xoá/thêm liên tục
                const randId = Math.floor(Math.random() * 1000000);
                return `
                <tr class="time-slot-row">
                    <td>
                        <input type="text" name="time_slots[${randId}][start_time]"
                               class="form-control form-control-sm time-start time-picker"
                               value="${start}" placeholder="00:00" required>
                    </td>
                    <td>
                        <input type="text" name="time_slots[${randId}][end_time]"
                               class="form-control form-control-sm time-end time-picker"
                               value="${end}" placeholder="00:00" required>
                    </td>
                    <td>
                        <input type="number" name="time_slots[${randId}][price]"
                               class="form-control form-control-sm time-price"
                               value="${price}" placeholder="Nhập giá" min="0" required>
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-sm btn-outline-danger remove-slot-btn" title="Xóa">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>`;
            }

            // --- 1. KHỞI TẠO DỮ LIỆU CŨ HOẶC MẶC ĐỊNH ---
            const oldData = $('#old-time-slots').data('slots');
            if (oldData && Array.isArray(oldData) && oldData.length > 0) {
                // Nếu có old data (do submit lỗi)
                oldData.forEach((slot, i) => {
                    const s = slot.start_time || '';
                    const e = slot.end_time || '';
                    const p = slot.price || '';
                    if (s || e || p) {
                        container.append(createRowHtml(i, s, e, p));
                    }
                });
            } else {
                // Mặc định 1 dòng trống
                container.append(createRowHtml(0));
            }
            initFlatpickr(".time-picker");

            // --- 2. SỰ KIỆN THÊM / XÓA ---
            $('#add-time-slot-btn').click(function() {
                const newRow = $(createRowHtml(0));
                container.append(newRow);
                initFlatpickr(newRow.find('.time-picker'));
            });

            $(document).on('click', '.remove-slot-btn', function() {
                // Nếu còn nhiều hơn 1 dòng thì xoá thoải mái
                // Nếu chỉ còn 1 dòng thì clear value thôi (UX tốt hơn xoá sạch)
                if (container.find('tr').length > 1) {
                    $(this).closest('tr').remove();
                } else {
                    const row = $(this).closest('tr');
                    row.find('input').val('');
                }
            });

// --- 3. LOGIC TỰ ĐỘNG CHIA GIỜ ---
$(document).on('change', '.time-end, .time-price', function() {
    const row = $(this).closest('tr');
    const startVal = row.find('.time-start').val();
    const endVal = row.find('.time-end').val();
    const priceVal = row.find('.time-price').val();

    if (!startVal || !endVal || !priceVal) return;

    const startMin = timeToMinutes(startVal);
    const endMin = timeToMinutes(endVal) === 0 ? 1440 : timeToMinutes(endVal);

    // Check logic cơ bản
    if (endMin <= startMin) {
        if ($(this).hasClass('time-end')) {
            alert('Giờ kết thúc phải lớn hơn giờ bắt đầu!');
            $(this).val(''); // Reset ô nhập lỗi
        }
        return;
    }

    // Nếu khoảng thời gian > 60 phút -> Hỏi chia nhỏ
    if ((endMin - startMin) > 60) {
        if(confirm(`Bạn đang nhập khoảng ${endMin - startMin} phút. Hệ thống có thể tự động chia nhỏ thành các khung 60 phút để khách dễ đặt hơn. Bạn có muốn chia không?`)) {
            splitSlots(row, startMin, endMin, priceVal);
        }
    }
});

function splitSlots(originalRow, startMin, endMin, price) {
    const tbody = originalRow.closest('tbody');
    // Xoá dòng cũ đi
    originalRow.remove();

    let current = startMin;
    while(current < endMin) {
        let next = current + 60;
        if(next > endMin) next = endMin;

        // Tạo dòng mới
        const sStr = minutesToTime(current);
        const eStr = minutesToTime(next);
        const randId = Math.floor(Math.random() * 1000000); // ID ngẫu nhiên cho name array

        const html = `
        <tr class="time-slot-row">
            <td>
                <input type="text" name="time_slots[${randId}][start_time]"
                       class="form-control form-control-sm time-start time-picker"
                       value="${sStr}" required>
            </td>
            <td>
                <input type="text" name="time_slots[${randId}][end_time]"
                       class="form-control form-control-sm time-end time-picker"
                       value="${eStr}" required>
            </td>
            <td>
                <input type="number" name="time_slots[${randId}][price]"
                       class="form-control form-control-sm time-price"
                       value="${price}" required>
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-outline-danger remove-slot-btn"><i class="fas fa-trash"></i></button>
            </td>
        </tr>`;

        const newRow = $(html);
        tbody.append(newRow);
        initFlatpickr(newRow.find('.time-picker'));

        current = next;
    }
}

// --- 4. VALIDATE TRƯỚC KHI SUBMIT ---
$('#create-court-form').on('submit', function(e) {
    // Reset trạng thái lỗi cũ
    $('.is-invalid').removeClass('is-invalid');
    $('.custom-error-msg').remove();

    let isValid = true;
    let firstError = null;

    // Hàm hiển thị lỗi cục bộ
    function showErr(selector, msg) {
        const el = $(selector);
        el.addClass('is-invalid');
        // Thêm text lỗi
        if(el.next('.invalid-feedback').length === 0) {
            el.after(`<div class="invalid-feedback custom-error-msg">${msg}</div>`);
        } else {
            el.next('.invalid-feedback').text(msg).show();
        }
        if(!firstError) firstError = el;
        isValid = false;
    }

    // 4.1. Validate Thông tin cơ bản
    const nameInput = $('input[name="name"]');
    if (nameInput.val().trim() === '') showErr(nameInput, 'Tên sân không được để trống.');

    const typeSelect = $('select[name="venue_type_id"]');
    if (typeSelect.val() === '') showErr(typeSelect, 'Vui lòng chọn loại hình.');

    // 4.2. Validate Danh sách khung giờ
    const rows = container.find('tr');
    if (rows.length === 0) {
        alert('Vui lòng thêm ít nhất một khung giờ hoạt động!');
        isValid = false;
    } else {
        rows.each(function() {
            const tr = $(this);
            const tStart = tr.find('.time-start');
            const tEnd = tr.find('.time-end');
            const tPrice = tr.find('.time-price');

            // Check rỗng
            if (!tStart.val()) { tStart.addClass('is-invalid'); isValid = false; }
            if (!tEnd.val()) { tEnd.addClass('is-invalid'); isValid = false; }
            if (!tPrice.val()) { tPrice.addClass('is-invalid'); isValid = false; }

            // Check logic giờ
            if (tStart.val() && tEnd.val()) {
                if (timeToMinutes(tEnd.val()) <= timeToMinutes(tStart.val())) {
                    tEnd.addClass('is-invalid');
                    isValid = false;
                    if(!firstError) firstError = tEnd;
                    // Alert 1 lần thôi để đỡ phiền
                    if(isValid === false && rows.length === 1) {
                        alert('Giờ kết thúc phải lớn hơn giờ bắt đầu!');
                    }
                }
            }
        });
    }

    // Nếu có lỗi -> Chặn submit và cuộn đến lỗi
    if (!isValid) {
        e.preventDefault();
        if (firstError) {
            $('html, body').animate({
                scrollTop: $(firstError).offset().top - 150
            }, 500);
            firstError.focus();
        } else if (rows.length === 0) {
             // Nếu lỗi do chưa có row nào thì cuộn xuống bảng
             $('html, body').animate({
                scrollTop: $('.time-slot-table').offset().top - 150
            }, 500);
        }
    }
});
});
</script>
@endpush
