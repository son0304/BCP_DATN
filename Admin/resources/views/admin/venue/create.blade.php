@extends('app')

@section('content')
<div class="container-fluid py-4">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h4 mb-0">Tạo thương hiệu sân mới</h2>
            <p class="text-muted mb-0">Nhập thông tin chi tiết cho thương hiệu sân.</p>
        </div>
        <div>
            <a href="{{ route('brand.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Quay lại danh sách
            </a>
        </div>
    </div>

    {{-- Form --}}
    <form action="{{ route('brand.store') }}" method="POST">
        @csrf
        <div class="row">
            {{-- Cột trái --}}
            <div class="col-lg-8">
                {{-- Thông tin cơ bản --}}
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Thông tin cơ bản</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Tên thương hiệu (sân)</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Chủ sở hữu</label>
                            <select name="owner_id" class="form-select" required>
                                <option value="">-- Chọn chủ sở hữu --</option>
                                @foreach($owners as $owner)
                                    <option value="{{ $owner->id }}">{{ $owner->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <hr class="my-4">
                        <h6 class="fw-bold">Thông tin địa chỉ</h6>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tỉnh/Thành</label>
                                <select name="province_id" id="province_id" class="form-select" required>
                                    <option value="">-- Chọn Tỉnh/Thành --</option>
                                    @foreach($provinces as $province)
                                        <option value="{{ $province->id }}">{{ $province->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Quận/Huyện</label>
                                <select name="district_id" id="district_id" class="form-select" required>
                                    <option value="">-- Chọn Quận/Huyện --</option>
                                    @foreach($districts as $district)
                                        <option value="{{ $district->id }}">{{ $district->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Địa chỉ chi tiết</label>
                            <input type="text" name="address_detail" class="form-control" required>
                        </div>
                    </div>
                </div>

                {{-- 💡 DANH SÁCH SÂN --}}
                <div class="card shadow-sm mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Danh sách sân</h5>
                        <button type="button" id="add-court-btn" class="btn btn-sm btn-success">
                            <i class="fas fa-plus"></i> Thêm sân
                        </button>
                    </div>
                    <div class="card-body" id="court-list">
                        {{-- Các sân sẽ được thêm động tại đây --}}
                    </div>
                </div>
            </div>

            {{-- Cột phải --}}
            <div class="col-lg-4">
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Thông tin bổ sung</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Số điện thoại</label>
                            <input type="tel" name="phone" class="form-control" placeholder="09xxxxxxxx">
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Giờ mở cửa</label>
                                <input type="time" name="start_time" class="form-control" value="06:00">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Giờ đóng cửa</label>
                                <input type="time" name="end_time" class="form-control" value="22:00">
                            </div>
                        </div>

                        <label class="form-label fw-bold d-block">Loại hình sân</label>
                        @foreach($venue_types as $type)
                            <div class="form-check form-check-inline">
                                <input class="form-check-input venue-type-checkbox" type="checkbox" name="venue_types[]" value="{{ $type->id }}">
                                <label class="form-check-label">{{ $type->name }}</label>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div class="text-center mt-4">
            <input type="hidden" name="is_active" value="0">
            <button type="submit" class="btn btn-primary px-4 py-2">
                <i class="fas fa-save me-2"></i> Lưu và tạo mới
            </button>
        </div>
    </form>
</div>

{{-- ✅ JS: Thêm sân + khung giờ + tự động cập nhật loại sân --}}
<script>
document.addEventListener('DOMContentLoaded', () => {
    let courtIndex = 0;
    const courtList = document.getElementById('court-list');
    const addCourtBtn = document.getElementById('add-court-btn');

    // 👉 Lấy danh sách loại hình sân được tick
    function getSelectedVenueTypes() {
        const checkedBoxes = document.querySelectorAll('.venue-type-checkbox:checked');
        return Array.from(checkedBoxes).map(cb => ({
            id: cb.value,
            name: cb.nextElementSibling.textContent.trim()
        }));
    }

    // 👉 Sinh danh sách <option> loại sân
    function renderVenueTypeOptions(selectedTypes) {
        if (selectedTypes.length === 0) {
            return `<option value="">-- Chưa chọn loại hình sân ở trên --</option>`;
        }
        return selectedTypes.map(type => `<option value="${type.id}">${type.name}</option>`).join('');
    }

    // 👉 Hàm chia thời gian thành các slot 1 giờ
    function splitTimeIntoHourlySlots(startTime, endTime, price) {
        const slots = [];
        const start = new Date('2000-01-01 ' + startTime);
        const end = new Date('2000-01-01 ' + endTime);

        // Nếu thời gian kết thúc là ngày hôm sau (ví dụ: 23:00 - 01:00)
        if (end <= start) {
            end.setDate(end.getDate() + 1);
        }

        let current = new Date(start);

        while (current < end) {
            const nextHour = new Date(current);
            nextHour.setHours(nextHour.getHours() + 1);

            // Nếu slot tiếp theo vượt quá thời gian kết thúc, dừng lại
            if (nextHour > end) {
                break;
            }

            const slotStart = current.toTimeString().substring(0, 5);
            const slotEnd = nextHour.toTimeString().substring(0, 5);

            slots.push({
                start_time: slotStart,
                end_time: slotEnd,
                price: price
            });

            current = nextHour;
        }

        return slots;
    }

    // 👉 Hàm cập nhật tên input cho time slots
    function updateTimeSlotNames() {
        document.querySelectorAll('.court-item').forEach((courtItem, courtIdx) => {
            const tbody = courtItem.querySelector('tbody');
            const rows = tbody.querySelectorAll('tr');

            rows.forEach((row, slotIdx) => {
                const startInput = row.querySelector('.time-start');
                const endInput = row.querySelector('.time-end');
                const priceInput = row.querySelector('.time-price');

                if (startInput) startInput.name = `courts[${courtIdx}][time_slots][${slotIdx}][start_time]`;
                if (endInput) endInput.name = `courts[${courtIdx}][time_slots][${slotIdx}][end_time]`;
                if (priceInput) priceInput.name = `courts[${courtIdx}][time_slots][${slotIdx}][price]`;
            });
        });
    }

    // 👉 Thêm sân mới
    addCourtBtn.addEventListener('click', () => {
        const options = renderVenueTypeOptions(getSelectedVenueTypes());

        const newCourt = `
        <div class="border rounded p-3 mb-3 court-item">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="mb-0 fw-bold">Sân #${courtIndex + 1}</h6>
                <button type="button" class="btn btn-sm btn-danger remove-court">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Tên sân</label>
                    <input type="text" name="courts[${courtIndex}][name]" class="form-control" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Loại sân</label>
                    <select name="courts[${courtIndex}][venue_type_id]" class="form-select court-type-select" required>
                        ${options}
                    </select>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Mặt sân</label>
                    <input type="text" name="courts[${courtIndex}][surface]" class="form-control" placeholder="Cỏ nhân tạo, cỏ tự nhiên...">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Trong nhà</label>
                    <select name="courts[${courtIndex}][is_indoor]" class="form-select">
                        <option value="0">Ngoài trời</option>
                        <option value="1">Trong nhà</option>
                    </select>
                </div>
            </div>

            <h6 class="fw-bold mt-3 d-flex justify-content-between align-items-center">
                <span>Khung giờ và giá</span>
                <button type="button" class="btn btn-sm btn-outline-success add-time-slot">
                    <i class="fas fa-plus"></i> Thêm khung giờ
                </button>
            </h6>

            <div class="table-responsive mt-2">
                <table class="table table-bordered table-sm align-middle time-slot-table">
                    <thead>
                        <tr class="bg-light">
                            <th>Giờ bắt đầu</th>
                            <th>Giờ kết thúc</th>
                            <th>Giá (VNĐ)</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>`;
        courtList.insertAdjacentHTML('beforeend', newCourt);
        courtIndex++;
        updateTimeSlotNames();
    });

    // 👉 Tự động cập nhật dropdown loại sân khi thay đổi checkbox
    document.querySelectorAll('.venue-type-checkbox').forEach(cb => {
        cb.addEventListener('change', () => {
            const selectedTypes = getSelectedVenueTypes();
            const options = renderVenueTypeOptions(selectedTypes);

            document.querySelectorAll('.court-type-select').forEach(select => {
                const currentValue = select.value;
                select.innerHTML = options;

                // Nếu lựa chọn hiện tại vẫn còn trong danh sách, giữ nguyên
                const stillExists = selectedTypes.some(type => type.id === currentValue);
                if (stillExists) {
                    select.value = currentValue;
                } else {
                    select.value = '';
                }
            });
        });
    });

    // 👉 Quản lý thêm/xóa khung giờ và sân
    document.addEventListener('click', e => {
        if (e.target.closest('.add-time-slot')) {
            const courtItem = e.target.closest('.court-item');
            const tbody = courtItem.querySelector('tbody');
            const courtIdx = Array.from(courtList.children).indexOf(courtItem);
            const timeSlotIndex = tbody.children.length;

            tbody.insertAdjacentHTML('beforeend', `
                <tr>
                    <td><input type="time" class="form-control form-control-sm time-start" required></td>
                    <td><input type="time" class="form-control form-control-sm time-end" required></td>
                    <td><input type="number" class="form-control form-control-sm time-price" required></td>
                    <td class="text-center">
                        <button type="button" class="btn btn-sm btn-outline-danger remove-slot"><i class="fas fa-trash"></i></button>
                    </td>
                </tr>
            `);
            updateTimeSlotNames();
        }

        if (e.target.closest('.remove-slot')) {
            e.target.closest('tr').remove();
            updateTimeSlotNames();
        }

        if (e.target.closest('.remove-court')) {
            e.target.closest('.court-item').remove();
        }
    });

    // 👉 Sự kiện thay đổi thời gian - tự động chia slot
    document.addEventListener('change', e => {
        if (e.target.classList.contains('time-start') || e.target.classList.contains('time-end') || e.target.classList.contains('time-price')) {
            const row = e.target.closest('tr');
            const startTime = row.querySelector('.time-start').value;
            const endTime = row.querySelector('.time-end').value;
            const price = row.querySelector('.time-price').value;

            if (startTime && endTime && price) {
                const slots = splitTimeIntoHourlySlots(startTime, endTime, price);

                if (slots.length > 1) {
                    const courtItem = row.closest('.court-item');
                    const tbody = courtItem.querySelector('tbody');
                    const courtIdx = Array.from(courtList.children).indexOf(courtItem);

                    // Xóa hàng hiện tại
                    row.remove();

                    // Thêm các slot 1 giờ
                    slots.forEach((slot, slotIdx) => {
                        tbody.insertAdjacentHTML('beforeend', `
                            <tr>
                                <td><input type="time" class="form-control form-control-sm time-start" value="${slot.start_time}" required></td>
                                <td><input type="time" class="form-control form-control-sm time-end" value="${slot.end_time}" required></td>
                                <td><input type="number" class="form-control form-control-sm time-price" value="${slot.price}" required></td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-outline-danger remove-slot"><i class="fas fa-trash"></i></button>
                                </td>
                            </tr>
                        `);
                    });

                    updateTimeSlotNames();
                }
            }
        }
    });

    // 👉 Sự kiện submit form - cập nhật tên input cuối cùng
    document.querySelector('form').addEventListener('submit', () => {
        updateTimeSlotNames();
    });
});
</script>
@endsection
