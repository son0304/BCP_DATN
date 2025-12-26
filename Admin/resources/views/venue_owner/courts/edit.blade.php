@extends('app')

@section('content')
    {{-- CSS FLATPICKR --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

    <style>
        .is-invalid {
            border-color: #dc3545 !important;
            background-image: none !important;
        }

        .invalid-feedback.custom-error-msg {
            display: block;
            font-size: 0.875em;
            margin-top: 0.25rem;
            color: #dc3545;
        }

        .time-slot-table .form-control.is-invalid {
            padding-right: 5px;
        }

        /* Style cho bảng khung giờ */
        .time-slot-table th {
            font-weight: 600;
            background-color: #f8f9fa;
        }
    </style>

    <div class="container-fluid mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="h4 mb-0">Chỉnh sửa sân: <span class="text-primary">{{ $court->name }}</span></h2>
                <div class="text-muted small">
                    Thuộc thương hiệu: <span class="fw-bold text-success">{{ $venue->name }}</span>
                </div>
            </div>
            <div>
                <a href="{{ route('owner.venues.courts.show', ['venue' => $venue->id, 'court' => $court->id]) }}"
                    class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Quay lại chi tiết
                </a>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-4">
                        @if ($errors->any())
                            <div class="alert alert-danger mb-4">
                                <i class="fas fa-exclamation-triangle me-1"></i> Vui lòng kiểm tra lại dữ liệu bên dưới.
                            </div>
                        @endif

                        <form id="edit-court-form"
                            action="{{ route('owner.venues.courts.update', ['venue' => $venue->id, 'court' => $court->id]) }}"
                            method="POST" novalidate autocomplete="off">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="venue_id" value="{{ $venue->id }}">

                            {{-- 1. THÔNG TIN CƠ BẢN --}}
                            <h5 class="text-primary mb-3 border-bottom pb-2">1. Thông tin cơ bản</h5>
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label class="form-label fw-bold">Tên sân <span class="text-danger">*</span></label>
                                    <input type="text" name="name"
                                        class="form-control @error('name') is-invalid @enderror"
                                        value="{{ old('name', $court->name) }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label fw-bold">Loại hình <span class="text-danger">*</span></label>
                                    <select name="venue_type_id" id="venue_type_id"
                                        class="form-select @error('venue_type_id') is-invalid @enderror" required>
                                        <option value="">-- Chọn loại hình --</option>
                                        @foreach ($venue->venueTypes as $venueType)
                                            <option value="{{ $venueType->id }}"
                                                {{ old('venue_type_id', $court->venue_type_id) == $venueType->id ? 'selected' : '' }}>
                                                {{ $venueType->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('venue_type_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label fw-bold">Trong nhà / Ngoài trời</label>
                                    <select name="is_indoor" class="form-select">
                                        <option value="1"
                                            {{ old('is_indoor', $court->is_indoor) == '1' ? 'selected' : '' }}>Trong nhà
                                        </option>
                                        <option value="0"
                                            {{ old('is_indoor', $court->is_indoor) == '0' ? 'selected' : '' }}>Ngoài trời
                                        </option>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label fw-bold">Mặt sân</label>
                                    <input type="text" name="surface" class="form-control"
                                        value="{{ old('surface', $court->surface) }}">
                                </div>
                            </div>

                            {{-- 2. KHUNG GIỜ VÀ GIÁ --}}
                            <h5
                                class="text-primary mb-3 mt-4 border-bottom pb-2 d-flex justify-content-between align-items-center">
                                <span>2. Khung giờ hoạt động & Giá</span>
                                <button type="button" id="add-time-slot-btn" class="btn btn-sm btn-success">
                                    <i class="fas fa-plus me-1"></i> Thêm khung giờ
                                </button>
                            </h5>

                            <div class="alert alert-warning small">
                                <i class="fas fa-exclamation-circle me-1"></i>
                                Lưu ý: Việc cập nhật khung giờ sẽ xóa lịch trống (chưa có người đặt) từ hôm nay trở đi và
                                tạo lại theo cấu hình mới. Lịch đã đặt sẽ được giữ nguyên.
                            </div>

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
                                        {{-- Dữ liệu cũ hoặc dữ liệu hiện tại của sân sẽ được JS load vào --}}
                                    </tbody>
                                </table>
                            </div>
                            @error('time_slots')
                                <div class="text-danger small mb-2">{{ $message }}</div>
                            @enderror

                            <div class="text-end mt-4">
                                <button type="submit" class="btn btn-primary px-4">
                                    <i class="fas fa-save me-1"></i> Cập nhật thông tin
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Data ẩn để JS khôi phục --}}
    {{-- Nếu submit lỗi -> lấy old('time_slots'), nếu không -> lấy dữ liệu hiện tại từ DB --}}
    @php
        $slotsData = old('time_slots') ? old('time_slots') : $currentPricesDetailed;
    @endphp
    <div id="initial-slots-data" data-slots='@json($slotsData)'></div>
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

        function timeToMinutes(str) {
            if (!str) return 0;
            const [h, m] = str.split(':').map(Number);
            return h * 60 + m;
        }

        function minutesToTime(mins) {
            if (mins === 1440 || mins === 0) return "00:00";
            let h = Math.floor(mins / 60);
            let m = mins % 60;
            if (h >= 24) h -= 24;
            return `${String(h).padStart(2,'0')}:${String(m).padStart(2,'0')}`;
        }

        $(document).ready(function() {
            const container = $('#time-slots-container');

            function createRowHtml(start = '', end = '', price = '') {
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

            // --- 1. KHỞI TẠO DỮ LIỆU ---
            const initialData = $('#initial-slots-data').data('slots');
            let hasData = false;

            if (initialData && Array.isArray(initialData) && initialData.length > 0) {
                initialData.forEach(slot => {
                    // Xử lý format khác nhau giữa DB và old()
                    const s = slot.start_time || '';
                    const e = slot.end_time || '';
                    const p = slot.price || '';
                    if (s || e || p) {
                        // Nếu lấy từ DB có thể có giây (05:00:00), cắt đi
                        const cleanS = s.length > 5 ? s.substring(0, 5) : s;
                        const cleanE = e.length > 5 ? e.substring(0, 5) : e;
                        container.append(createRowHtml(cleanS, cleanE, p));
                        hasData = true;
                    }
                });
            }

            if (!hasData) {
                container.append(createRowHtml());
            }
            initFlatpickr(".time-picker");

            // --- 2. EVENTS ---
            $('#add-time-slot-btn').click(function() {
                const newRow = $(createRowHtml());
                container.append(newRow);
                initFlatpickr(newRow.find('.time-picker'));
            });

            $(document).on('click', '.remove-slot-btn', function() {
                if (container.find('tr').length > 1) {
                    $(this).closest('tr').remove();
                } else {
                    // Nếu xoá dòng cuối cùng thì chỉ clear value
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

                if (endMin <= startMin) {
                    if ($(this).hasClass('time-end')) {
                        alert('Giờ kết thúc phải lớn hơn giờ bắt đầu!');
                        $(this).val('');
                    }
                    return;
                }

                if ((endMin - startMin) > 60) {
                    if (confirm(
                            `Bạn đang nhập khoảng ${endMin - startMin} phút. Có muốn chia nhỏ thành các khung 60 phút không?`
                            )) {
                        splitSlots(row, startMin, endMin, priceVal);
                    }
                }
            });

            function splitSlots(originalRow, startMin, endMin, price) {
                const tbody = originalRow.closest('tbody');
                originalRow.remove();

                let current = startMin;
                while (current < endMin) {
                    let next = current + 60;
                    if (next > endMin) next = endMin;

                    const sStr = minutesToTime(current);
                    const eStr = minutesToTime(next);

                    const newRow = $(createRowHtml(sStr, eStr, price));
                    tbody.append(newRow);
                    initFlatpickr(newRow.find('.time-picker'));

                    current = next;
                }
            }

            // --- 4. VALIDATE SUBMIT ---
            $('#edit-court-form').on('submit', function(e) {
                $('.is-invalid').removeClass('is-invalid');
                $('.custom-error-msg').remove();

                let isValid = true;
                let firstError = null;

                function showErr(selector, msg) {
                    const el = $(selector);
                    el.addClass('is-invalid');
                    if (el.next('.invalid-feedback').length === 0) {
                        el.after(`<div class="invalid-feedback custom-error-msg">${msg}</div>`);
                    } else {
                        el.next('.invalid-feedback').text(msg).show();
                    }
                    if (!firstError) firstError = el;
                    isValid = false;
                }

                // Basic Info
                const nameInput = $('input[name="name"]');
                if (nameInput.val().trim() === '') showErr(nameInput, 'Tên sân không được để trống.');

                const typeSelect = $('select[name="venue_type_id"]');
                if (typeSelect.val() === '') showErr(typeSelect, 'Vui lòng chọn loại hình.');

                // Time Slots
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

                        if (!tStart.val()) {
                            tStart.addClass('is-invalid');
                            isValid = false;
                        }
                        if (!tEnd.val()) {
                            tEnd.addClass('is-invalid');
                            isValid = false;
                        }
                        if (!tPrice.val()) {
                            tPrice.addClass('is-invalid');
                            isValid = false;
                        }

                        if (tStart.val() && tEnd.val()) {
                            if (timeToMinutes(tEnd.val()) <= timeToMinutes(tStart.val())) {
                                tEnd.addClass('is-invalid');
                                isValid = false;
                                if (!firstError) firstError = tEnd;
                            }
                        }
                    });
                }

                if (!isValid) {
                    e.preventDefault();
                    if (firstError) {
                        $('html, body').animate({
                            scrollTop: $(firstError).offset().top - 150
                        }, 500);
                        firstError.focus();
                    } else if (rows.length === 0) {
                        $('html, body').animate({
                            scrollTop: $('.time-slot-table').offset().top - 150
                        }, 500);
                    }
                }
            });
        });
    </script>
@endpush
