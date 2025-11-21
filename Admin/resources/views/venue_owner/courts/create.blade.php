@extends('app')

@section('content')


<div class="container-fluid mt-4">

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white border-0 py-3">
            <h3 class="mb-1">Thêm sân mới</h3>
            <div class="text-muted small">
                Thuộc thương hiệu:
                <span class="fw-semibold text-success">{{ $venue->name }}</span>
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

            <form action="{{ route('owner.venues.courts.store', ['venue' => $venue->id]) }}" method="POST"> @csrf
                <input type="hidden" name="venue_id" value="{{ $venue->id }}">

                <fieldset class="mb-4">
                    <legend class="h6 text-primary">1. Thông tin cơ bản</legend>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Tên sân <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name"
                                value="{{ old('name') }}" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="venue_type_id" class="form-label">Loại hình <span
                                    class="text-danger">*</span></label>
                            <select class="form-select" id="venue_type_id" name="venue_type_id" required>
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
                                value="{{ old('surface') }}" placeholder="Ví dụ: Cỏ nhân tạo, Sàn gỗ">
                        </div>
                    </div>
                </fieldset>

                <fieldset>
                    <legend class="h6 text-primary">2. Khung giờ & Giá</legend>
                    <p class="text-muted small">
                        Thiết lập các khung giờ hoạt động và giá tiền tương ứng. Lịch hoạt động cho 30 ngày tới sẽ được
                        tự động tạo. <strong>Bạn phải thêm ít nhất một khung giờ.</strong>
                    </p>

                    <div class="table-responsive bg-light rounded border p-2">
                        <table class="table table-bordered align-middle mb-0">
                            <thead class="table-light small text-muted fw-bold">
                                <tr>
                                    <th style="width: 30%">Giờ bắt đầu</th>
                                    <th style="width: 30%">Giờ kết thúc</th>
                                    <th style="width: 30%">Giá (VNĐ)</th>
                                    <th style="width: 10%" class="text-center">Hành động</th>
                                </tr>
                            </thead>
                            <tbody id="time-slots-container">
                                {{-- JS sẽ append các dòng vào đây --}}
                            </tbody>
                        </table>
                    </div>

                    <button type="button" id="add-time-slot-btn" class="btn btn-outline-primary mt-3">
                        <i class="fas fa-plus me-1"></i> Thêm khung giờ
                    </button>
                </fieldset>


                <div class="card-footer bg-white text-end border-0 px-0 pt-4">
                    <a href="/owner/venues/{{ $venue->id }}" class="btn btn-secondary">Hủy bỏ</a>
                    <button type="submit" class="btn btn-primary">Lưu lại</button>
                </div>
            </form>

        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof $ === 'undefined') {
            console.error('jQuery is not loaded!');
            return;
        }

        const container = $('#time-slots-container');

        function createTimeSlotRow() {
            return `
        <tr class="time-slot-row">
            <td>
                <input type="time" class="form-control form-control-sm time-start" required>
            </td>
            <td>
                <input type="time" class="form-control form-control-sm time-end" required>
            </td>
            <td>
                <input type="number" class="form-control form-control-sm time-price"
                       placeholder="Nhập giá (VNĐ)" min="0" step="1000" required>
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-outline-danger remove-slot-btn" title="Xóa khung giờ">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        </tr>
    `;
        }
        function splitTimeIntoHourlySlots(startTime, endTime, price) {
            // CẤU HÌNH GIỜ VÀNG
            const GOLDEN_HOUR_START = 17; // 5 PM (17:00)
            const GOLDEN_HOUR_MULTIPLIER = 1.5;
            const slots = [];

            // Khởi tạo ngày/giờ ảo
            const today = new Date('2000-01-01');
            const startParts = startTime.split(':').map(Number);
            const endParts = endTime.split(':').map(Number);

            let start = new Date(today);
            start.setHours(startParts[0], startParts[1], 0, 0);

            let end = new Date(today);
            end.setHours(endParts[0], endParts[1], 0, 0);

            // Xử lý trường hợp xuyên đêm (End time nhỏ hơn Start time, ví dụ 22:00 -> 06:00)
            // Hoặc trường hợp kết thúc vào 00:00 (End time = Start time)
            if (end <= start) {
                end.setDate(end.getDate() + 1);
            }

            const basePrice = Number(price);
            let current = new Date(start);

            // Lặp chừng nào giờ bắt đầu hiện tại còn trước giờ kết thúc
            while (current < end) {
                const next = new Date(current);
                next.setHours(next.getHours() + 1);

                // Giờ kết thúc thực tế của slot phải là min(next full hour, global end time)
                // Điều chỉnh nextHour chỉ bằng end nếu nextHour vượt quá end
                const nextHour = (next > end) ? end : next;

                // Nếu giờ bắt đầu và giờ kết thúc slot trùng nhau (chỉ xảy ra nếu current = end), thì dừng
                if (current.getTime() === nextHour.getTime()) {
                    break;
                }

                let currentPrice;
                // Kiểm tra giờ vàng (Áp dụng nếu slot bắt đầu từ 17:00 trở đi)
                if (current.getHours() >= GOLDEN_HOUR_START) {
                    currentPrice = basePrice * GOLDEN_HOUR_MULTIPLIER;
                } else {
                    currentPrice = basePrice;
                }
                currentPrice = Math.round(currentPrice);

                const slotStart = current.toTimeString().substring(0, 5);
                const slotEnd = nextHour.toTimeString().substring(0, 5);

                slots.push({
                    start_time: slotStart,
                    end_time: slotEnd,
                    price: currentPrice
                });

                current = nextHour; // Chuyển sang giờ tiếp theo

                // Nếu đã đạt đến thời gian kết thúc (end), dừng
                if (current.getTime() === end.getTime()) {
                    break;
                }
            }

            return slots;
        }

        function updateInputNames() {
            let slotIndex = 0;
            container.find('.time-slot-row').each(function() {
                const $row = $(this);
                $row.find('.time-start').attr('name', `time_slots[${slotIndex}][start_time]`);
                $row.find('.time-end').attr('name', `time_slots[${slotIndex}][end_time]`);
                $row.find('.time-price').attr('name', `time_slots[${slotIndex}][price]`);
                slotIndex++;
            });
        }

        $(document).on('click', '#add-time-slot-btn', function(e) {
            e.preventDefault();
            container.append(createTimeSlotRow());
            updateInputNames();
        });

        $(document).on('click', '.remove-slot-btn', function(e) {
            e.preventDefault();
            $(this).closest('.time-slot-row').remove();
            updateInputNames();
        });

        $(document).on('change', '.time-start, .time-end, .time-price', function() {
            const $row = $(this).closest('.time-slot-row');
            const startTime = $row.find('.time-start').val();
            const endTime = $row.find('.time-end').val();
            const price = $row.find('.time-price').val();

            if (startTime && endTime && price) {
                const slots = splitTimeIntoHourlySlots(startTime, endTime, price);

                if (slots.length > 1) {
                    $row.remove(); // Xóa hàng hiện tại

                    // Thêm các slot 1 giờ
                    slots.forEach(slot => {
                        const newRow = $(createTimeSlotRow());
                        newRow.find('.time-start').val(slot.start_time);
                        newRow.find('.time-end').val(slot.end_time);
                        newRow.find('.time-price').val(slot.price);
                        container.append(newRow);
                    });

                    updateInputNames();
                }
            }
        });

        $('form').on('submit', function() {
            updateInputNames(); // Cập nhật tên input lần cuối trước khi gửi
        });

        // Tự động thêm một hàng khi trang tải
        if (container.children().length === 0) {
            $('#add-time-slot-btn').click();
        }
    });
</script>
@endpush