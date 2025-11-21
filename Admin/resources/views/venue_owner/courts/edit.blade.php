@extends('app')

@section('content')

<div class="container-fluid mt-4">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
            <h1 class="h4 mb-0">
                Chỉnh sửa sân: <strong>{{ $court->name }}</strong> - <strong>Thương hiệu: {{ $venue->name }}</strong>
            </h1>
            <a href="{{ route('owner.venues.courts.show', ['venue' => $venue->id, 'court' => $court->id]) }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> Quay lại
            </a>
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

            <form action="{{ route('owner.venues.courts.update', ['venue' => $court->venue_id, 'court' => $court->id]) }}" method="POST">
                @csrf
                @method('PUT')

                <input type="hidden" name="venue_id" value="{{ $venue->id }}">


                {{-- THÔNG TIN CƠ BẢN --}}
                <fieldset class="mb-4">
                    <legend class="h6 text-primary">1. Thông tin cơ bản</legend>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Tên sân <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name"
                                value="{{ old('name', $court->name) }}" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="venue_type_id" class="form-label">Loại hình <span class="text-danger">*</span></label>
                            <select class="form-select" id="venue_type_id" name="venue_type_id" required>
                                <option value="" disabled>-- Chọn loại hình --</option>
                                @foreach ($venue->venueTypes as $venueType)
                                <option value="{{ $venueType->id }}"
                                    {{ old('venue_type_id', $court->venue_type_id) == $venueType->id ? 'selected' : '' }}>
                                    {{ $venueType->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="is_indoor" class="form-label">Loại sân <span class="text-danger">*</span></label>
                            <select class="form-select" id="is_indoor" name="is_indoor" required>
                                <option value="1" {{ old('is_indoor', $court->is_indoor) == '1' ? 'selected' : '' }}>Trong nhà</option>
                                <option value="0" {{ old('is_indoor', $court->is_indoor) == '0' ? 'selected' : '' }}>Ngoài trời</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="surface" class="form-label">Bề mặt sân</label>
                            <input type="text" class="form-control" id="surface" name="surface"
                                value="{{ old('surface', $court->surface) }}" placeholder="Ví dụ: Cỏ nhân tạo, Sàn gỗ">
                        </div>
                    </div>
                </fieldset>

                {{-- KHUNG GIỜ & GIÁ --}}
                <fieldset class="mb-4">
                    <legend class="h6 text-primary">2. Khung giờ & Giá</legend>
                    <p class="text-muted small">
                        Cập nhật các khung giờ sẽ ảnh hưởng đến lịch hoạt động từ hôm nay trở đi. Các khung giờ đã được đặt trước sẽ được giữ lại.
                    </p>
                    <div class="p-3 bg-light rounded border">
                        <div class="row gx-2 mb-2 d-none d-md-flex small text-muted fw-bold">
                            <div class="col-md-3"><label>Giờ bắt đầu</label></div>
                            <div class="col-md-3"><label>Giờ kết thúc</label></div>
                            <div class="col-md-4"><label>Giá (VNĐ)</label></div>
                            <div class="col-md-2 text-end"><label>Hành động</label></div>
                        </div>
                        <div id="time-slots-container">
                            {{-- Rows sẽ được thêm bằng JS --}}
                        </div>
                    </div>

                    <button type="button" id="add-time-slot-btn" class="btn btn-outline-primary mt-3">
                        <i class="fas fa-plus me-1"></i> Thêm khung giờ
                    </button>
                </fieldset>

                <div class="card-footer bg-white text-end border-0 px-0 pt-4">
                    <button type="submit" class="btn btn-primary">Cập nhật</button>
                </div>
            </form>

        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const container = document.getElementById('time-slots-container');
        let index = 0;

        function regenerateIndex() {
            index = 0;
            document.querySelectorAll('.time-slot-row').forEach(row => {
                row.querySelector('.start').name = `time_slots[${index}][start_time]`;
                row.querySelector('.end').name = `time_slots[${index}][end_time]`;
                row.querySelector('.price').name = `time_slots[${index}][price]`;
                index++;
            });
        }

        // --- HÀM TẠO ROW (ĐÃ ĐIỀU CHỈNH HTML) ---
        function addRow(start = '', end = '', price = '') {
            const row = `
                <div class="row gx-2 align-items-center mb-2 time-slot-row p-2 bg-white rounded border">
                    <div class="col-md-3">
                        <label class="form-label d-md-none small">Giờ bắt đầu</label>
                        <input type="time" class="form-control form-control-sm start" value="${start}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label d-md-none small">Giờ kết thúc</label>
                        <input type="time" class="form-control form-control-sm end" value="${end}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label d-md-none small">Giá (VNĐ)</label>
                        <input type="number" class="form-control form-control-sm price" placeholder="Nhập giá (VNĐ)" value="${price}" min="0" step="1000" required>
                    </div>
                    <div class="col-md-2 d-flex align-items-center justify-content-end">
                        <button type="button" class="btn btn-sm btn-outline-danger remove-slot-btn" title="Xóa khung giờ">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>`;

            container.insertAdjacentHTML('beforeend', row);
            regenerateIndex();
        }

        // ----------------------------------------------------------------------
        // ✅ CẬP NHẬT LOGIC CHIA KHUNG GIỜ (Hỗ trợ 24:00 và Giờ vàng)
        // ----------------------------------------------------------------------
        function splitTimeIntoHourlySlots(startTime, endTime, price) {
            // CẤU HÌNH GIỜ VÀNG
            const GOLDEN_HOUR_START = 17; // 5 PM (17:00)
            const GOLDEN_HOUR_MULTIPLIER = 1.5;
            const slots = [];

            // Khởi tạo ngày/giờ ảo - Dùng 2000-01-01 làm mốc
            const baseDate = '2000-01-01';
            let start = new Date(`${baseDate}T${startTime}:00`);
            let end;

            // Xử lý 24:00 hoặc trường hợp qua đêm (End time <= Start time)
            if (endTime === '24:00') {
                end = new Date(`${baseDate}T00:00:00`);
                end.setDate(end.getDate() + 1); // 24:00 là 00:00 của ngày hôm sau
            } else {
                end = new Date(`${baseDate}T${endTime}:00`);
                if (end <= start) {
                    end.setDate(end.getDate() + 1); // Qua đêm
                }
            }

            // Nếu start và end bằng nhau sau khi xử lý (ví dụ: 00:00 - 00:00), cho phép chạy 24h
            if (start.getTime() === end.getTime()) {
                end.setDate(end.getDate() + 1);
            }


            const basePrice = Number(price);
            let current = new Date(start);

            // Lặp chừng nào giờ bắt đầu hiện tại còn trước giờ kết thúc
            while (current < end) {
                const next = new Date(current);
                next.setHours(next.getHours() + 1); // Tìm giờ tiếp theo

                // Giờ kết thúc thực tế của slot phải là min(next full hour, global end time)
                const nextHour = (next > end) ? end : next;

                // Nếu giờ bắt đầu và giờ kết thúc slot trùng nhau, thì dừng
                if (current.getTime() >= nextHour.getTime()) {
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

                // Format giờ HH:mm
                const slotStart = current.toTimeString().substring(0, 5);

                // Xử lý Giờ kết thúc cho trường hợp 24:00
                // Nếu nextHour là 00:00 của ngày hôm sau, hiển thị là '24:00'
                let slotEnd;
                if (nextHour.getHours() === 0 && nextHour.getMinutes() === 0 && nextHour.getDate() > current.getDate()) {
                    slotEnd = '24:00';
                } else {
                    slotEnd = nextHour.toTimeString().substring(0, 5);
                }


                slots.push({
                    start_time: slotStart,
                    end_time: slotEnd,
                    price: currentPrice
                });

                current = nextHour; // Chuyển sang giờ tiếp theo

                // Nếu đã đạt đến hoặc vượt quá thời gian kết thúc (end), dừng
                if (current.getTime() >= end.getTime()) {
                    break;
                }
            }
            return slots;
        }
        // ----------------------------------------------------------------------
        // KẾT THÚC LOGIC CHIA KHUNG GIỜ
        // ----------------------------------------------------------------------


        // Tải dữ liệu khung giờ cũ (từ $currentPricesDetailed được Controller truyền qua)
        const currentPrices = @json($currentPricesDetailed);
        if (currentPrices.length > 0) {
            currentPrices.forEach(slot => {
                // ✅ Sử dụng old() nếu có lỗi validation
                const oldStart = '{{ old('time_slots.${index}.start_time', 'placeholder') }}'.replace('placeholder', slot.start_time);
                const oldEnd = '{{ old('time_slots.${index}.end_time', 'placeholder') }}'.replace('placeholder', slot.end_time);
                const oldPrice = '{{ old('time_slots.${index}.price', 'placeholder') }}'.replace('placeholder', slot.price);
                
                // Chỉ thêm hàng nếu không phải là dữ liệu cũ bị trùng lặp
                if(oldStart !== 'placeholder' || oldEnd !== 'placeholder' || oldPrice !== 'placeholder') {
                    addRow(oldStart, oldEnd, oldPrice);
                } else {
                    addRow(slot.start_time, slot.end_time, slot.price);
                }
            });
        } else {
            // Tự động thêm một hàng khi không có dữ liệu cũ
            addRow();
        }


        document.getElementById('add-time-slot-btn').addEventListener('click', function() {
            addRow();
        });

        document.addEventListener('click', function(e) {
            if (e.target.closest('.remove-slot-btn')) {
                e.preventDefault();
                e.target.closest('.time-slot-row').remove();
                regenerateIndex();
            }
        });

        document.addEventListener('change', function(e) {
            const row = e.target.closest('.time-slot-row');
            if (!row) return;

            // Chỉ trigger khi thay đổi input thời gian/giá
            if (e.target.classList.contains('start') || e.target.classList.contains('end') || e.target.classList.contains('price')) {
                
                const start = row.querySelector('.start').value;
                const end = row.querySelector('.end').value;
                const price = row.querySelector('.price').value;

                if (start && end && price) {
                    const slots = splitTimeIntoHourlySlots(start, end, price);

                    // Chỉ chia khi slot lớn hơn 1 giờ
                    if (slots.length > 1) {
                        row.remove();
                        slots.forEach(s => addRow(s.start_time, s.end_time, s.price));
                        regenerateIndex();
                    }
                }
            }
        });

        // Cập nhật tên input lần cuối trước khi gửi form
        document.querySelector('form').addEventListener('submit', function() {
             regenerateIndex();
        });

    });
</script>
@endpush