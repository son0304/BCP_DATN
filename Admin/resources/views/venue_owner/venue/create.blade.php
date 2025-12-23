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

    <form id="venue-create-form" action="{{ route('owner.venues.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="row">
            <div class="col-lg-8">
                {{-- CARD 1: THÔNG TIN CƠ BẢN --}}
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Thông tin cơ bản</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Tên thương hiệu (sân) <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Chủ sở hữu</label>
                            @if (auth()->user()->role->name === 'admin')
                                <select name="owner_id" class="form-select @error('owner_id') is-invalid @enderror" required>
                                    <option value="">-- Chọn chủ sở hữu --</option>
                                    @foreach ($owners as $owner)
                                        <option value="{{ $owner->id }}"
                                            {{ old('owner_id') == $owner->id ? 'selected' : '' }}>
                                            {{ $owner->name }} ({{ $owner->email }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('owner_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            @else
                                <input type="hidden" name="owner_id" value="{{ auth()->user()->id }}">
                                <input type="text" class="form-control" value="{{ auth()->user()->name }}" disabled>
                            @endif
                        </div>

                        <hr class="my-4">
                        <h6 class="fw-bold">Thông tin địa chỉ</h6>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tỉnh/Thành <span class="text-danger">*</span></label>
                                <select name="province_id" id="province_id"
                                    class="form-select @error('province_id') is-invalid @enderror"
                                    data-old="{{ old('province_id') }}" required>
                                    <option value="">-- Đang tải... --</option>
                                </select>
                                @error('province_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Quận/Huyện <span class="text-danger">*</span></label>
                                <select name="district_id" id="district_id"
                                    class="form-select @error('district_id') is-invalid @enderror"
                                    data-old="{{ old('district_id') }}" required disabled>
                                    <option value="">-- Chọn Tỉnh/Thành trước --</option>
                                </select>
                                @error('district_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Địa chỉ chi tiết <span class="text-danger">*</span></label>
                            <input type="text" name="address_detail" value="{{ old('address_detail') }}"
                                class="form-control @error('address_detail') is-invalid @enderror" required>
                            @error('address_detail')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- CARD 2: DANH SÁCH SÂN --}}
                <div class="card shadow-sm mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Danh sách sân</h5>
                        <button type="button" id="add-court-btn" class="btn btn-sm btn-success">
                            <i class="fas fa-plus"></i> Thêm sân
                        </button>
                    </div>
                    <div class="card-body" id="court-list">
                        @if (old('courts'))
                            @foreach (old('courts') as $courtIndex => $court)
                                <div class="border rounded p-3 mb-3 court-item" data-index="{{ $courtIndex }}">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h6 class="mb-0 fw-bold">Sân #<span class="court-number">{{ $courtIndex + 1 }}</span></h6>
                                        <button type="button" class="btn btn-sm btn-danger remove-court"><i class="fas fa-times"></i></button>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Tên sân</label>
                                            <input type="text" name="courts[{{ $courtIndex }}][name]"
                                                value="{{ $court['name'] ?? '' }}"
                                                class="form-control @error("courts.{$courtIndex}.name") is-invalid @enderror"
                                                required>
                                            @error("courts.{$courtIndex}.name")
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Loại sân</label>
                                            <select name="courts[{{ $courtIndex }}][venue_type_id]"
                                                class="form-select court-type-select @error("courts.{$courtIndex}.venue_type_id") is-invalid @enderror"
                                                required>
                                                <option value="">-- Chọn loại hình --</option>
                                                @foreach ($venue_types as $type)
                                                    <option value="{{ $type->id }}"
                                                        {{ ($court['venue_type_id'] ?? '') == $type->id ? 'selected' : '' }}>
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
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Mặt sân</label>
                                            <input type="text" name="courts[{{ $courtIndex }}][surface]"
                                                value="{{ $court['surface'] ?? '' }}"
                                                class="form-control @error("courts.{$courtIndex}.surface") is-invalid @enderror"
                                                placeholder="Cỏ nhân tạo, cỏ tự nhiên...">
                                            @error("courts.{$courtIndex}.surface")
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Trong nhà / Ngoài trời</label>
                                            <select name="courts[{{ $courtIndex }}][is_indoor]"
                                                class="form-select @error("courts.{$courtIndex}.is_indoor") is-invalid @enderror">
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
                                                        <tr class="@if ($errors->has("courts.{$courtIndex}.time_slots.{$slotIndex}.*")) table-danger @endif">
                                                            <td>
                                                                <input type="time"
                                                                    name="courts[{{ $courtIndex }}][time_slots][{{ $slotIndex }}][start_time]"
                                                                    value="{{ $slot['start_time'] ?? '' }}"
                                                                    class="form-control form-control-sm time-start @error("courts.{$courtIndex}.time_slots.{$slotIndex}.start_time") is-invalid @enderror"
                                                                    required>
                                                                @error("courts.{$courtIndex}.time_slots.{$slotIndex}.start_time")
                                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                                @enderror
                                                            </td>
                                                            <td>
                                                                <input type="time"
                                                                    name="courts[{{ $courtIndex }}][time_slots][{{ $slotIndex }}][end_time]"
                                                                    value="{{ $slot['end_time'] ?? '' }}"
                                                                    class="form-control form-control-sm time-end @error("courts.{$courtIndex}.time_slots.{$slotIndex}.end_time") is-invalid @enderror"
                                                                    required>
                                                                @error("courts.{$courtIndex}.time_slots.{$slotIndex}.end_time")
                                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                                @enderror
                                                            </td>
                                                            <td>
                                                                <input type="number"
                                                                    name="courts[{{ $courtIndex }}][time_slots][{{ $slotIndex }}][price]"
                                                                    value="{{ $slot['price'] ?? '' }}"
                                                                    class="form-control form-control-sm time-price @error("courts.{$courtIndex}.time_slots.{$slotIndex}.price") is-invalid @enderror"
                                                                    required>
                                                                @error("courts.{$courtIndex}.time_slots.{$slotIndex}.price")
                                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                                @enderror
                                                            </td>
                                                            <td class="text-center">
                                                                <button type="button" class="btn btn-sm btn-outline-danger remove-slot"><i class="fas fa-trash"></i></button>
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
            <div class="col-lg-4">
                {{-- CARD 3: THÔNG TIN BỔ SUNG --}}
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Thông tin bổ sung</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Số điện thoại</label>
                            <input type="tel" name="phone" value="{{ old('phone') }}"
                                class="form-control @error('phone') is-invalid @enderror" placeholder="09xxxxxxxx">
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Giờ mở cửa</label>
                                <input type="time" name="start_time"
                                    class="form-control custom-input @error('start_time') is-invalid @enderror"
                                    value="{{ old('start_time') }}">
                                @error('start_time')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Giờ đóng cửa</label>
                                <input type="time" name="end_time"
                                    class="form-control custom-input @error('end_time') is-invalid @enderror"
                                    value="{{ old('end_time') }}">
                                @error('end_time')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <hr>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Hình ảnh thương hiệu (Tối đa 5) <span class="text-danger">*</span></label>

                            <ul class="nav nav-tabs" id="imageTab" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="file-tab" data-bs-toggle="tab"
                                        data-bs-target="#file-tab-pane" type="button" role="tab"
                                        aria-controls="file-tab-pane" aria-selected="true">Tải file</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="link-tab" data-bs-toggle="tab"
                                        data-bs-target="#link-tab-pane" type="button" role="tab"
                                        aria-controls="link-tab-pane" aria-selected="false">Chèn link</button>
                                </li>
                            </ul>

                            <div class="tab-content border border-top-0 p-3 rounded-bottom" id="imageTabContent">
                                <div class="tab-pane fade show active" id="file-tab-pane" role="tabpanel" aria-labelledby="file-tab" tabindex="0">
                                    <input type="file" name="images[]" id="images_input"
                                        class="form-control @error('images') is-invalid @enderror @error('images.*') is-invalid @enderror"
                                        accept="image/jpeg,image/png,image/jpg,image/gif,image/webp" multiple>
                                    <div class="form-text text-muted">Chọn file ảnh.</div>
                                </div>

                                <div class="tab-pane fade" id="link-tab-pane" role="tabpanel" aria-labelledby="link-tab" tabindex="0">
                                    <div id="image-links-container">
                                        @if (old('image_links'))
                                            @foreach (old('image_links') as $i => $link)
                                                <div class="input-group mb-2 image-link-item">
                                                    <input type="url" name="image_links[]"
                                                        class="form-control form-control-sm image-link-input @error('image_links.' . $i) is-invalid @enderror"
                                                        value="{{ $link }}" placeholder="https://..." required>
                                                    <button class="btn btn-outline-danger btn-sm remove-link-btn" type="button"><i class="fas fa-trash"></i></button>
                                                    @error('image_links.' . $i)
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            @endforeach
                                        @endif
                                    </div>
                                    <button type="button" id="add-link-btn" class="btn btn-sm btn-outline-primary mt-2">
                                        <i class="fas fa-plus me-1"></i> Thêm link ảnh
                                    </button>
                                </div>
                            </div>

                            <input type="hidden" name="primary_image_index" id="primary_image_index" value="{{ old('primary_image_index', 0) }}">

                            @error('images') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            @error('images.*') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            @error('image_links') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            @error('image_links.*') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            @error('primary_image_index') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>

                        <h6 class="fw-bold mb-3">Xem trước và chọn ảnh chính</h6>
                        <div id="images-preview" class="row g-2 mb-4"></div>

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
                            <div class="text-danger mt-1 small"><i class="fas fa-exclamation-circle"></i> {{ $message }}</div>
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

   <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
   
    let courtIndex = {{ old('courts') ? count(old('courts')) : 0 }};
    const courtList = document.getElementById('court-list');
    const addCourtBtn = document.getElementById('add-court-btn');

    
    function timeToMinutes(timeStr) {
        if (!timeStr) return 0;
        const [hours, minutes] = timeStr.split(':').map(Number);
        return hours * 60 + minutes;
    }

    // Lấy giờ hoạt động của Thương hiệu
    function getVenueHours() {
        const open = document.querySelector('input[name="start_time"]').value;
        const close = document.querySelector('input[name="end_time"]').value;
        return { 
            open: open, 
            close: close,
            openMin: timeToMinutes(open),
            
            closeMin: timeToMinutes(close) === 0 ? 1440 : timeToMinutes(close) 
        };
    }

    // Kiểm tra khung giờ có nằm trong giờ thương hiệu không
    function validateSlotRange(row) {
        const venue = getVenueHours();
        const startInput = row.querySelector('.time-start');
        const endInput = row.querySelector('.time-end');
        
        if (!venue.open || !venue.close || !startInput.value || !endInput.value) return;

        const sMin = timeToMinutes(startInput.value);
        const eMin = timeToMinutes(endInput.value) === 0 ? 1440 : timeToMinutes(endInput.value);

        // Xóa thông báo lỗi cũ
        row.querySelectorAll('.error-msg').forEach(el => el.remove());
        row.classList.remove('table-danger');

        // So sánh
        if (sMin < venue.openMin || eMin > venue.closeMin) {
            row.classList.add('table-danger');
            const msg = `<div class="error-msg text-danger small">Ngoài giờ hoạt động (${venue.open} - ${venue.close})</div>`;
            startInput.closest('td').insertAdjacentHTML('beforeend', msg);
        }
    }

    function getSelectedVenueTypes() {
        return Array.from(document.querySelectorAll('.venue-type-checkbox:checked')).map(cb => ({
            id: cb.value,
            name: cb.nextElementSibling.textContent.trim()
        }));
    }

    function refreshAllCourtTypeSelects() {
        const selectedTypes = getSelectedVenueTypes();
        let options = selectedTypes.length === 0 
            ? `<option value="">-- Chưa chọn loại hình --</option>` 
            : `<option value="">-- Chọn loại hình --</option>` + selectedTypes.map(t => `<option value="${t.id}">${t.name}</option>`).join('');

        document.querySelectorAll('.court-type-select').forEach(select => {
            const currentVal = select.value;
            select.innerHTML = options;
            select.value = currentVal;
        });
    }

    document.querySelectorAll('.venue-type-checkbox').forEach(cb => cb.addEventListener('change', refreshAllCourtTypeSelects));

    
    function splitTimeIntoHourlySlots(startTime, endTime, price) {
        const GOLDEN_HOUR_START = 17; 
        const GOLDEN_HOUR_MULTIPLIER = 1.5;
        const slots = [];
        const venue = getVenueHours();

        let sMin = timeToMinutes(startTime);
        let eMin = timeToMinutes(endTime) === 0 ? 1440 : timeToMinutes(endTime);

        const basePrice = Number(price);
        let currentMin = sMin;

        while (currentMin < eMin) {
            let nextMin = currentMin + 60;
            if (nextMin > eMin) nextMin = eMin;

            let hours = Math.floor(currentMin / 60);
            let mins = currentMin % 60;
            let startStr = `${String(hours).padStart(2, '0')}:${String(mins).padStart(2, '0')}`;

            let nHours = Math.floor(nextMin / 60);
            let nMins = nextMin % 60;
            let endStr = `${String(nHours === 24 ? 0 : nHours).padStart(2, '0')}:${String(nMins).padStart(2, '0')}`;

            let currentPrice = (hours >= GOLDEN_HOUR_START) ? basePrice * GOLDEN_HOUR_MULTIPLIER : basePrice;
            
            slots.push({
                start: startStr,
                end: endStr,
                price: Math.round(currentPrice / 1000) * 1000
            });

            currentMin = nextMin;
        }
        return slots;
    }

    function updateAllNames() {
        document.querySelectorAll('.court-item').forEach((court, cIdx) => {
            const num = court.querySelector('.court-number');
            if(num) num.innerText = cIdx + 1;
            court.querySelectorAll('.time-slot-table tbody tr').forEach((row, sIdx) => {
                row.querySelector('.time-start').name = `courts[${cIdx}][time_slots][${sIdx}][start_time]`;
                row.querySelector('.time-end').name = `courts[${cIdx}][time_slots][${sIdx}][end_time]`;
                row.querySelector('.time-price').name = `courts[${cIdx}][time_slots][${sIdx}][price]`;
            });
        });
    }

    addCourtBtn.addEventListener('click', () => {
        const types = getSelectedVenueTypes();
        let options = types.length === 0 ? `<option value="">-- Chưa chọn loại hình --</option>` : types.map(t => `<option value="${t.id}">${t.name}</option>`).join('');
        const html = `
            <div class="border rounded p-3 mb-3 court-item">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="mb-0 fw-bold">Sân #<span class="court-number"></span></h6>
                    <button type="button" class="btn btn-sm btn-danger remove-court"><i class="fas fa-times"></i></button>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Tên sân</label>
                        <input type="text" name="courts[${courtIndex}][name]" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Loại sân</label>
                        <select name="courts[${courtIndex}][venue_type_id]" class="form-select court-type-select" required><option value="">-- Chọn --</option>${options}</select>
                    </div>
                </div>
                <table class="table table-bordered table-sm time-slot-table">
                    <thead><tr class="bg-light"><th>Bắt đầu</th><th>Kết thúc</th><th>Giá (VNĐ)</th><th></th></tr></thead>
                    <tbody>
                        <tr>
                            <td><input type="time" class="form-control form-control-sm time-start" required></td>
                            <td><input type="time" class="form-control form-control-sm time-end" required></td>
                            <td><input type="number" class="form-control form-control-sm time-price" required></td>
                            <td></td>
                        </tr>
                    </tbody>
                </table>
                <button type="button" class="btn btn-sm btn-outline-success add-time-slot"><i class="fas fa-plus"></i> Thêm khung giờ</button>
            </div>`;
        courtList.insertAdjacentHTML('beforeend', html);
        courtIndex++;
        updateAllNames();
    });

   
    document.addEventListener('change', (e) => {
        if (e.target.classList.contains('time-start') || e.target.classList.contains('time-end') || e.target.classList.contains('time-price')) {
            const row = e.target.closest('tr');
            const tbody = row.closest('tbody');
            const startVal = row.querySelector('.time-start').value;
            const endVal = row.querySelector('.time-end').value;
            const priceVal = row.querySelector('.time-price').value;

            if (startVal && endVal && priceVal) {
                const slots = splitTimeIntoHourlySlots(startVal, endVal, priceVal);
                if (slots.length > 1) {
                    row.remove();
                    slots.forEach(s => {
                        tbody.insertAdjacentHTML('beforeend', `
                            <tr>
                                <td><input type="time" class="form-control form-control-sm time-start" value="${s.start}" required></td>
                                <td><input type="time" class="form-control form-control-sm time-end" value="${s.end}" required></td>
                                <td><input type="number" class="form-control form-control-sm time-price" value="${s.price}" required></td>
                                <td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger remove-slot"><i class="fas fa-trash"></i></button></td>
                            </tr>
                        `);
                    });
                } else {
                    validateSlotRange(row);
                }
                updateAllNames();
            }
        }
    });

    
    document.addEventListener('click', (e) => {
        if (e.target.closest('.add-time-slot')) {
            const tbody = e.target.closest('.court-item').querySelector('tbody');
            tbody.insertAdjacentHTML('beforeend', `<tr><td><input type="time" class="form-control form-control-sm time-start" required></td><td><input type="time" class="form-control form-control-sm time-end" required></td><td><input type="number" class="form-control form-control-sm time-price" required></td><td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger remove-slot"><i class="fas fa-trash"></i></button></td></tr>`);
            updateAllNames();
        }
        if (e.target.closest('.remove-slot')) { e.target.closest('tr').remove(); updateAllNames(); }
        if (e.target.closest('.remove-court')) { e.target.closest('.court-item').remove(); updateAllNames(); }
    });

  
    const imagesInput = document.getElementById('images_input');
    const previewContainer = document.getElementById('images-preview');
    const primaryIndexInput = document.getElementById('primary_image_index');
    let accumulatedFiles = [];

    function updatePreview() {
        previewContainer.innerHTML = '';
        accumulatedFiles.forEach((file, i) => {
            const col = document.createElement('div');
            col.className = 'col-6 col-md-4 mb-2';
            col.innerHTML = `
                <div class="border rounded p-1 text-center position-relative">
                    <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-1 remove-img" data-idx="${i}">&times;</button>
                    <img src="${URL.createObjectURL(file)}" class="img-fluid rounded mb-1" style="height:80px; object-fit:cover;">
                    <div class="form-check small"><input type="radio" name="p_radio" value="${i}" ${primaryIndexInput.value == i ? 'checked' : ''}> Chính</div>
                </div>`;
            previewContainer.appendChild(col);
        });
    }

    imagesInput.addEventListener('change', (e) => {
        accumulatedFiles = [...accumulatedFiles, ...Array.from(e.target.files)].slice(0, 5);
        updatePreview();
    });

    document.addEventListener('click', (e) => {
        if (e.target.classList.contains('remove-img')) {
            accumulatedFiles.splice(e.target.dataset.idx, 1);
            updatePreview();
        }
        if (e.target.name === 'p_radio') primaryIndexInput.value = e.target.value;
    });

    document.getElementById('venue-create-form').addEventListener('submit', function(e) {
        const dataTransfer = new DataTransfer();
        accumulatedFiles.forEach(f => dataTransfer.items.add(f));
        imagesInput.files = dataTransfer.files;
    });

   
    const $p = $('#province_id'), $d = $('#district_id');
    $.get('/api-proxy/provinces', (data) => {
        let h = '<option value="">-- Chọn --</option>';
        data.forEach(x => h += `<option value="${x.code}" ${$p.data('old') == x.code ? 'selected' : ''}>${x.name}</option>`);
        $p.html(h).trigger('change');
    });
    $p.on('change', function() {
        const code = $(this).val();
        if (!code) return $d.html('<option value="">-- Chọn --</option>').prop('disabled', true);
        $d.prop('disabled', false).html('<option>Đang tải...</option>');
        $.get('/api-proxy/districts/' + code, (data) => {
            let h = '<option value="">-- Chọn --</option>';
            data.forEach(x => h += `<option value="${x.code}" ${$d.data('old') == x.code ? 'selected' : ''}>${x.name}</option>`);
            $d.html(h);
        });
    });
});
</script>
@endsection