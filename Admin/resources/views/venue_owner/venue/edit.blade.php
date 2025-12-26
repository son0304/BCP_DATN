@extends('app')

@section('content')
    {{-- 1. CSS CHO MAP & FLATPICKR --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

    <style>
        #map {
            height: 300px;
            width: 100%;
            border-radius: 5px;
            border: 1px solid #ced4da;
            z-index: 1;
        }

        .is-invalid {
            border-color: #dc3545 !important;
        }

        .image-preview-item {
            position: relative;
        }

        .btn-delete-image {
            position: absolute;
            top: 5px;
            right: 5px;
            z-index: 10;
        }

        .custom-error-msg {
            color: #dc3545;
            font-size: 0.875em;
            margin-top: 0.25rem;
        }
    </style>

    <div class="container-fluid py-4">
        {{-- HEADER --}}
        <div class="d-flex justify-content-between align-items-center mb-4 bg-white p-3 rounded shadow-sm border">
            <div>
                <h4 class="fw-bold mb-0 text-primary">Chỉnh sửa thương hiệu</h4>
                <p class="text-muted small mb-0">Cập nhật thông tin cho: <strong>{{ $venue->name }}</strong></p>
            </div>
            <div>
                <a href="{{ route('owner.venues.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Quay lại danh sách
                </a>
            </div>
        </div>

        {{-- ERROR ALERT --}}
        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i> <strong>Vui lòng kiểm tra lại dữ liệu!</strong>
                <ul class="mb-0 mt-1 ps-3 small">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <form action="{{ route('owner.venues.update', $venue->id) }}" method="POST" enctype="multipart/form-data"
            id="venue-edit-form" novalidate>
            @csrf
            @method('PUT')

            <div class="row g-4">
                {{-- CỘT TRÁI: THÔNG TIN CƠ BẢN & MAP --}}
                <div class="col-lg-8">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-header bg-white py-3 border-bottom">
                            <h6 class="mb-0 fw-bold text-uppercase text-muted"><i class="fas fa-info-circle me-1"></i> Thông
                                tin cơ bản</h6>
                        </div>
                        <div class="card-body">
                            {{-- Tên & Owner --}}
                            <div class="mb-3">
                                <label class="form-label fw-bold">Tên thương hiệu <span class="text-danger">*</span></label>
                                <input type="text" name="name"
                                    class="form-control @error('name') is-invalid @enderror"
                                    value="{{ old('name', $venue->name) }}" required>
                            </div>

                            @if (auth()->user()->role->name === 'admin')
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Chủ sở hữu <span class="text-danger">*</span></label>
                                    <select name="owner_id" class="form-select @error('owner_id') is-invalid @enderror"
                                        required>
                                        <option value="">-- Chọn --</option>
                                        @foreach ($owners as $owner)
                                            <option value="{{ $owner->id }}"
                                                {{ old('owner_id', $venue->owner_id) == $owner->id ? 'selected' : '' }}>
                                                {{ $owner->name }} ({{ $owner->email }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            @else
                                <input type="hidden" name="owner_id" value="{{ $venue->owner_id }}">
                            @endif

                            <hr class="text-muted opacity-25">

                            {{-- Địa chỉ & Map --}}
                            <h6 class="fw-bold small text-muted text-uppercase mb-3">Vị trí & Bản đồ</h6>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Tỉnh/Thành <span class="text-danger">*</span></label>
                                    <select name="province_id" id="province_id"
                                        class="form-select @error('province_id') is-invalid @enderror"
                                        data-old="{{ old('province_id', $venue->province_id) }}" required>
                                        <option value="">-- Đang tải... --</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Quận/Huyện <span class="text-danger">*</span></label>
                                    <select name="district_id" id="district_id"
                                        class="form-select @error('district_id') is-invalid @enderror"
                                        data-old="{{ old('district_id', $venue->district_id) }}" required disabled>
                                        <option value="">-- Chọn Tỉnh/Thành trước --</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Địa chỉ chi tiết <span class="text-danger">*</span></label>
                                <input type="text" name="address_detail"
                                    class="form-control @error('address_detail') is-invalid @enderror"
                                    value="{{ old('address_detail', $venue->address_detail) }}" required>
                            </div>

                            {{-- MAP CONTAINER --}}
                            <div class="mb-3">
                                <label class="form-label fw-bold">Cập nhật vị trí trên bản đồ <span
                                        class="text-danger">*</span></label>
                                <div id="map"></div>
                                <input type="hidden" name="lat" id="lat" value="{{ old('lat', $venue->lat) }}">
                                <input type="hidden" name="lng" id="lng" value="{{ old('lng', $venue->lng) }}">
                                <div class="form-text text-muted">Kéo thả ghim đỏ để điều chỉnh vị trí chính xác.</div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- CỘT PHẢI: CẤU HÌNH & ẢNH --}}
                <div class="col-lg-4">
                    {{-- Thông tin bổ sung --}}
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-white py-3 border-bottom">
                            <h6 class="mb-0 fw-bold text-uppercase text-muted"><i class="fas fa-cog me-1"></i> Cấu hình</h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Số điện thoại</label>
                                <input type="tel" name="phone"
                                    class="form-control @error('phone') is-invalid @enderror"
                                    value="{{ old('phone', $venue->phone) }}" placeholder="09xxxxxxxx">
                            </div>

                            <div class="row">
                                <div class="col-6 mb-3">
                                    <label class="form-label fw-bold">Mở cửa</label>
                                    <input type="text" name="start_time" class="form-control time-picker"
                                        value="{{ old('start_time', \Carbon\Carbon::parse($venue->start_time)->format('H:i')) }}"
                                        required>
                                </div>
                                <div class="col-6 mb-3">
                                    <label class="form-label fw-bold">Đóng cửa</label>
                                    <input type="text" name="end_time" class="form-control time-picker"
                                        value="{{ old('end_time', $venue->end_time == '23:59:59' ? '24:00' : \Carbon\Carbon::parse($venue->end_time)->format('H:i')) }}"
                                        required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Loại hình sân <span class="text-danger">*</span></label>
                                <div class="border rounded p-2 bg-light check-group @error('venue_types') border-danger @enderror"
                                    style="max-height: 150px; overflow-y: auto;">
                                    @foreach ($venue_types as $type)
                                        <div class="form-check">
                                            <input class="form-check-input venue-type-checkbox" type="checkbox"
                                                name="venue_types[]" value="{{ $type->id }}"
                                                id="vtype_{{ $type->id }}"
                                                {{ in_array($type->id, old('venue_types', $venue->venueTypes->pluck('id')->toArray())) ? 'checked' : '' }}>
                                            <label class="form-check-label"
                                                for="vtype_{{ $type->id }}">{{ $type->name }}</label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Trạng thái</label>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="is_active" value="1"
                                        id="is_active" {{ old('is_active', $venue->is_active) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">Hiển thị trên hệ thống</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Quản lý Hình ảnh --}}
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-white py-3 border-bottom">
                            <h6 class="mb-0 fw-bold text-uppercase text-muted"><i class="fas fa-images me-1"></i> Hình ảnh
                            </h6>
                        </div>
                        <div class="card-body">
                            {{-- Tab thêm ảnh --}}
                            <ul class="nav nav-tabs nav-fill" id="imgTabs">
                                <li class="nav-item">
                                    <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-file"
                                        type="button">File</button>
                                </li>
                                <li class="nav-item">
                                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-link"
                                        type="button">Link</button>
                                </li>
                            </ul>
                            <div class="tab-content border border-top-0 p-3 mb-3 bg-light rounded-bottom">
                                <div class="tab-pane fade show active" id="tab-file">
                                    <input type="file" id="file_input" class="form-control" accept="image/*"
                                        multiple>
                                </div>
                                <div class="tab-pane fade" id="tab-link">
                                    <div class="input-group">
                                        <input type="url" id="link_input" class="form-control"
                                            placeholder="https://...">
                                        <button type="button" class="btn btn-primary" id="btn-add-link"><i
                                                class="fas fa-plus"></i></button>
                                    </div>
                                </div>
                            </div>

                            {{-- Khu vực Preview --}}
                            <div id="image-preview-container" class="row g-2"></div>

                            {{-- Hidden Inputs để submit --}}
                            <div id="hidden-inputs-container">
                                <input type="hidden" name="primary_image_index" id="primary_image_index">
                                <input type="hidden" name="deleted_image_ids" id="deleted_image_ids"
                                    value="{{ old('deleted_image_ids') }}">
                                {{-- Input file thật sẽ được JS append vào đây trước khi submit --}}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-end mt-4 pb-5">
                <button type="submit" class="btn btn-primary btn-lg px-5 shadow"><i class="fas fa-save me-2"></i> Lưu
                    thay đổi</button>
            </div>
        </form>
    </div>

    {{-- DỮ LIỆU JSON ĐỂ JS KHÔI PHỤC TRẠNG THÁI --}}
    <script id="venue-images-data" type="application/json">{!! json_encode($venue->images) !!}</script>
    <script id="old-links-data" type="application/json">{!! json_encode(old('image_links', [])) !!}</script>

    {{-- SCRIPTS --}}
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
        // --- 1. SETUP MAP & FLATPICKR ---
        flatpickr(".time-picker", {
            enableTime: true,
            noCalendar: true,
            dateFormat: "H:i",
            time_24hr: true
        });

        // Map setup
        let lat = parseFloat($('#lat').val()) || 21.028511;
        let lng = parseFloat($('#lng').val()) || 105.854444;
        var map = L.map('map').setView([lat, lng], 13);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap'
        }).addTo(map);
        var marker = L.marker([lat, lng], {
            draggable: true
        }).addTo(map);

        function updateMarker(l, n) {
            $('#lat').val(l.toFixed(6));
            $('#lng').val(n.toFixed(6));
        }
        marker.on('dragend', function(e) {
            var c = e.target.getLatLng();
            updateMarker(c.lat, c.lng);
        });
        map.on('click', function(e) {
            marker.setLatLng(e.latlng);
            updateMarker(e.latlng.lat, e.latlng.lng);
        });

        // --- 2. SETUP ADDRESS ---
        const apiHost = '/api-proxy/provinces'; // Đảm bảo route này tồn tại
        const $province = $('#province_id'),
            $district = $('#district_id');

        function loadOptions($el, url, ph, sel = null) {
            $.get(url, function(data) {
                let h = `<option value="">${ph}</option>`;
                data.forEach(i => h +=
                    `<option value="${i.code}" ${sel == i.code ? 'selected' : ''}>${i.name}</option>`);
                $el.html(h).prop('disabled', false);
            });
        }

        // Load initial
        loadOptions($province, apiHost, '-- Chọn Tỉnh/Thành --', $province.data('old'));
        if ($province.data('old')) loadOptions($district, `/api-proxy/districts/${$province.data('old')}`,
            '-- Chọn Quận/Huyện --', $district.data('old'));

        $province.change(function() {
            const code = $(this).val();
            if (code) {
                // Update map view based on province (Optional hardcoded coords)
                const cityCoords = {
                    '01': [21.0285, 105.8542],
                    '79': [10.8231, 106.6297]
                };
                if (cityCoords[code]) {
                    map.setView(cityCoords[code], 12);
                    marker.setLatLng(cityCoords[code]);
                    updateMarker(cityCoords[code][0], cityCoords[code][1]);
                }
                loadOptions($district, `/api-proxy/districts/${code}`, '-- Chọn Quận/Huyện --');
            } else {
                $district.html('<option>-- Chọn Tỉnh trước --</option>').prop('disabled', true);
            }
        });

        // --- 3. IMAGE MANAGEMENT ---
        $(document).ready(function() {
            const MAX_FILES = 5;
            const $preview = $('#image-preview-container');
            const $deletedInput = $('#deleted_image_ids');
            const $primaryInput = $('#primary_image_index');
            const $fileInput = $('#file_input');
            const $hiddenContainer = $('#hidden-inputs-container');

            let state = {
                existing: JSON.parse($('#venue-images-data').text() || '[]').map(i => ({
                    ...i,
                    type: 'existing',
                    uniqueKey: `existing_${i.id}`
                })),
                newFiles: [], // {file: File, uniqueKey: string}
                newLinks: JSON.parse($('#old-links-data').text() || '[]').map((u, i) => ({
                    url: u,
                    type: 'link',
                    uniqueKey: `new_link_${i}`
                })),
                deletedIds: $deletedInput.val() ? $deletedInput.val().split(',') : []
            };

            function render() {
                $preview.empty();
                $hiddenContainer.find('.dynamic-link').remove();

                // Merge & Filter
                const displayImages = [
                    ...state.existing.filter(i => !state.deletedIds.includes(String(i.id))),
                    ...state.newFiles,
                    ...state.newLinks
                ];

                // Auto select primary if invalid
                let currentPrimary = $primaryInput.val();
                if (!displayImages.some(i => i.uniqueKey === currentPrimary)) {
                    // Try to find old primary
                    const oldPrimary = state.existing.find(i => i.is_primary == 1 && !state.deletedIds.includes(
                        String(i.id)));
                    currentPrimary = oldPrimary ? oldPrimary.uniqueKey : (displayImages.length ? displayImages[0]
                        .uniqueKey : '');
                    $primaryInput.val(currentPrimary);
                }

                // Render UI
                displayImages.forEach(img => {
                    let src = img.type === 'file' ? URL.createObjectURL(img.file) : img.url;
                    let isChecked = img.uniqueKey === currentPrimary ? 'checked' : '';

                    const html = `
                        <div class="col-6 col-md-4">
                            <div class="border rounded p-2 position-relative bg-white text-center h-100 shadow-sm">
                                <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1"
                                    onclick="removeImg('${img.uniqueKey}')" style="z-index:5;">&times;</button>
                                <div style="height: 100px; overflow: hidden;" class="d-flex align-items-center justify-content-center mb-2 bg-light">
                                    <img src="${src}" class="img-fluid" style="max-height: 100%;" onerror="this.src='https://via.placeholder.com/150'">
                                </div>
                                <div class="form-check d-flex justify-content-center">
                                    <input class="form-check-input me-1" type="radio" name="primary_ui"
                                        ${isChecked} onchange="setPrimary('${img.uniqueKey}')">
                                    <label class="form-check-label small">Ảnh chính</label>
                                </div>
                            </div>
                        </div>`;
                    $preview.append(html);

                    if (img.type === 'link') {
                        $hiddenContainer.append(
                            `<input type="hidden" name="image_links[]" value="${img.url}" class="dynamic-link">`
                            );
                    }
                });
                $deletedInput.val(state.deletedIds.join(','));
            }

            // Helpers global for inline onclick
            window.removeImg = function(key) {
                if (key.startsWith('existing_')) state.deletedIds.push(key.replace('existing_', ''));
                else if (key.startsWith('new_file_')) state.newFiles = state.newFiles.filter(i => i
                    .uniqueKey !== key);
                else if (key.startsWith('new_link_')) state.newLinks = state.newLinks.filter(i => i
                    .uniqueKey !== key);
                render();
            };
            window.setPrimary = function(key) {
                $primaryInput.val(key);
            };

            // Add File
            $fileInput.change(function(e) {
                const files = Array.from(e.target.files);
                const currentTotal = (state.existing.length - state.deletedIds.length) + state.newFiles
                    .length + state.newLinks.length;
                if (currentTotal + files.length > MAX_FILES) return alert(`Tối đa ${MAX_FILES} ảnh.`);

                files.forEach(f => {
                    state.newFiles.push({
                        type: 'file',
                        file: f,
                        uniqueKey: `new_file_${Date.now()}_${Math.random()}`
                    });
                });
                $fileInput.val('');
                render();
            });

            // Add Link
            $('#btn-add-link').click(function() {
                const url = $('#link_input').val().trim();
                if (!url) return;
                const currentTotal = (state.existing.length - state.deletedIds.length) + state.newFiles
                    .length + state.newLinks.length;
                if (currentTotal >= MAX_FILES) return alert(`Tối đa ${MAX_FILES} ảnh.`);

                state.newLinks.push({
                    type: 'link',
                    url: url,
                    uniqueKey: `new_link_${Date.now()}`
                });
                $('#link_input').val('');
                render();
            });

            // --- 4. FORM SUBMIT VALIDATION & PREP ---
            $('#venue-edit-form').on('submit', function(e) {
                // Clear old errors
                $('.is-invalid').removeClass('is-invalid');
                $('.custom-error-msg').remove();
                let isValid = true;
                let firstErr = null;

                function err(sel, msg) {
                    const el = $(sel);
                    el.addClass('is-invalid');
                    if (!el.next('.custom-error-msg').length) el.after(
                        `<div class="custom-error-msg">${msg}</div>`);
                    isValid = false;
                    if (!firstErr) firstErr = el;
                }

                // Logic checks
                if (!$('input[name="name"]').val().trim()) err('input[name="name"]',
                'Nhập tên thương hiệu');
                if (!$('#province_id').val()) err('#province_id', 'Chọn Tỉnh/Thành');
                if (!$('.venue-type-checkbox:checked').length) err('.check-group',
                    'Chọn ít nhất 1 loại hình');

                // Image check
                const activeCount = (state.existing.length - state.deletedIds.length) + state.newFiles
                    .length + state.newLinks.length;
                if (activeCount === 0) {
                    alert('Vui lòng chọn ít nhất 1 ảnh.');
                    isValid = false;
                }

                if (!isValid) {
                    e.preventDefault();
                    if (firstErr) $('html, body').animate({
                        scrollTop: $(firstErr).offset().top - 100
                    }, 500);
                } else {
                    // PREPARE FILES FOR SUBMIT
                    // Create a hidden file input with accumulated files using DataTransfer
                    const dt = new DataTransfer();
                    state.newFiles.forEach(f => dt.items.add(f.file));

                    // Remove old input to avoid duplicates if any, create new one
                    $('#real_file_input').remove();
                    const hiddenFile = $(
                        '<input type="file" name="new_files[]" id="real_file_input" multiple class="d-none">'
                        );
                    $hiddenContainer.append(hiddenFile);
                    hiddenFile[0].files = dt.files;
                }
            });

            render(); // Init
        });
    </script>
@endsection
