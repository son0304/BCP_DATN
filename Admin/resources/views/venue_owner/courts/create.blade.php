@extends('app')

@section('content')

    <div class="container-fluid mt-4">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white border-0 py-3">
                <h3 class="mb-1">Thêm sân mới</h3>
                <div class="text-muted small">
                    Thuộc thương hiệu: <span class="fw-semibold text-success">{{ $venue->name }}</span>
                </div>
            </div>

            <div class="card-body">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <strong><i class="fas fa-exclamation-triangle"></i> Vui lòng kiểm tra lại dữ liệu:</strong>
                        <ul class="mb-0 mt-2">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form id="create-court-form" action="{{ route('owner.venues.courts.store', ['venue' => $venue->id]) }} "
                    novalidate autocomplete="off" method="POST">
                    @csrf
                    <input type="hidden" name="venue_id" value="{{ $venue->id }}">

                    <fieldset class="mb-4">
                        <legend class="h6 text-primary">1. Thông tin cơ bản</legend>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">Tên sân <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                    id="name" name="name" value="{{ old('name') }}" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="venue_type_id" class="form-label">Loại hình <span
                                        class="text-danger">*</span></label>
                                <select class="form-select @error('venue_type_id') is-invalid @enderror" id="venue_type_id"
                                    name="venue_type_id" required>
                                    <option value="" disabled selected>-- Chọn loại hình --</option>
                                    @foreach ($venue->venueTypes as $venueType)
                                        <option value="{{ $venueType->id }}"
                                            {{ old('venue_type_id') == $venueType->id ? 'selected' : '' }}>
                                            {{ $venueType->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="is_indoor" class="form-label">Loại sân <span
                                        class="text-danger">*</span></label>
                                <select class="form-select" id="is_indoor" name="is_indoor" required>
                                    <option value="1" {{ old('is_indoor', '1') == '1' ? 'selected' : '' }}>Trong nhà
                                    </option>
                                    <option value="0" {{ old('is_indoor', '0') == '0' ? 'selected' : '' }}>Ngoài trời
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="surface" class="form-label">Bề mặt sân</label>
                                <input type="text" class="form-control" id="surface" name="surface"
                                    value="{{ old('surface') }}" placeholder="Ví dụ: Cỏ nhân tạo">
                            </div>
                        </div>
                    </fieldset>

                    <fieldset>
                        <legend class="h6 text-primary">2. Khung giờ & Giá</legend>
                        <p class="text-muted small">
                            Bạn phải thêm ít nhất một khung giờ hoạt động.
                        </p>

                        <div class="table-responsive bg-light rounded border p-2">
                            <table class="table table-bordered align-middle mb-0">
                                <thead class="table-light small text-muted fw-bold">
                                    <tr>
                                        <th style="width: 30%">Giờ bắt đầu <span class="text-danger">*</span></th>
                                        <th style="width: 30%">Giờ kết thúc <span class="text-danger">*</span></th>
                                        <th style="width: 30%">Giá (VNĐ) <span class="text-danger">*</span></th>
                                        <th style="width: 10%" class="text-center">Xóa</th>
                                    </tr>
                                </thead>
                                <tbody id="time-slots-container">

                                </tbody>
                            </table>
                        </div>
                        @error('time_slots')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror

                        <button type="button" id="add-time-slot-btn" class="btn btn-outline-primary mt-3">
                            <i class="fas fa-plus me-1"></i> Thêm khung giờ
                        </button>
                    </fieldset>

                    <div class="card-footer bg-white text-end border-0 px-0 pt-4">
                        <a href="{{ route('owner.venues.show', $venue->id) }}" class="btn btn-secondary">Hủy bỏ</a>
                        <button type="submit" class="btn btn-primary" id="btn-submit-form">
                            <i class="fas fa-save me-1"></i> Lưu lại
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="old-time-slots" data-slots='@json(old('time_slots', []))'></div>

@endsection

@push('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const container = $('#time-slots-container');
            const addBtn = $('#add-time-slot-btn');
            container.empty();

            function createTimeSlotRowHtml(start = '', end = '', price = '') {
                return `
                <tr class="time-slot-row">
                    <td>
                        <input type="time" class="form-control form-control-sm time-start" value="${start}" required>
                    </td>
                    <td>
                        <input type="time" class="form-control form-control-sm time-end" value="${end}" required>
                    </td>
                    <td>
                        <input type="number" class="form-control form-control-sm time-price"
                               value="${price}" placeholder="Nhập giá" min="0" required>
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-sm btn-outline-danger remove-slot-btn" title="Xóa">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>`;
            }

            function updateInputNames() {
                container.find('.time-slot-row').each(function(index) {
                    const $row = $(this);
                    $row.find('.time-start').attr('name', `time_slots[${index}][start_time]`);
                    $row.find('.time-end').attr('name', `time_slots[${index}][end_time]`);
                    $row.find('.time-price').attr('name', `time_slots[${index}][price]`);
                });
            }
            //chia giờ
            function splitTimeIntoHourlySlots(startTime, endTime, price) {
                const GOLDEN_HOUR_START = 17;
                const GOLDEN_HOUR_MULTIPLIER = 1.5;
                const slots = [];
                const today = new Date('2000-01-01');
                const startParts = startTime.split(':').map(Number);
                const endParts = endTime.split(':').map(Number);

                let start = new Date(today);
                start.setHours(startParts[0], startParts[1], 0, 0);

                let end = new Date(today);
                end.setHours(endParts[0], endParts[1], 0, 0);

                if (end <= start) end.setDate(end.getDate() + 1);

                const basePrice = Number(price);
                let current = new Date(start);

                while (current < end) {
                    const next = new Date(current);
                    next.setHours(next.getHours() + 1);
                    const nextHour = (next > end) ? end : next;

                    if (current.getTime() === nextHour.getTime()) break;

                    let currentPrice = (current.getHours() >= GOLDEN_HOUR_START) ? basePrice *
                        GOLDEN_HOUR_MULTIPLIER : basePrice;
                    currentPrice = Math.round(currentPrice);

                    slots.push({
                        start_time: current.toTimeString().substring(0, 5),
                        end_time: nextHour.toTimeString().substring(0, 5),
                        price: currentPrice
                    });

                    current = nextHour;
                    if (current.getTime() === end.getTime()) break;
                }
                return slots;
            }


            $(document).off('click', '#add-time-slot-btn').on('click', '#add-time-slot-btn', function() {
                container.append(createTimeSlotRowHtml());
                updateInputNames();
            });


            $(document).off('click', '.remove-slot-btn').on('click', '.remove-slot-btn', function() {
                $(this).closest('.time-slot-row').remove();
                updateInputNames();
            });

            // Tự động chia giờ
            $(document).off('change', '.time-start, .time-end, .time-price').on('change',
                '.time-start, .time-end, .time-price',
                function() {
                    const $row = $(this).closest('.time-slot-row');
                    const startTime = $row.find('.time-start').val();
                    const endTime = $row.find('.time-end').val();
                    const price = $row.find('.time-price').val();

                    if (startTime && endTime && price) {
                        const slots = splitTimeIntoHourlySlots(startTime, endTime, price);
                        if (slots.length > 1) {
                            $row.remove();
                            slots.forEach(slot => {
                                container.append(createTimeSlotRowHtml(slot.start_time, slot.end_time,
                                    slot.price));
                            });
                            updateInputNames();
                        }
                    }
                });

            // Submit Form
            $('#create-court-form').off('submit').on('submit', function(e) {
                // Xóa các dòng trống rỗng trước khi gửi
                container.find('.time-slot-row').each(function() {
                    const $row = $(this);
                    const start = $row.find('.time-start').val();
                    const end = $row.find('.time-end').val();
                    const price = $row.find('.time-price').val();

                    if (!start && !end && !price) {
                        $row.remove();
                    }
                });

                updateInputNames();

                // Kiểm tra nếu không còn dòng nào
                if (container.find('.time-slot-row').length === 0) {
                    e.preventDefault();
                    alert('Vui lòng thêm ít nhất một khung giờ hoạt động!');
                    container.append(createTimeSlotRowHtml());
                    updateInputNames();
                    return;
                }
            });

            // --- KHỞI TẠO DỮ LIỆU BAN ĐẦU ---
            const oldData = $('#old-time-slots').data('slots');
            let hasValidOldData = false;

            if (oldData && Array.isArray(oldData) && oldData.length > 0) {
                oldData.forEach(slot => {
                    const s = (slot.start_time || '').toString();
                    const e = (slot.end_time || '').toString();
                    const p = (slot.price || '').toString();

                    if (s.trim() !== '' || e.trim() !== '' || p.trim() !== '') {
                        container.append(createTimeSlotRowHtml(s, e, p));
                        hasValidOldData = true;
                    }
                });
            }
            if (!hasValidOldData) {
                container.append(createTimeSlotRowHtml());
            }

            updateInputNames();
        });
    </script>
@endpush
