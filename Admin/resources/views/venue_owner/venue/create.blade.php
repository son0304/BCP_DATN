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
    <div class="container-fluid py-4">
        {{-- Header --}}
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
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <form action="{{ route('owner.venues.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="row">
                {{-- CỘT TRÁI --}}
                <div class="col-lg-8">
                    {{--  THÔNG TIN CƠ BẢN --}}
                    <div class="card shadow-sm mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Thông tin cơ bản</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Tên thương hiệu (sân)</label>
                                <input type="text" name="name" class="form-control" value="{{ old('name') }}">
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Chủ sở hữu</label>
                                @if (auth()->user()->role->name === 'admin')
                                    <select name="owner_id" class="form-select">
                                        <option value="">-- Chọn chủ sở hữu --</option>
                                        @foreach ($owners as $owner)
                                            <option value="{{ $owner->id }}">{{ $owner->name }} ({{ $owner->email }})
                                            </option>
                                        @endforeach
                                    </select>
                                @else
                                    <input type="text" class="form-control" value="{{ auth()->user()->name }}" disabled>
                                @endif
                            </div>

                            <hr class="my-4">
                            <h6 class="fw-bold">Thông tin địa chỉ (API Tự động)</h6>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Tỉnh/Thành</label>
                                    <select name="province_id" data-old="{{ old('province_id') }}" id="province_id"
                                        class="form-select">
                                        <option value="">-- Đang tải... --</option>
                                    </select>
                                    <input type="hidden" name="province_name" id="province_name">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Quận/Huyện</label>
                                    <select name="district_id" disabled data-old="{{ old('district_id') }}" id="district_id"
                                        class="form-select">
                                        <option value="">-- Chọn Tỉnh/Thành trước --</option>
                                    </select>
                                    <input type="hidden" name="district_name" id="district_name">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Địa chỉ chi tiết</label>
                                <input type="text" name="address_detail" value="{{ old('address_detail') }}"
                                    class="form-control">
                            </div>
                        </div>
                    </div>

                    {{--  DANH SÁCH SÂN --}}
                    <div class="card shadow-sm mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">Danh sách sân</h5>
                            <button type="button" id="add-court-btn" class="btn btn-sm btn-success">
                                <i class="fas fa-plus"></i> Thêm sân
                            </button>
                        </div>
                        <div class="card-body" id="court-list">
                            {{-- Hiển thị lại dữ liệu cũ nếu Validate lỗi --}}
                            @if (old('courts'))
                                @foreach (old('courts') as $courtIndex => $court)
                                    <div class="border rounded p-3 mb-3 court-item" data-index="{{ $courtIndex }}">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <h6 class="mb-0 fw-bold">Sân #<span
                                                    class="court-number">{{ $courtIndex + 1 }}</span></h6>
                                            <button type="button" class="btn btn-sm btn-danger remove-court"><i
                                                    class="fas fa-times"></i></button>
                                        </div>


                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Tên sân</label>
                                                <input type="text" name="courts[{{ $courtIndex }}][name]"
                                                    value="{{ $court['name'] ?? '' }}"
                                                    class="form-control @error("courts.{$courtIndex}.name") is-invalid @enderror">
                                                @error("courts.{$courtIndex}.name")
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Loại sân</label>
                                                <select name="courts[{{ $courtIndex }}][venue_type_id]"
                                                    class="form-select court-type-select">
                                                    <option value="">-- Chọn loại hình --</option>
                                                    @foreach ($venue_types as $type)
                                                        <option value="{{ $type->id }}"
                                                            {{ ($court['venue_type_id'] ?? '') == $type->id ? 'selected' : '' }}>
                                                            {{ $type->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>

                                        <div class="row bg-light p-2 mb-3 mx-0 rounded border">
                                            <div class="col-md-5 mb-2">
                                                <label class="form-label fw-bold">Ảnh đại diện (1 ảnh)</label>
                                                <input type="file" name="courts[{{ $courtIndex }}][avatar]"
                                                    class="form-control court-avatar-input" accept="image/*">
                                            </div>
                                            <div class="col-md-7 mb-2">
                                                <label class="form-label fw-bold">Album ảnh (Nhiều ảnh)</label>
                                                <input type="file" name="courts[{{ $courtIndex }}][images][]"
                                                    class="form-control court-images-input" accept="image/*" multiple>
                                            </div>
                                            <div class="row g-2 mt-2 court-image-preview-container">
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Mặt sân</label>
                                                <input type="text" name="courts[{{ $courtIndex }}][surface]"
                                                    value="{{ $court['surface'] ?? '' }}" class="form-control">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Trong nhà</label>
                                                <select name="courts[{{ $courtIndex }}][is_indoor]"
                                                    class="form-select">
                                                    <option value="0"
                                                        {{ ($court['is_indoor'] ?? '0') == '0' ? 'selected' : '' }}>Ngoài
                                                        trời</option>
                                                    <option value="1"
                                                        {{ ($court['is_indoor'] ?? '0') == '1' ? 'selected' : '' }}>Trong
                                                        nhà</option>
                                                </select>
                                            </div>
                                        </div>

                                        {{-- Time Slots --}}
                                        <h6 class="fw-bold mt-3 d-flex justify-content-between align-items-center">
                                            <span>Khung giờ và giá</span>
                                            <button type="button" class="btn btn-sm btn-outline-success add-time-slot"><i
                                                    class="fas fa-plus"></i> Thêm khung giờ</button>
                                        </h6>
                                        <div class="table-responsive mt-2">
                                            <table class="table table-bordered table-sm align-middle time-slot-table">
                                                <thead>
                                                    <tr class="bg-light">
                                                        <th>Bắt đầu</th>
                                                        <th>Kết thúc</th>
                                                        <th>Giá (VNĐ)</th>
                                                        <th></th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @if (!empty($court['time_slots']))
                                                        @foreach ($court['time_slots'] as $slotIndex => $slot)
                                                            <tr>
                                                                <td>
                                                                    <input type="time"
                                                                        name="courts[{{ $courtIndex }}][time_slots][{{ $slotIndex }}][start_time]"
                                                                        value="{{ $slot['start_time'] ?? '' }}"
                                                                        class="form-control form-control-sm time-start @error("courts.{$courtIndex}.time_slots.{$slotIndex}.start_time") is-invalid @enderror">
                                                                    @error("courts.{$courtIndex}.time_slots.{$slotIndex}.start_time")
                                                                        <div class="invalid-feedback">{{ $message }}
                                                                        </div>
                                                                    @enderror
                                                                </td>
                                                                <td>
                                                                    <input type="time"
                                                                        name="courts[{{ $courtIndex }}][time_slots][{{ $slotIndex }}][end_time]"
                                                                        value="{{ $slot['end_time'] ?? '' }}"
                                                                        class="form-control form-control-sm time-end @error("courts.{$courtIndex}.time_slots.{$slotIndex}.end_time") is-invalid @enderror">
                                                                    @error("courts.{$courtIndex}.time_slots.{$slotIndex}.end_time")
                                                                        <div class="invalid-feedback">{{ $message }}
                                                                        </div>
                                                                    @enderror
                                                                </td>
                                                                <td>
                                                                    <input type="number"
                                                                        name="courts[{{ $courtIndex }}][time_slots][{{ $slotIndex }}][price]"
                                                                        value="{{ $slot['price'] ?? '' }}"
                                                                        class="form-control form-control-sm time-price @error("courts.{$courtIndex}.time_slots.{$slotIndex}.price") is-invalid @enderror">
                                                                    @error("courts.{$courtIndex}.time_slots.{$slotIndex}.price")
                                                                        <div class="invalid-feedback">{{ $message }}
                                                                        </div>
                                                                    @enderror
                                                                </td>

                                                                <td class="text-center">
                                                                    <button type="button"
                                                                        class="btn btn-sm btn-outline-danger remove-slot">
                                                                        <i class="fas fa-trash"></i>
                                                                    </button>
                                                                </td>
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

                {{-- CỘT PHẢI --}}
                <div class="col-lg-4">
                    <div class="card shadow-sm mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Thông tin bổ sung</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Số điện thoại</label>
                                <input type="tel" name="phone" value="{{ old('phone') }}" class="form-control"
                                    placeholder="09xxxxxxxx">
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Giờ mở cửa</label>
                                    <input type="time" name="start_time" class="form-control custom-input"
                                        value="{{ old('start_time', '06:00') }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Giờ đóng cửa</label>
                                    <input type="time" name="end_time" class="form-control custom-input"
                                        value="{{ old('end_time', '22:00') }}">
                                </div>
                            </div>
                            <label class="form-label fw-bold d-block">Loại hình sân</label>
                            @foreach ($venue_types as $type)
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input venue-type-checkbox custom-checkbox" type="checkbox"
                                        name="venue_types[]" id="venue_type_{{ $type->id }}"
                                        value="{{ $type->id }}"
                                        {{ is_array(old('venue_types')) && in_array($type->id, old('venue_types')) ? 'checked' : '' }}>
                                    <label class="form-check-label custom-checkbox2"
                                        for="venue_type_{{ $type->id }}">
                                        {{ $type->name }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-center mt-4">
                <input type="hidden" name="is_active" value="0">
                <button type="submit" class="btn btn-primary px-4 py-2"><i class="fas fa-save me-2"></i> Lưu và tạo
                    mới</button>
            </div>
        </form>
    </div>
    <div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content bg-transparent border-0">
                <div class="modal-body p-0">
                    <img id="modalImage" src="" class="img-fluid rounded shadow-lg"
                        style="max-height: 90vh; width: auto; display: block; margin: 0 auto;">
                </div>
                <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3"
                    data-bs-dismiss="modal" aria-label="Close" style="z-index: 1051;"></button>
            </div>
        </div>
    </div>

    {{-- ✅ JS: Thêm sân + khung giờ + tự động cập nhật loại sân  --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.21.1/axios.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const courtList = document.getElementById('court-list');
            const addCourtBtn = document.getElementById('add-court-btn');
            let courtIndex = document.querySelectorAll('.court-item').length;


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

            function updateTimeSlotNames() {
                document.querySelectorAll('.court-item').forEach((courtItem, courtIdx) => {

                    const nameInput = courtItem.querySelector('input[name*="[name]"]');
                    if (nameInput) nameInput.name = `courts[${courtIdx}][name]`;


                    const typeSelect = courtItem.querySelector('select[name*="[venue_type_id]"]');
                    if (typeSelect) typeSelect.name = `courts[${courtIdx}][venue_type_id]`;


                    const avatarInput = courtItem.querySelector('input[name*="[avatar]"]');
                    if (avatarInput) avatarInput.name = `courts[${courtIdx}][avatar]`;


                    const imagesInput = courtItem.querySelector('input[name*="[images]"]');
                    if (imagesInput) imagesInput.name = `courts[${courtIdx}][images][]`;


                    const surfaceInput = courtItem.querySelector('input[name*="[surface]"]');
                    if (surfaceInput) surfaceInput.name = `courts[${courtIdx}][surface]`;

                    const indoorSelect = courtItem.querySelector('select[name*="[is_indoor]"]');
                    if (indoorSelect) indoorSelect.name = `courts[${courtIdx}][is_indoor]`;

                    const courtNumberSpan = courtItem.querySelector('.court-number');
                    if (courtNumberSpan) courtNumberSpan.textContent = courtIdx + 1;
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
                        <input type="text" name="courts[${courtIndex}][name]" class="form-control">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Loại sân</label>
                        <select name="courts[${courtIndex}][venue_type_id]" class="form-select court-type-select">
                            ${options}
                        </select>
                    </div>
                </div>
                <div class="row bg-light p-2 mb-3 mx-0 rounded border">
                    <div class="col-md-5 mb-2">
                        <label class="form-label fw-bold">Ảnh đại diện (1 ảnh)</label>
                        <input type="file" name="courts[${courtIndex}][avatar]" class="form-control court-avatar-input" accept="image/*">
                    </div>
                    <div class="col-md-7 mb-2">
                        <label class="form-label fw-bold">Album ảnh (Nhiều ảnh)</label>
                        <!-- ĐÃ SỬA CLASS TẠI ĐÂY -->
                        <input type="file" name="courts[${courtIndex}][images][]" class="form-control court-images-input" accept="image/*" multiple>
                    </div>
                    <!-- THÊM CONTAINER PREVIEW -->
                    <div class="row g-2 mt-2 court-image-preview-container"></div>
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
                <!-- XÓA KHỐI LẶP DƯ THỪA (DÒNG 414-419) -->

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


                const newCourtItem = courtList.lastElementChild;

                const avatarInput = newCourtItem.querySelector('.court-avatar-input');
                const imagesInput = newCourtItem.querySelector('.court-images-input');
                const previewContainer = newCourtItem.querySelector('.court-image-preview-container');

                avatarInput.addEventListener('change', function() {
                    previewContainer.innerHTML = '';
                    setupImagePreview(previewContainer, this);
                });
                imagesInput.addEventListener('change', function() {
                    previewContainer.innerHTML = '';
                    setupImagePreview(previewContainer, this);
                });

                courtIndex++;
                updateTimeSlotNames();
            });
            function updateAllCourtTypeSelects() {
    const selectedTypes = getSelectedVenueTypes();
    const optionsHtml = renderVenueTypeOptions(selectedTypes);

    document.querySelectorAll('.court-type-select').forEach(selectElement => {
        const currentValue = selectElement.value;
        selectElement.innerHTML = optionsHtml;
        if (currentValue && selectedTypes.some(t => t.id == currentValue)) {
            selectElement.value = currentValue;
        }
    });
}

            // Tự động cập nhật dropdown loại sân khi thay đổi checkbox
            document.querySelectorAll('.court-item').forEach(courtItem => {
                // Sử dụng class đã định nghĩa
                const avatarInput = courtItem.querySelector('.court-avatar-input');
                const imagesInput = courtItem.querySelector('.court-images-input');
                const previewContainer = courtItem.querySelector('.court-image-preview-container');

                if (avatarInput) {
                    avatarInput.addEventListener('change', function() {
                        previewContainer.innerHTML = '';
                        setupImagePreview(previewContainer, this);
                    });
                }
                if (imagesInput) {
                    imagesInput.addEventListener('change', function() {
                        previewContainer.innerHTML = '';
                        setupImagePreview(previewContainer, this);
                    });
                }
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
                        <td><input type="time" class="form-control form-control-sm time-start"></td>
                        <td><input type="time" class="form-control form-control-sm time-end"></td>
                        <td><input type="number" class="form-control form-control-sm time-price"></td>
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
                                    <td><input type="time" class="form-control form-control-sm time-start" value="${slot.start_time}"></td>
                                    <td><input type="time" class="form-control form-control-sm time-end" value="${slot.end_time}"></td>
                                    <td><input type="number" class="form-control form-control-sm time-price" value="${slot.price}"></td>
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

            document.querySelector('form').addEventListener('submit', () => updateTimeSlotNames());
        });
        const host = "https://provinces.open-api.vn/api/";
        const provinceSelect = document.getElementById('province_id');
        const districtSelect = document.getElementById('district_id');
        const provinceNameInput = document.getElementById('province_name');
        const districtNameInput = document.getElementById('district_name');

        // Lấy data cũ từ thuộc tính data-old
        const oldProvinceId = provinceSelect.getAttribute('data-old');
        const oldDistrictId = districtSelect.getAttribute('data-old');

        axios.get(host + "?depth=1").then((response) => {
            let row = '<option value="">-- Chọn Tỉnh/Thành --</option>';
            response.data.forEach(element => {
                row +=
                    `<option value="${element.code}" data-name="${element.name}">${element.name}</option>`;
            });
            provinceSelect.innerHTML = row;

            // Nếu có dữ liệu cũ (khi reload do lỗi), set lại giá trị
            if (oldProvinceId) {
                provinceSelect.value = oldProvinceId;
                // Gọi hàm load huyện ngay lập tức
                loadDistricts(oldProvinceId, oldDistrictId);
            }
        });

        function loadDistricts(provinceCode, selectedDistrict = null) {
            districtSelect.innerHTML = '<option value="">-- Đang tải... --</option>';
            districtSelect.disabled = true;

            axios.get(host + "p/" + provinceCode + "?depth=2").then((response) => {
                let row = '<option value="">-- Chọn Quận/Huyện --</option>';
                response.data.districts.forEach(element => {
                    row +=
                        `<option value="${element.code}" data-name="${element.name}">${element.name}</option>`;
                });
                districtSelect.innerHTML = row;
                districtSelect.disabled = false;


                if (selectedDistrict) {
                    districtSelect.value = selectedDistrict;
                }
            });
        }

        provinceSelect.addEventListener("change", () => {
            const selectedOption = provinceSelect.options[provinceSelect.selectedIndex];
            const provinceCode = selectedOption.value;
            provinceNameInput.value = selectedOption.getAttribute('data-name');

            if (provinceCode) {
                loadDistricts(provinceCode);
            } else {
                districtSelect.innerHTML = '<option value="">-- Chọn Tỉnh/Thành trước --</option>';
                districtSelect.disabled = true;
            }
        });

        districtSelect.addEventListener("change", () => {
            const selectedOption = districtSelect.options[districtSelect.selectedIndex];
            districtNameInput.value = selectedOption.getAttribute('data-name');
        });

        function setupImagePreview(containerElement, fileInput) {
            containerElement.innerHTML = '';
            if (fileInput.files) {
                Array.from(fileInput.files).forEach(file => {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const imageUrl = e.target.result;
                        const isSingle = fileInput.hasAttribute('multiple') === false;

                        const html = `
                                <div class="col-md-${isSingle ? 12 : 4} col-6">
                                    <div class="position-relative border rounded p-2">
                                        <img src="${imageUrl}" 
                                             class="img-fluid rounded shadow-sm" 
                                             style="height: 150px; width: 100%; object-fit: cover; cursor: pointer;"
                                             alt="Preview"
                                             data-bs-toggle="modal" 
                                             data-bs-target="#imageModal"
                                             data-image-url="${imageUrl}">
                                        <span class="badge bg-info position-absolute top-0 start-0 m-1">${isSingle ? 'Đại diện' : 'Album'}</span>
                                    </div>
                                </div>
                            `;
                        containerElement.insertAdjacentHTML('beforeend', html);
                    }
                    reader.readAsDataURL(file);
                });
            }
        }
        // phong to anh
        var imageModal = document.getElementById('imageModal');
        if (imageModal) {
            imageModal.addEventListener('show.bs.modal', function(event) {
                var triggerImage = event.relatedTarget;
                var imageUrl = triggerImage.getAttribute('data-image-url');
                var modalImage = document.getElementById('modalImage');
                modalImage.src = imageUrl;
            });
        }
    </script>
@endsection
