@extends('app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="h4 mb-0">Tạo thương hiệu sân mới</h2>
        <p class="text-muted mb-0">Nhập thông tin chi tiết cho thương hiệu sân.</p>
    </div>
    <div>
        <a href="{{ route('owner.venues.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Quay lại danh sách
        </a>
    </div>
</div>

<form action="{{ route('owner.venues.store') }}" method="POST">
    @csrf
    <div class="row">
        {{-- Cột trái: Thông tin chính --}}
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Thông tin cơ bản</h5>
                </div>
                <div class="card-body">
                    {{-- Tên thương hiệu --}}
                    <div class="mb-3">
                        <label class="form-label fw-bold">Tên thương hiệu (sân) <span class="text-danger">*</span></label>
                        <input type="text" name="name"
                            class="form-control @error('name') is-invalid @enderror"
                            value="{{ old('name') }}" required>
                        @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Chủ sở hữu --}}
                    <div class="mb-3">
                        <label class="form-label fw-bold">Chủ sở hữu</label>
                        @if (auth()->user()->role->name === 'admin')
                        <select name="owner_id" class="form-select @error('owner_id') is-invalid @enderror" required>
                            <option value="">-- Chọn chủ sở hữu --</option>
                            @foreach ($owners as $owner)
                            <option value="{{ $owner->id }}" {{ old('owner_id') == $owner->id ? 'selected' : '' }}>
                                {{ $owner->name }} ({{ $owner->email }})
                            </option>
                            @endforeach
                        </select>
                        @error('owner_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        @else
                        <input type="text" class="form-control" value="{{ auth()->user()->name }}" disabled>
                        @endif
                    </div>

                    <hr class="my-4">
                    <h6 class="fw-bold">Thông tin địa chỉ</h6>

                    <div class="row">
                        {{-- Tỉnh/Thành --}}
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tỉnh/Thành <span class="text-danger">*</span></label>
                            <select name="province_id" id="province_id" class="form-select @error('province_id') is-invalid @enderror" required>
                                <option value="">-- Chọn Tỉnh/Thành --</option>
                                @foreach ($provinces as $province)
                                <option value="{{ $province->id }}" {{ old('province_id') == $province->id ? 'selected' : '' }}>
                                    {{ $province->name }}
                                </option>
                                @endforeach
                            </select>
                            @error('province_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Quận/Huyện --}}
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Quận/Huyện <span class="text-danger">*</span></label>
                            <select name="district_id" id="district_id" class="form-select @error('district_id') is-invalid @enderror" required disabled>
                                <option value="">-- Vui lòng chọn Tỉnh/Thành trước --</option>
                            </select>
                            @error('district_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- Địa chỉ chi tiết --}}
                    <div class="mb-3">
                        <label class="form-label">Địa chỉ chi tiết <span class="text-danger">*</span></label>
                        <input type="text" name="address_detail"
                            value="{{ old('address_detail') }}"
                            class="form-control @error('address_detail') is-invalid @enderror" required>
                        @error('address_detail')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
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
                    {{-- Loop hiển thị lại dữ liệu cũ khi validate fail --}}
                    @if (old('courts'))
                    @foreach (old('courts') as $courtIndex => $court)
                    <div class="border rounded p-3 mb-3 court-item" data-index="{{ $courtIndex }}">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="mb-0 fw-bold">Sân #<span class="court-number">{{ $courtIndex + 1 }}</span></h6>
                            <button type="button" class="btn btn-sm btn-danger remove-court"><i class="fas fa-times"></i></button>
                        </div>
                        <div class="row">
                            {{-- Tên sân con --}}
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tên sân</label>
                                <input type="text" name="courts[{{ $courtIndex }}][name]"
                                    value="{{ $court['name'] ?? '' }}"
                                    class="form-control @error(" courts.{$courtIndex}.name") is-invalid @enderror"
                                    required>
                                @error("courts.{$courtIndex}.name")
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            {{-- Loại sân con --}}
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Loại sân</label>
                                <select name="courts[{{ $courtIndex }}][venue_type_id]"
                                    class="form-select court-type-select @error(" courts.{$courtIndex}.venue_type_id") is-invalid @enderror"
                                    required>
                                    <option value="">-- Chọn loại hình --</option>
                                    @foreach ($venue_types as $type)
                                    <option value="{{ $type->id }}" {{ ($court['venue_type_id'] ?? '') == $type->id ? 'selected' : '' }}>
                                        {{ $type->name }}
                                    </option>
                                    @endforeach
                                </select>
                                @error("courts.{$courtIndex}.venue_type_id")
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="row">
                            {{-- Mặt sân --}}
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Mặt sân</label>
                                <input type="text" name="courts[{{ $courtIndex }}][surface]"
                                    value="{{ $court['surface'] ?? '' }}"
                                    class="form-control @error(" courts.{$courtIndex}.surface") is-invalid @enderror"
                                    placeholder="Cỏ nhân tạo, cỏ tự nhiên...">
                                @error("courts.{$courtIndex}.surface")
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            {{-- Trong nhà/Ngoài trời --}}
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Trong nhà / Ngoài trời</label>
                                <select name="courts[{{ $courtIndex }}][is_indoor]"
                                    class="form-select @error(" courts.{$courtIndex}.is_indoor") is-invalid @enderror">
                                    <option value="0" {{ ($court['is_indoor'] ?? '0') == '0' ? 'selected' : '' }}>Ngoài trời</option>
                                    <option value="1" {{ ($court['is_indoor'] ?? '0') == '1' ? 'selected' : '' }}>Trong nhà</option>
                                </select>
                                @error("courts.{$courtIndex}.is_indoor")
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <h6 class="fw-bold mt-3 d-flex justify-content-between align-items-center">
                            <span>Khung giờ và giá</span>
                            <button type="button" class="btn btn-sm btn-outline-success add-time-slot"><i class="fas fa-plus"></i> Thêm khung giờ</button>
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
                                <tbody>
                                    @if (!empty($court['time_slots']))
                                    @foreach ($court['time_slots'] as $slotIndex => $slot)
                                    <tr class="@if ($errors->has(" courts.{$courtIndex}.time_slots.{$slotIndex}.*")) table-danger @endif">
                                        <td>
                                            <input type="time"
                                                name="courts[{{ $courtIndex }}][time_slots][{{ $slotIndex }}][start_time]"
                                                value="{{ $slot['start_time'] ?? '' }}"
                                                class="form-control form-control-sm time-start @error(" courts.{$courtIndex}.time_slots.{$slotIndex}.start_time") is-invalid @enderror"
                                                required>
                                            @error("courts.{$courtIndex}.time_slots.{$slotIndex}.start_time")
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </td>
                                        <td>
                                            <input type="time"
                                                name="courts[{{ $courtIndex }}][time_slots][{{ $slotIndex }}][end_time]"
                                                value="{{ $slot['end_time'] ?? '' }}"
                                                class="form-control form-control-sm time-end @error(" courts.{$courtIndex}.time_slots.{$slotIndex}.end_time") is-invalid @enderror"
                                                required>
                                            @error("courts.{$courtIndex}.time_slots.{$slotIndex}.end_time")
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </td>
                                        <td>
                                            <input type="number"
                                                name="courts[{{ $courtIndex }}][time_slots][{{ $slotIndex }}][price]"
                                                value="{{ $slot['price'] ?? '' }}"
                                                class="form-control form-control-sm time-price @error(" courts.{$courtIndex}.time_slots.{$slotIndex}.price") is-invalid @enderror"
                                                required>
                                            @error("courts.{$courtIndex}.time_slots.{$slotIndex}.price")
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </td>
                                        <td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger remove-slot"><i class="fas fa-trash"></i></button></td>
                                    </tr>
                                    @endforeach
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endforeach
                    @endif
                </div>
            </div>
        </div>

        {{-- Cột phải: Thông tin bổ sung --}}
        <div class="col-lg-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Thông tin bổ sung</h5>
                </div>
                <div class="card-body">
                    {{-- Số điện thoại --}}
                    <div class="mb-3">
                        <label class="form-label fw-bold">Số điện thoại</label>
                        <input type="tel" name="phone"
                            value="{{ old('phone') }}"
                            class="form-control @error('phone') is-invalid @enderror"
                            placeholder="09xxxxxxxx">
                        @error('phone')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        {{-- Giờ mở cửa --}}
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Giờ mở cửa</label>
                            <input type="time" name="start_time"
                                class="form-control custom-input @error('start_time') is-invalid @enderror"
                                value="{{ old('start_time', '06:00') }}">
                            @error('start_time')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        {{-- Giờ đóng cửa --}}
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Giờ đóng cửa</label>
                            <input type="time" name="end_time"
                                class="form-control custom-input @error('end_time') is-invalid @enderror"
                                value="{{ old('end_time', '22:00') }}">
                            @error('end_time')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <label class="form-label fw-bold d-block">Loại hình sân</label>
                    <div class="border rounded p-2 @error('venue_types') border-danger @enderror">
                        @foreach ($venue_types as $type)
                        <div class="form-check form-check-inline">
                            <input class="form-check-input venue-type-checkbox custom-checkbox" type="checkbox"
                                name="venue_types[]" id="venue_type_{{ $type->id }}"
                                value="{{ $type->id }}"
                                {{ is_array(old('venue_types')) && in_array($type->id, old('venue_types')) ? 'checked' : '' }}>

                            <label class="form-check-label custom-checkbox2" for="venue_type_{{ $type->id }}">
                                {{ $type->name }}
                            </label>
                        </div>
                        @endforeach
                    </div>
                    @error('venue_types')
                    <div class="text-danger mt-1 small">
                        <i class="fas fa-exclamation-circle"></i> {{ $message }}
                    </div>
                    @enderror
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

{{-- ✅ JS: Thêm sân + khung giờ + tự động cập nhật loại sân  --}}
<script>
    document.addEventListener('DOMContentLoaded', () => {
        let courtIndex = 0;
        const courtList = document.getElementById('court-list');
        const addCourtBtn = document.getElementById('add-court-btn');


        function getSelectedVenueTypes() {
            const checkedBoxes = document.querySelectorAll('.venue-type-checkbox:checked');
            return Array.from(checkedBoxes).map(cb => ({
                id: cb.value,
                name: cb.nextElementSibling.textContent.trim()
            }));
        }


        function renderVenueTypeOptions(selectedTypes) {
            if (selectedTypes.length === 0) {
                return `<option value="">-- Chưa chọn loại hình sân ở trên --</option>`;
            }
            return selectedTypes.map(type => `<option value="${type.id}">${type.name}</option>`).join('');
        }

        function splitTimeIntoHourlySlots(startTime, endTime, price) {
            // CẤU HÌNH GIỜ VÀNG
            const GOLDEN_HOUR_START = 17;
            const GOLDEN_HOUR_MULTIPLIER = 1.5;
            const slots = [];
            const start = new Date('2000-01-01 ' + startTime);
            const end = new Date('2000-01-01 ' + endTime);
            const basePrice = Number(price);

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


                let currentPrice;
                // Kiểm tra xem giờ bắt đầu của slot có phải là giờ vàng không
                if (current.getHours() >= GOLDEN_HOUR_START) {
                    // Nếu đúng, nhân giá với hệ số 1.5
                    currentPrice = basePrice * GOLDEN_HOUR_MULTIPLIER;
                } else {
                    // Nếu không, giữ nguyên giá gốc
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

                current = nextHour;
            }

            return slots;
        }


        // Hàm cập nhật tên input cho time slots
        function updateTimeSlotNames() {
            document.querySelectorAll('.court-item').forEach((courtItem, courtIdx) => {
                const tbody = courtItem.querySelector('tbody');
                const rows = tbody.querySelectorAll('tr');

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

        //  Thêm sân mới
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

        // Tự động cập nhật dropdown loại sân khi thay đổi checkbox
        document.querySelectorAll('.venue-type-checkbox').forEach(cb => {
            cb.addEventListener('change', () => {
                const selectedTypes = getSelectedVenueTypes();
                const options = renderVenueTypeOptions(selectedTypes);

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

        // Quản lý thêm/xóa khung giờ và sân
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

        // Sự kiện thay đổi thời gian - tự động chia slot
        document.addEventListener('change', e => {
            if (e.target.classList.contains('time-start') || e.target.classList.contains('time-end') ||
                e.target.classList.contains('time-price')) {
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

        document.querySelector('form').addEventListener('submit', () => {
            updateTimeSlotNames();
        });

    });
</script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        // Khi người dùng thay đổi lựa chọn trong ô Tỉnh/Thành
        $('#province_id').on('change', function() {
            var provinceId = $(this).val(); // Lấy ID của tỉnh/thành đã chọn
            var districtSelect = $('#district_id'); // Tham chiếu đến ô quận/huyện

            // Xóa các lựa chọn cũ và vô hiệu hóa
            districtSelect.html('<option value="">-- Đang tải... --</option>');
            districtSelect.prop('disabled', true);

            // Nếu đã chọn một tỉnh/thành hợp lệ
            if (provinceId) {
                // Gửi yêu cầu AJAX đến server
                $.ajax({
                    url: '/api/districts/' + provinceId,
                    type: 'GET',
                    dataType: 'json',
                    success: function(data) {
                        // Khi nhận được dữ liệu thành công
                        districtSelect.prop('disabled', false); // Kích hoạt lại ô
                        districtSelect.html(
                            '<option value="" disabled selected>-- Chọn Quận/Huyện --</option>'
                        );
                        // Lặp qua dữ liệu trả về và thêm vào ô select
                        $.each(data, function(key, value) {
                            districtSelect.append('<option value="' + value.id +
                                '">' + value.name + '</option>');
                        });
                    },
                    error: function() {
                        districtSelect.html(
                            '<option value="">-- Có lỗi xảy ra --</option>');
                        console.error('Lỗi khi tải danh sách quận/huyện.');
                    }
                });
            } else {
                districtSelect.html('<option value="">-- Vui lòng chọn Tỉnh/Thành trước --</option>');
                districtSelect.prop('disabled', true);
            }
        });
    });
</script>
@endsection