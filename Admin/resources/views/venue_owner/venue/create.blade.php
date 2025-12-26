@extends('app')

@section('content')
    {{-- 1. THÊM CSS FLATPICKR VÀ LEAFLET --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

    <style>
        /* CSS cho Map */
        #map { height: 300px; width: 100%; border-radius: 5px; border: 1px solid #ced4da; z-index: 1; }

        /* CSS tuỳ chỉnh cho Validate */
        .is-invalid { border-color: #dc3545 !important; background-image: none !important; }
        .invalid-feedback.custom-error-msg { display: block; font-size: 0.875em; margin-top: 0.25rem; color: #dc3545; }

        /* Chỉnh lại hiển thị input giờ trong bảng khi lỗi */
        .time-slot-table .form-control.is-invalid { padding-right: 5px; }

        /* Style checkbox */
        .custom-checkbox { cursor: pointer; }
    </style>

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

    <form id="venue-create-form" action="{{ route('owner.venues.store') }}" method="POST" enctype="multipart/form-data" novalidate>
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
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

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
                            @else
                                <input type="hidden" name="owner_id" value="{{ auth()->user()->id }}">
                                <input type="text" class="form-control" value="{{ auth()->user()->name }}" disabled>
                            @endif
                        </div>

                        <hr class="my-4">
                        <h6 class="fw-bold">Thông tin địa chỉ & Bản đồ</h6>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tỉnh/Thành <span class="text-danger">*</span></label>
                                <select name="province_id" id="province_id" class="form-select @error('province_id') is-invalid @enderror" data-old="{{ old('province_id') }}" required>
                                    <option value="">-- Đang tải... --</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Quận/Huyện <span class="text-danger">*</span></label>
                                <select name="district_id" id="district_id" class="form-select @error('district_id') is-invalid @enderror" data-old="{{ old('district_id') }}" required disabled>
                                    <option value="">-- Chọn Tỉnh/Thành trước --</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Địa chỉ chi tiết <span class="text-danger">*</span></label>
                            <input type="text" name="address_detail" value="{{ old('address_detail') }}" class="form-control @error('address_detail') is-invalid @enderror" required>
                        </div>

                        {{-- === PHẦN BẢN ĐỒ === --}}
                        <div class="mb-3">
                            <label class="form-label fw-bold">Ghim vị trí (Kéo thả để chọn) <span class="text-danger">*</span></label>
                            <div id="map"></div>
                            {{-- Input ẩn chứa Lat/Lng --}}
                            <input type="hidden" name="lat" id="lat" value="{{ old('lat', '21.028511') }}">
                            <input type="hidden" name="lng" id="lng" value="{{ old('lng', '105.854444') }}">
                            @error('lat') <div class="text-danger small mt-1">Vui lòng chọn vị trí trên bản đồ.</div> @enderror
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
                                            <input type="text" name="courts[{{ $courtIndex }}][name]" value="{{ $court['name'] ?? '' }}" class="form-control @error('courts.'.$courtIndex.'.name') is-invalid @enderror" required>
                                            @error('courts.'.$courtIndex.'.name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Loại sân</label>
                                            <select name="courts[{{ $courtIndex }}][venue_type_id]" class="form-select court-type-select @error('courts.'.$courtIndex.'.venue_type_id') is-invalid @enderror" required>
                                                <option value="">-- Chọn loại hình --</option>
                                                <option value="{{ $court['venue_type_id'] ?? '' }}" selected>Đã chọn (Load lại trang)</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Mặt sân</label>
                                            <input type="text" name="courts[{{ $courtIndex }}][surface]" value="{{ $court['surface'] ?? '' }}" class="form-control" placeholder="Cỏ nhân tạo...">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Trong nhà / Ngoài trời</label>
                                            <select name="courts[{{ $courtIndex }}][is_indoor]" class="form-select">
                                                <option value="0" {{ ($court['is_indoor'] ?? '0') == '0' ? 'selected' : '' }}>Ngoài trời</option>
                                                <option value="1" {{ ($court['is_indoor'] ?? '0') == '1' ? 'selected' : '' }}>Trong nhà</option>
                                            </select>
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
                                                        <tr>
                                                            <td>
                                                                <input type="text"
                                                                    name="courts[{{ $courtIndex }}][time_slots][{{ $slotIndex }}][start_time]"
                                                                    value="{{ $slot['start_time'] ?? '' }}"
                                                                    class="form-control form-control-sm time-start time-picker"
                                                                    required placeholder="00:00">
                                                            </td>
                                                            <td>
                                                                <input type="text"
                                                                    name="courts[{{ $courtIndex }}][time_slots][{{ $slotIndex }}][end_time]"
                                                                    value="{{ $slot['end_time'] ?? '' }}"
                                                                    class="form-control form-control-sm time-end time-picker"
                                                                    required placeholder="00:00">
                                                            </td>
                                                            <td>
                                                                <input type="number"
                                                                    name="courts[{{ $courtIndex }}][time_slots][{{ $slotIndex }}][price]"
                                                                    value="{{ $slot['price'] ?? '' }}"
                                                                    class="form-control form-control-sm time-price"
                                                                    required>
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
                            <input type="tel" name="phone" value="{{ old('phone') }}" class="form-control @error('phone') is-invalid @enderror" placeholder="09xxxxxxxx">
                            @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Giờ mở cửa</label>
                                <input type="text" name="start_time"
                                    class="form-control time-picker"
                                    value="{{ old('start_time', '05:00') }}" placeholder="05:00">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Giờ đóng cửa</label>
                                <input type="text" name="end_time"
                                    class="form-control time-picker"
                                    value="{{ old('end_time', '23:00') }}" placeholder="23:00">
                            </div>
                        </div>

                        <hr>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Hình ảnh thương hiệu (Tối đa 5) <span class="text-danger">*</span></label>
                            <ul class="nav nav-tabs" id="imageTab" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="file-tab" data-bs-toggle="tab" data-bs-target="#file-tab-pane" type="button">Tải file</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="link-tab" data-bs-toggle="tab" data-bs-target="#link-tab-pane" type="button">Chèn link</button>
                                </li>
                            </ul>
                            <div class="tab-content border border-top-0 p-3 rounded-bottom" id="imageTabContent">
                                <div class="tab-pane fade show active" id="file-tab-pane">
                                    <input type="file" name="images[]" id="images_input" class="form-control" accept="image/*" multiple>
                                </div>
                                <div class="tab-pane fade" id="link-tab-pane">
                                    <div id="image-links-container"></div>
                                    <button type="button" id="add-link-btn" class="btn btn-sm btn-outline-primary mt-2">+ Thêm link ảnh</button>
                                </div>
                            </div>
                            <input type="hidden" name="primary_image_index" id="primary_image_index" value="0">
                        </div>

                        <h6 class="fw-bold mb-3">Xem trước và chọn ảnh chính</h6>
                        <div id="images-preview" class="row g-2 mb-4"></div>
                        @error('images') <div class="text-danger small mb-2">{{ $message }}</div> @enderror

                        <label class="form-label fw-bold d-block">Loại hình sân <span class="text-danger">*</span></label>
                        <div class="border rounded p-2">
                            @foreach ($venue_types as $type)
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input venue-type-checkbox custom-checkbox" type="checkbox"
                                        name="venue_types[]" id="venue_type_{{ $type->id }}" value="{{ $type->id }}"
                                        {{ is_array(old('venue_types')) && in_array($type->id, old('venue_types')) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="venue_type_{{ $type->id }}">{{ $type->name }}</label>
                                </div>
                            @endforeach
                        </div>
                        @error('venue_types') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="text-center mt-4 mb-5">
            <input type="hidden" name="is_active" value="0">
            <button type="submit" class="btn btn-primary px-4 py-2">
                <i class="fas fa-save me-2"></i> Lưu và tạo mới
            </button>
        </div>
    </form>

    {{-- 4. JS: FLATPICKR, LEAFLET & JQUERY --}}
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
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
    function minutesToTime(mins) { if(mins === 1440 || mins === 0) return "00:00"; let h = Math.floor(mins / 60); let m = mins % 60; if(h >= 24) h -= 24; return `${String(h).padStart(2,'0')}:${String(m).padStart(2,'0')}`; }

    $(document).ready(function() {
        // --- 1. SETUP MAP ---
        let defaultLat = parseFloat($('#lat').val()) || 21.028511;
        let defaultLng = parseFloat($('#lng').val()) || 105.854444;

        var map = L.map('map').setView([defaultLat, defaultLng], 13);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        var marker = L.marker([defaultLat, defaultLng], {draggable: true}).addTo(map);

        function updateMarker(lat, lng) {
            $('#lat').val(lat.toFixed(6));
            $('#lng').val(lng.toFixed(6));
        }

        marker.on('dragend', function(e) {
            var coord = e.target.getLatLng();
            updateMarker(coord.lat, coord.lng);
        });

        map.on('click', function(e) {
            marker.setLatLng(e.latlng);
            updateMarker(e.latlng.lat, e.latlng.lng);
        });

        // --- 2. SETUP TIME & IMAGES ---
        initFlatpickr(".time-picker");

        const imagesInput = document.getElementById('images_input');
        const previewContainer = document.getElementById('images-preview');
        const primaryIndexInput = document.getElementById('primary_image_index');
        let accumulatedFiles = []; // Biến global để check validate ảnh

        function updatePreview() {
            previewContainer.innerHTML = '';
            accumulatedFiles.forEach((file, i) => {
                const col = document.createElement('div');
                col.className = 'col-6 col-md-4 mb-2';
                col.innerHTML = `<div class="border rounded p-1 text-center position-relative"><button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-1 remove-img" data-idx="${i}">&times;</button><img src="${URL.createObjectURL(file)}" class="img-fluid rounded mb-1" style="height:80px; object-fit:cover;"><div class="form-check small"><input type="radio" name="p_radio" value="${i}" ${primaryIndexInput.value == i ? 'checked' : ''}> Chính</div></div>`;
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

        // --- 3. DYNAMIC COURTS LOGIC ---
        let courtIndex = {{ old('courts') ? count(old('courts')) : 0 }};
        const courtList = $('#court-list');

        function getSelectedVenueTypes() {
            let types = [];
            $('.venue-type-checkbox:checked').each(function() {
                types.push({id: $(this).val(), name: $(this).next('label').text().trim()});
            });
            return types;
        }

        function refreshAllCourtTypeSelects() {
            const types = getSelectedVenueTypes();
            let options = types.length === 0 ? `<option value="">-- Chưa chọn loại hình --</option>` : `<option value="">-- Chọn loại hình --</option>`;
            types.forEach(t => options += `<option value="${t.id}">${t.name}</option>`);

            $('.court-type-select').each(function() {
                const current = $(this).val();
                $(this).html(options).val(current);
            });
        }
        $('.venue-type-checkbox').on('change', refreshAllCourtTypeSelects);

        $('#add-court-btn').click(function() {
            const types = getSelectedVenueTypes();
            let options = types.length === 0 ? `<option value="">-- Chưa chọn loại hình --</option>` : `<option value="">-- Chọn --</option>`;
            types.forEach(t => options += `<option value="${t.id}">${t.name}</option>`);

            const html = `
                <div class="border rounded p-3 mb-3 court-item" data-index="${courtIndex}">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="mb-0 fw-bold">Sân #<span class="court-number">${courtIndex + 1}</span></h6>
                        <button type="button" class="btn btn-sm btn-danger remove-court"><i class="fas fa-times"></i></button>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tên sân</label>
                            <input type="text" name="courts[${courtIndex}][name]" class="form-control" required placeholder="Sân ${courtIndex + 1}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Loại sân</label>
                            <select name="courts[${courtIndex}][venue_type_id]" class="form-select court-type-select" required>${options}</select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Mặt sân</label>
                            <input type="text" name="courts[${courtIndex}][surface]" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Trong nhà/Ngoài trời</label>
                            <select name="courts[${courtIndex}][is_indoor]" class="form-select">
                                <option value="0">Ngoài trời</option>
                                <option value="1">Trong nhà</option>
                            </select>
                        </div>
                    </div>
                    <table class="table table-bordered table-sm time-slot-table">
                        <thead><tr class="bg-light"><th>Bắt đầu</th><th>Kết thúc</th><th>Giá (VNĐ)</th><th></th></tr></thead>
                        <tbody>
                            <tr>
                                <td><input type="text" class="form-control form-control-sm time-start time-picker" name="courts[${courtIndex}][time_slots][0][start_time]" required placeholder="00:00"></td>
                                <td><input type="text" class="form-control form-control-sm time-end time-picker" name="courts[${courtIndex}][time_slots][0][end_time]" required placeholder="00:00"></td>
                                <td><input type="number" class="form-control form-control-sm time-price" name="courts[${courtIndex}][time_slots][0][price]" required></td>
                                <td></td>
                            </tr>
                        </tbody>
                    </table>
                    <button type="button" class="btn btn-sm btn-outline-success add-time-slot"><i class="fas fa-plus"></i> Thêm khung giờ</button>
                </div>`;

            const newCourt = $(html);
            courtList.append(newCourt);
            initFlatpickr(newCourt.find('.time-picker'));
            courtIndex++;
        });

        $(document).on('click', '.remove-court', function() {
            if(confirm('Xóa sân này?')) $(this).closest('.court-item').remove();
        });

        $(document).on('click', '.add-time-slot', function() {
            const tbody = $(this).prev('table').find('tbody');
            const cIdx = $(this).closest('.court-item').data('index');
            const sIdx = Math.floor(Math.random() * 100000);

            const row = `
                <tr>
                    <td><input type="text" class="form-control form-control-sm time-start time-picker" name="courts[${cIdx}][time_slots][${sIdx}][start_time]" required placeholder="00:00"></td>
                    <td><input type="text" class="form-control form-control-sm time-end time-picker" name="courts[${cIdx}][time_slots][${sIdx}][end_time]" required placeholder="00:00"></td>
                    <td><input type="number" class="form-control form-control-sm time-price" name="courts[${cIdx}][time_slots][${sIdx}][price]" required></td>
                    <td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger remove-slot"><i class="fas fa-trash"></i></button></td>
                </tr>`;

            const newRow = $(row);
            tbody.append(newRow);
            initFlatpickr(newRow.find('.time-picker'));
        });

        $(document).on('click', '.remove-slot', function() {
            $(this).closest('tr').remove();
        });

        $(document).on('change', '.time-end, .time-price', function() {
            const row = $(this).closest('tr');
            const startVal = row.find('.time-start').val();
            const endVal = row.find('.time-end').val();
            const priceVal = row.find('.time-price').val();

            if (!startVal || !endVal || !priceVal) return;

            const startMin = timeToMinutes(startVal);
            const endMin = timeToMinutes(endVal) === 0 ? 1440 : timeToMinutes(endVal);

            if (endMin <= startMin) {
                // Sẽ được validate ở bước submit, nhưng alert nhắc trước
                return;
            }

            if ((endMin - startMin) > 60) {
                if(confirm(`Bạn nhập khoảng ${endMin - startMin} phút. Có muốn tự động chia nhỏ thành các khung 60 phút không?`)) {
                    splitSlots(row, startMin, endMin, priceVal);
                }
            }
        });

        function splitSlots(originalRow, startMin, endMin, price) {
            const tbody = originalRow.closest('tbody');
            const cIdx = originalRow.closest('.court-item').data('index');
            originalRow.remove();
            let current = startMin;
            while(current < endMin) {
                let next = current + 60;
                if(next > endMin) next = endMin;
                const randId = Math.floor(Math.random() * 100000);
                const html = `<tr>
                        <td><input type="text" class="form-control form-control-sm time-start time-picker" value="${minutesToTime(current)}" name="courts[${cIdx}][time_slots][${randId}][start_time]" required></td>
                        <td><input type="text" class="form-control form-control-sm time-end time-picker" value="${minutesToTime(next)}" name="courts[${cIdx}][time_slots][${randId}][end_time]" required></td>
                        <td><input type="number" class="form-control form-control-sm time-price" value="${price}" name="courts[${cIdx}][time_slots][${randId}][price]" required></td>
                        <td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger remove-slot"><i class="fas fa-trash"></i></button></td>
                    </tr>`;
                const newRow = $(html);
                tbody.append(newRow);
                initFlatpickr(newRow.find('.time-picker'));
                current = next;
            }
        }

        // --- 4. API LOCATION ---
        const cityCoords = {'01': [21.0285, 105.8542], '79': [10.8231, 106.6297], '48': [16.0544, 108.2022]};
        const $p = $('#province_id'), $d = $('#district_id');
        $.get('/api-proxy/provinces', (data) => {
            let h = '<option value="">-- Chọn --</option>';
            data.forEach(x => h += `<option value="${x.code}" ${$p.data('old') == x.code ? 'selected' : ''}>${x.name}</option>`);
            $p.html(h).trigger('change');
        });
        $p.on('change', function() {
            const code = $(this).val();
            if(cityCoords[code]) {
                const newLatLng = cityCoords[code];
                map.setView(newLatLng, 12);
                marker.setLatLng(newLatLng);
                updateMarker(newLatLng[0], newLatLng[1]);
            }

            if (!code) return $d.html('<option value="">-- Chọn --</option>').prop('disabled', true);
            $d.prop('disabled', false).html('<option>Đang tải...</option>');
            $.get('/api-proxy/districts/' + code, (data) => {
                let h = '<option value="">-- Chọn --</option>';
                data.forEach(x => h += `<option value="${x.code}" ${$d.data('old') == x.code ? 'selected' : ''}>${x.name}</option>`);
                $d.html(h);
            });
        });

        // --- 5. VALIDATE & SUBMIT ---
        $('#venue-create-form').on('submit', function(e) {
            // Reset lỗi cũ
            $('.is-invalid').removeClass('is-invalid');
            $('.custom-error-msg').remove();

            let isValid = true;
            let firstError = null;

            function showErr(selector, msg) {
                const el = $(selector);
                el.addClass('is-invalid');
                if(el.next('.invalid-feedback').length === 0) {
                    el.after(`<div class="invalid-feedback custom-error-msg">${msg}</div>`);
                } else {
                    el.next('.invalid-feedback').text(msg).show();
                }
                if(!firstError) firstError = el;
                isValid = false;
            }

            // 5.1 Basic Info
            if($('input[name="name"]').val().trim() === '') showErr('input[name="name"]', 'Tên không được để trống.');
            if($('#province_id').val() === '') showErr('#province_id', 'Chọn Tỉnh/Thành.');
            if($('#district_id').val() === '') showErr('#district_id', 'Chọn Quận/Huyện.');
            if($('input[name="address_detail"]').val().trim() === '') showErr('input[name="address_detail"]', 'Nhập địa chỉ chi tiết.');

            // Map
            if($('#lat').val() === '' || $('#lng').val() === '') {
                alert('Vui lòng chọn vị trí trên bản đồ.');
                isValid = false;
                if(!firstError) firstError = $('#map');
            }

            // 5.2 Phone Regex
            const phone = $('input[name="phone"]').val().trim();
            const phoneRegex = /(84|0[3|5|7|8|9])+([0-9]{8})\b/;
            if (phone !== '' && !phoneRegex.test(phone)) {
                showErr('input[name="phone"]', 'SĐT không đúng định dạng.');
            }

            // 5.3 Time Logic
            const openTime = $('input[name="start_time"]').val();
            const closeTime = $('input[name="end_time"]').val();
            if (openTime && closeTime && timeToMinutes(closeTime) <= timeToMinutes(openTime)) {
                showErr('input[name="end_time"]', 'Giờ đóng phải sau giờ mở.');
            }

            // 5.4 Images
            if(accumulatedFiles.length === 0) {
                // Nếu không dùng link ảnh thì bắt buộc file
                // Kiểm tra xem có input link ảnh nào có giá trị không, nếu không -> lỗi
                alert('Vui lòng chọn ít nhất 1 ảnh thương hiệu.');
                isValid = false;
                if(!firstError) firstError = $('#images_input');
            }

            // 5.5 Venue Types
            if($('.venue-type-checkbox:checked').length === 0) {
                alert('Chọn ít nhất 1 loại hình sân.');
                isValid = false;
                if(!firstError) firstError = $('.venue-type-checkbox').first();
            }

            // 5.6 Courts Validation
            if($('.court-item').length === 0) {
                alert('Vui lòng thêm ít nhất 1 sân.');
                isValid = false;
            } else {
                $('.court-item').each(function() {
                    const row = $(this);
                    const cName = row.find('input[name*="[name]"]');
                    const cType = row.find('select[name*="[venue_type_id]"]');

                    if(cName.val().trim() === '') { cName.addClass('is-invalid'); isValid = false; if(!firstError) firstError = cName; }
                    if(cType.val() === '') { cType.addClass('is-invalid'); isValid = false; if(!firstError) firstError = cType; }

                    // Time slots check
                    const slots = row.find('.time-slot-table tbody tr');
                    if(slots.length === 0) {
                        alert(`Sân "${cName.val()}" chưa có khung giờ hoạt động.`);
                        isValid = false;
                    }
                    slots.each(function() {
                        const tr = $(this);
                        const tS = tr.find('.time-start');
                        const tE = tr.find('.time-end');
                        const tP = tr.find('.time-price');

                        if(!tS.val()) { tS.addClass('is-invalid'); isValid = false; }
                        if(!tE.val()) { tE.addClass('is-invalid'); isValid = false; }
                        if(!tP.val()) { tP.addClass('is-invalid'); isValid = false; }

                        if(tS.val() && tE.val() && timeToMinutes(tE.val()) <= timeToMinutes(tS.val())) {
                            tE.addClass('is-invalid');
                            isValid = false;
                            if(!firstError) firstError = tE;
                        }
                    });
                });
            }

            if (!isValid) {
                e.preventDefault();
                e.stopPropagation();
                if(firstError) {
                    $('html, body').animate({ scrollTop: $(firstError).offset().top - 150 }, 500);
                }
            } else {
                // Attach files to input
                const dataTransfer = new DataTransfer();
                accumulatedFiles.forEach(f => dataTransfer.items.add(f));
                imagesInput.files = dataTransfer.files;
            }
        });
    });
</script>
@endsection
