@extends('app')
<style>
    .custom-input {
        padding: 0.94rem 18px !important;
    }

    .custom-checkbox {
        margin-left: 0 !important;
    }

    .custom-checkbox2 {
        margin-left: 21px !important;
    }
</style>
@section('content')
    <div class="container-fluid py-5">
        {{-- Header --}}
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-5">
            <div>
                <h1 class="h3 fw-bold mb-1 text-dark">Tạo thương hiệu sân mới</h1>
                <p class="text-secondary mb-0">Nhập thông tin chi tiết cho thương hiệu sân.</p>
            </div>
            <a href="{{ route('owner.venues.index') }}" class="btn btn-outline-secondary mt-3 mt-md-0">
                <i class="fas fa-arrow-left me-2"></i> Quay lại danh sách
            </a>
        </div>

        {{-- Form --}}
        <form action="{{ route('owner.venues.store') }}" method="POST" class="needs-validation" novalidate>
            @csrf
            <div class="row g-4">
                {{-- Left Column --}}
                <div class="col-lg-8">
                    {{-- Basic Info Card --}}
                    <div class="card shadow-sm rounded-3 mb-4">
                        <div class="card-header">
                            <h5 class="mb-0 fw-bold">Thông tin cơ bản</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-4">
                                <label for="venue_name" class="form-label fw-semibold">Tên thương hiệu (sân)</label>
                                <input type="text" id="venue_name" name="name" class="form-control" required>
                                <div class="invalid-feedback">
                                    Vui lòng nhập tên thương hiệu.
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="owner_id" class="form-label fw-semibold">Chủ sở hữu</label>
                                <select id="owner_id" name="owner_id" class="form-select" required>
                                    <option value="" disabled selected>-- Chọn chủ sở hữu --</option>
                                    @foreach ($owners as $owner)
                                        <option value="{{ $owner->id }}">{{ $owner->name }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback">
                                    Vui lòng chọn chủ sở hữu.
                                </div>
                            </div>

                            <hr class="my-4">

                            <h6 class="fw-bold text-uppercase mb-3">Thông tin địa chỉ</h6>
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label for="province_id" class="form-label fw-semibold">Tỉnh/Thành</label>
                                    <select id="province_id" name="province_id" class="form-select" required>
                                        <option value="" disabled selected>-- Chọn Tỉnh/Thành --</option>
                                        @foreach ($provinces as $province)
                                            <option value="{{ $province->id }}">{{ $province->name }}</option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback">
                                        Vui lòng chọn Tỉnh/Thành.
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label for="district_id" class="form-label fw-semibold">Quận/Huyện</label>
                                    {{-- Tải động bằng JS --}}
                                    <select id="district_id" name="district_id" class="form-select" required>
                                        <option value="" disabled selected>-- Vui lòng chọn Tỉnh/Thành trước --
                                        </option>
                                    </select>
                                    <div class="invalid-feedback">
                                        Vui lòng chọn Quận/Huyện.
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="address_detail" class="form-label fw-semibold">Địa chỉ chi tiết</label>
                                <input type="text" id="address_detail" name="address_detail" class="form-control"
                                    required>
                                <div class="invalid-feedback">
                                    Vui lòng nhập địa chỉ chi tiết.
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Courts List Card --}}
                    <div class="card shadow-sm rounded-3 mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 fw-bold">Danh sách sân con</h5>
                            <button type="button" id="add-court-btn" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-plus me-1"></i> Thêm sân con
                            </button>
                        </div>
                        <div class="card-body" id="court-list-container">
                            {{-- Sân con sẽ được thêm vào đây bằng JS --}}
                            <p id="no-court-placeholder" class="text-center text-secondary mb-0">Chưa có sân con nào.
                                Nhấn "Thêm sân con" để bắt đầu.</p>
                        </div>
                    </div>
                </div>

                {{-- Right Column --}}
                <div class="col-lg-4">
                    <div class="card shadow-sm rounded-3 mb-4">
                        <div class="card-header">
                            <h5 class="mb-0 fw-bold">Thông tin bổ sung</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-4">
                                <label for="phone" class="form-label fw-semibold">Số điện thoại</label>
                                <input type="tel" id="phone" name="phone" class="form-control"
                                    placeholder="09xxxxxxxx" required>
                                <div class="invalid-feedback">
                                    Vui lòng nhập số điện thoại.
                                </div>
                            </div>

                            <div class="row g-3 mb-4">
                                <div class="col-6">
                                    <label for="start_time" class="form-label fw-semibold">Giờ mở cửa</label>
                                    <input type="time" id="start_time" name="start_time" class="form-control"
                                        value="06:00" required>
                                </div>
                                <div class="col-6">
                                    <label for="end_time" class="form-label fw-semibold">Giờ đóng cửa</label>
                                    <input type="time" id="end_time" name="end_time" class="form-control"
                                        value="22:00" required>
                                </div>
                            </div>

                            <label class="form-label fw-semibold d-block mb-2">Loại hình sân</label>
                            <div class="d-flex flex-wrap gap-3">
                                @foreach ($venue_types as $type)
                                    <div class="form-check">
                                        <input class="form-check-input venue-type-checkbox" type="checkbox"
                                            name="venue_types[]" value="{{ $type->id }}"
                                            id="venue_type_{{ $type->id }}">
                                        <label class="form-check-label"
                                            for="venue_type_{{ $type->id }}">{{ $type->name }}</label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Submit --}}
            <div class="text-center mt-4">
                <input type="hidden" name="is_active" value="0">
                <button type="submit" class="btn btn-primary btn-lg px-5 py-2">
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
                let current = new Date(start);

                while (current < end) {
                    const nextHour = new Date(current);
                    nextHour.setHours(nextHour.getHours() + 1);
                while (current < end) {
                    const nextHour = new Date(current);
                    nextHour.setHours(nextHour.getHours() + 1);

                    // Nếu slot tiếp theo vượt quá thời gian kết thúc, dừng lại
                    if (nextHour > end) {
                        break;
                    }
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
                    current = nextHour;
                }

                return slots;
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
                    rows.forEach((row, slotIdx) => {
                        const startInput = row.querySelector('.time-start');
                        const endInput = row.querySelector('.time-end');
                        const priceInput = row.querySelector('.time-price');

                        if (startInput) startInput.name =
                            `courts[${courtIdx}][time_slots][${slotIdx}][start_time]`;
                        if (endInput) endInput.name =
                            `courts[${courtIdx}][time_slots][${slotIdx}][end_time]`;
                        if (priceInput) priceInput.name =
                            `courts[${courtIdx}][time_slots][${slotIdx}][price]`;
                    });
                });
            }
                        if (startInput) startInput.name =
                            `courts[${courtIdx}][time_slots][${slotIdx}][start_time]`;
                        if (endInput) endInput.name =
                            `courts[${courtIdx}][time_slots][${slotIdx}][end_time]`;
                        if (priceInput) priceInput.name =
                            `courts[${courtIdx}][time_slots][${slotIdx}][price]`;
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
                    document.querySelectorAll('.court-type-select').forEach(select => {
                        const currentValue = select.value;
                        select.innerHTML = options;

                        // Nếu lựa chọn hiện tại vẫn còn trong danh sách, giữ nguyên
                        const stillExists = selectedTypes.some(type => type.id ===
                            currentValue);
                        if (stillExists) {
                            select.value = currentValue;
                        } else {
                            select.value = '';
                        }
                    });
                });
            });
                        // Nếu lựa chọn hiện tại vẫn còn trong danh sách, giữ nguyên
                        const stillExists = selectedTypes.some(type => type.id ===
                            currentValue);
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
                if (e.target.closest('.remove-slot')) {
                    e.target.closest('tr').remove();
                    updateTimeSlotNames();
                }

                if (e.target.closest('.remove-court')) {
                    e.target.closest('.court-item').remove();
                }
            });
                if (e.target.closest('.remove-court')) {
                    e.target.closest('.court-item').remove();
                }
            });

            // 👉 Sự kiện thay đổi thời gian - tự động chia slot
            document.addEventListener('change', e => {
                if (e.target.classList.contains('time-start') || e.target.classList.contains('time-end') ||
                    e.target.classList.contains('time-price')) {
                    const row = e.target.closest('tr');
                    const startTime = row.querySelector('.time-start').value;
                    const endTime = row.querySelector('.time-end').value;
                    const price = row.querySelector('.time-price').value;

                    if (startTime && endTime && price) {
                        const slots = splitTimeIntoHourlySlots(startTime, endTime, price);
                    if (startTime && endTime && price) {
                        const slots = splitTimeIntoHourlySlots(startTime, endTime, price);

                        if (slots.length > 1) {
                            const courtItem = row.closest('.court-item');
                            const tbody = courtItem.querySelector('tbody');
                            const courtIdx = Array.from(courtList.children).indexOf(courtItem);
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
