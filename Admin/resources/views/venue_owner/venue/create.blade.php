@extends('app')

@section('content')
    {{-- CSS --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

    <style>
        #map {
            height: 380px;
            width: 100%;
            border-radius: 8px;
            border: 2px solid #e9ecef;
            z-index: 1;
        }

        .preview-box {
            position: relative;
            width: 100%;
            padding-top: 75%;
            border-radius: 6px;
            overflow: hidden;
            border: 1px solid #dee2e6;
            background: #f8f9fa;
        }

        .preview-box img {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .btn-del-img {
            position: absolute;
            top: 5px;
            right: 5px;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background: rgba(220, 53, 69, 0.9);
            color: #fff;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 14px;
            z-index: 10;
            transition: all 0.2s;
        }

        .btn-del-img:hover {
            background: #dc3545;
            transform: scale(1.1);
        }

        .primary-badge {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            background: rgba(0, 0, 0, 0.6);
            color: #fff;
            font-size: 11px;
            text-align: center;
            padding: 4px 0;
            cursor: pointer;
            backdrop-filter: blur(2px);
        }

        .primary-badge.active {
            background: rgba(25, 135, 84, 0.9);
            font-weight: bold;
        }

        .court-item {
            border: 1px solid #e2e8f0;
            transition: all 0.3s;
        }

        .court-item:hover {
            border-color: #3b82f6;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
    </style>

    <div class="container-fluid py-4">
        {{-- HEADER --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="h4 fw-bold text-uppercase mb-1">Đăng ký sân mới</h2>
                <p class="text-muted small mb-0">Điền thông tin chi tiết để bắt đầu vận hành.</p>
            </div>
            <a href="{{ url()->previous() }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>
                Quay lại</a>
        </div>

        <form id="venue-form"
            action="{{ route(auth()->user()->role->name === 'admin' ? 'admin.venues.store' : 'owner.venues.store') }}"
            method="POST" enctype="multipart/form-data">
            @csrf

            <div class="row g-4">
                {{-- CỘT TRÁI: THÔNG TIN CHÍNH --}}
                <div class="col-lg-8">
                    {{-- 1. THÔNG TIN CHUNG --}}
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-white py-3">
                            <h6 class="fw-bold mb-0 text-primary"><i class="fas fa-info-circle me-2"></i>1. Thông tin cơ bản
                                & Vị trí</h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3 mb-3">
                                <div class="col-md-7">
                                    <label class="form-label fw-bold small">Tên sân/Thương hiệu <span
                                            class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control" value="{{ old('name') }}"
                                        placeholder="VD: Sân Bóng Đá Mỹ Đình" required>
                                    @error('name')
                                        <span class="text-danger small">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label fw-bold small">Chủ sở hữu</label>
                                    @if (auth()->user()->role->name === 'admin')
                                        <select name="owner_id" class="form-select" required>
                                            <option value="">-- Chọn chủ sân --</option>
                                            @foreach ($owners as $o)
                                                <option value="{{ $o->id }}"
                                                    {{ old('owner_id') == $o->id ? 'selected' : '' }}>{{ $o->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    @else
                                        <input type="text" class="form-control bg-light"
                                            value="{{ auth()->user()->name }}" disabled>
                                    @endif
                                </div>
                            </div>

                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold small">Tỉnh/Thành phố <span
                                            class="text-danger">*</span></label>
                                    <select name="province_id" id="province_id" class="form-select" required>
                                        <option value="">-- Chọn --</option>
                                        @foreach ($provinces as $p)
                                            <option value="{{ $p->id }}"
                                                {{ old('province_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('province_id')
                                        <span class="text-danger small">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold small">Quận/Huyện <span
                                            class="text-danger">*</span></label>
                                    <select name="district_id" id="district_id" class="form-select" required disabled>
                                        <option value="">-- Chọn Tỉnh trước --</option>
                                    </select>
                                    @error('district_id')
                                        <span class="text-danger small">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold small">Địa chỉ chi tiết <span
                                        class="text-danger">*</span></label>
                                <input type="text" name="address_detail" class="form-control"
                                    value="{{ old('address_detail') }}" placeholder="Số nhà, ngõ, đường..." required>
                            </div>

                            <div class="mb-2">
                                <label class="form-label fw-bold small text-success"><i
                                        class="fas fa-map-marker-alt me-1"></i> Chọn vị trí trên bản đồ</label>
                                <div id="map"></div>
                                <div class="row g-2 mt-2">
                                    <div class="col-6"><input type="text" name="lat" id="lat"
                                            class="form-control form-control-sm bg-light"
                                            value="{{ old('lat', '21.0285') }}" readonly title="Vĩ độ"></div>
                                    <div class="col-6"><input type="text" name="lng" id="lng"
                                            class="form-control form-control-sm bg-light"
                                            value="{{ old('lng', '105.8544') }}" readonly title="Kinh độ"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 2. CHI TIẾT SÂN CON --}}
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                            <h6 class="fw-bold mb-0 text-success"><i class="fas fa-layer-group me-2"></i>2. Danh sách Sân &
                                Giá</h6>
                            <button type="button" class="btn btn-sm btn-success shadow-sm" id="btn-add-court"><i
                                    class="fas fa-plus me-1"></i> Thêm sân con</button>
                        </div>
                        <div class="card-body bg-light" id="courts-wrapper">
                            {{-- Sân con sẽ được JS render vào đây --}}
                            <div id="empty-court-msg" class="text-center text-muted py-4">
                                <i class="fas fa-plus-circle fa-2x mb-2 opacity-50"></i>
                                <p class="small">Chưa có sân nào. Nhấn "Thêm sân con" để bắt đầu.</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- CỘT PHẢI: CẤU HÌNH & ẢNH --}}
                <div class="col-lg-4">
                    {{-- 3. CÀI ĐẶT VẬN HÀNH --}}
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-white py-3">
                            <h6 class="fw-bold mb-0"><i class="fas fa-cogs me-2"></i>3. Cài đặt vận hành</h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label fw-bold small">Mô hình kinh doanh <span
                                        class="text-danger">*</span></label>
                                <div class="border rounded p-2 bg-white" style="max-height: 150px; overflow-y: auto;">
                                    @foreach ($venue_types as $vt)
                                        <div class="form-check">
                                            <input class="form-check-input chk-venue-type" type="checkbox"
                                                name="venue_types[]" value="{{ $vt->id }}"
                                                id="vt_{{ $vt->id }}"
                                                {{ in_array($vt->id, old('venue_types', [])) ? 'checked' : '' }}>
                                            <label class="form-check-label small"
                                                for="vt_{{ $vt->id }}">{{ $vt->name }}</label>
                                        </div>
                                    @endforeach
                                </div>
                                @error('venue_types')
                                    <span class="text-danger small d-block">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <label class="fw-bold small">Mở cửa</label>
                                    <input type="text" name="start_time"
                                        class="form-control flatpickr-time text-center"
                                        value="{{ old('start_time', '05:00') }}">
                                </div>
                                <div class="col-6">
                                    <label class="fw-bold small">Đóng cửa</label>
                                    <input type="text" name="end_time" class="form-control flatpickr-time text-center"
                                        value="{{ old('end_time', '23:00') }}">
                                </div>
                            </div>

                            <div class="mb-2">
                                <label class="fw-bold small">Hotline liên hệ</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fas fa-phone"></i></span>
                                    <input type="text" name="phone" class="form-control"
                                        value="{{ old('phone') }}" placeholder="0912...">
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 4. HÌNH ẢNH --}}
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-white py-3">
                            <h6 class="fw-bold mb-0"><i class="fas fa-images me-2"></i>4. Hình ảnh & Giấy tờ</h6>
                        </div>
                        <div class="card-body">
                            {{-- Ảnh sân --}}
                            <div class="mb-4">
                                <label class="form-label fw-bold small">Ảnh sân (Tối đa 5) <span
                                        class="text-danger">*</span></label>
                                <input type="file" id="input-venue-imgs" class="form-control form-control-sm mb-2"
                                    accept="image/*" multiple>
                                <input type="hidden" name="primary_image_index" id="primary_image_index"
                                    value="0">
                                <div class="row g-2" id="preview-venue-imgs"></div>
                                @error('images')
                                    <span class="text-danger small">{{ $message }}</span>
                                @enderror
                            </div>

                            <hr class="text-muted">

                            {{-- Giấy tờ --}}
                            <div class="mb-2">
                                <label class="form-label fw-bold small">Giấy tờ pháp lý <span
                                        class="text-danger">*</span></label>
                                <input type="file" id="input-doc-imgs" class="form-control form-control-sm mb-2"
                                    accept="image/*" multiple>
                                <div class="row g-2" id="preview-doc-imgs"></div>
                                @error('document_images')
                                    <span class="text-danger small">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold shadow-sm text-uppercase">
                        <i class="fas fa-check-circle me-2"></i> Hoàn tất đăng ký
                    </button>
                </div>
            </div>

            {{-- FILE INPUTS ẨN (Dùng để submit) --}}
            <div class="d-none">
                <input type="file" name="images[]" id="final-venue-imgs" multiple>
                <input type="file" name="document_images[]" id="final-doc-imgs" multiple>
            </div>
        </form>
    </div>

    {{-- SCRIPTS --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <script>
        $(document).ready(function() {
            // ===============================================
            // 1. LOGIC LỌC QUẬN HUYỆN (CLIENT-SIDE)
            // ===============================================
            const allDistricts = @json($allDistricts);
            const oldDistrictId = "{{ old('district_id') }}";
            const oldProvinceId = "{{ old('province_id') }}";

            function filterDistricts(provinceId) {
                const $d = $('#district_id');
                $d.empty();

                if (!provinceId) {
                    $d.html('<option value="">-- Chọn Tỉnh trước --</option>').prop('disabled', true);
                    return;
                }

                const filtered = allDistricts.filter(item => item.province_id == provinceId);

                if (filtered.length > 0) {
                    let html = '<option value="">-- Chọn Quận/Huyện --</option>';
                    filtered.forEach(item => {
                        const isSelected = (item.id == oldDistrictId) ? 'selected' : '';
                        html += `<option value="${item.id}" ${isSelected}>${item.name}</option>`;
                    });
                    $d.html(html).prop('disabled', false);
                } else {
                    $d.html('<option value="">-- Không có dữ liệu --</option>');
                }
            }

            $('#province_id').on('change', function() {
                filterDistricts($(this).val());
            });

            // Init on load
            if (oldProvinceId) {
                filterDistricts(oldProvinceId);
            }

            // ===============================================
            // 2. MAP LOGIC (CLICK & DRAG)
            // ===============================================
            const latInput = $('#lat');
            const lngInput = $('#lng');
            const map = L.map('map').setView([latInput.val(), lngInput.val()], 15);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

            const marker = L.marker([latInput.val(), lngInput.val()], {
                draggable: true
            }).addTo(map);

            function updateMarker(lat, lng) {
                marker.setLatLng([lat, lng]);
                latInput.val(lat.toFixed(6));
                lngInput.val(lng.toFixed(6));
            }

            marker.on('dragend', function(e) {
                const pos = marker.getLatLng();
                updateMarker(pos.lat, pos.lng);
            });

            map.on('click', function(e) {
                updateMarker(e.latlng.lat, e.latlng.lng);
            });

            // ===============================================
            // 3. IMAGE HANDLING (FILE LIST & PREVIEW)
            // ===============================================
            let venueFiles = [];
            let docFiles = [];

            function renderPreview(files, containerId, isVenue = false) {
                const $cont = $(`#${containerId}`).empty();
                files.forEach((file, index) => {
                    const url = URL.createObjectURL(file);
                    const isPrimary = isVenue && index == $('#primary_image_index').val();

                    let html = `
                        <div class="col-4">
                            <div class="preview-box">
                                <img src="${url}">
                                <button type="button" class="btn-del-img" onclick="removeFile('${containerId}', ${index})">&times;</button>
                                ${isVenue ? `<div class="primary-badge ${isPrimary ? 'active' : ''}" onclick="setPrimary(${index})"><i class="far ${isPrimary ? 'fa-check-circle' : 'fa-circle'}"></i> Chính</div>` : ''}
                            </div>
                        </div>`;
                    $cont.append(html);
                });
            }

            window.setPrimary = function(index) {
                $('#primary_image_index').val(index);
                renderPreview(venueFiles, 'preview-venue-imgs', true);
            }

            window.removeFile = function(contId, index) {
                if (contId === 'preview-venue-imgs') {
                    venueFiles.splice(index, 1);
                    let currPrimary = parseInt($('#primary_image_index').val());
                    if (currPrimary == index) $('#primary_image_index').val(0);
                    else if (currPrimary > index) $('#primary_image_index').val(currPrimary - 1);
                    renderPreview(venueFiles, contId, true);
                } else {
                    docFiles.splice(index, 1);
                    renderPreview(docFiles, contId, false);
                }
            }

            $('#input-venue-imgs').change(function() {
                venueFiles = [...venueFiles, ...Array.from(this.files)].slice(0, 5);
                renderPreview(venueFiles, 'preview-venue-imgs', true);
                $(this).val('');
            });

            $('#input-doc-imgs').change(function() {
                docFiles = [...docFiles, ...Array.from(this.files)];
                renderPreview(docFiles, 'preview-doc-imgs', false);
                $(this).val('');
            });

            // ===============================================
            // 4. DYNAMIC COURTS (SÂN CON & TIME SLOTS)
            // ===============================================
            let courtIdx = 0;

            function updateCourtTypeSelects() {
                let types = [];
                $('.chk-venue-type:checked').each(function() {
                    types.push({
                        id: $(this).val(),
                        name: $(this).next('label').text()
                    });
                });

                let opts = '<option value="">-- Chọn --</option>';
                types.forEach(t => opts += `<option value="${t.id}">${t.name}</option>`);

                $('.court-type-select').each(function() {
                    const oldVal = $(this).val();
                    $(this).html(opts).val(oldVal);
                });
            }

            $('.chk-venue-type').change(updateCourtTypeSelects);

            $('#btn-add-court').click(function() {
                $('#empty-court-msg').hide();
                const idx = courtIdx++;

                // Get current types
                let types = [];
                $('.chk-venue-type:checked').each(function() {
                    types.push({
                        id: $(this).val(),
                        name: $(this).next('label').text()
                    });
                });
                let typeOpts = '<option value="">-- Chọn --</option>';
                types.forEach(t => typeOpts += `<option value="${t.id}">${t.name}</option>`);

                const html = `
                <div class="court-item bg-white p-3 mb-3 rounded position-relative shadow-sm">
                    <button type="button" class="btn-close position-absolute top-0 end-0 m-2 btn-remove-court"></button>
                    <h6 class="text-primary fw-bold text-uppercase mb-3">Sân Con #${idx + 1}</h6>

                    <div class="row g-2 mb-3">
                        <div class="col-md-4">
                            <label class="small fw-bold">Tên sân</label>
                            <input type="text" name="courts[${idx}][name]" class="form-control form-control-sm" placeholder="Sân 1..." required>
                        </div>
                        <div class="col-md-4">
                            <label class="small fw-bold">Loại hình</label>
                            <select name="courts[${idx}][venue_type_id]" class="form-select form-select-sm court-type-select" required>${typeOpts}</select>
                        </div>
                        <div class="col-md-4">
                            <label class="small fw-bold">Trong nhà/Ngoài trời</label>
                            <select name="courts[${idx}][is_indoor]" class="form-select form-select-sm">
                                <option value="0">Ngoài trời</option>
                                <option value="1">Trong nhà</option>
                            </select>
                        </div>
                    </div>

                    <div class="bg-light p-2 rounded border">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="small fw-bold text-muted" style="font-size:11px">BẢNG GIÁ & KHUNG GIỜ</span>
                            <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none btn-add-slot" style="font-size:11px">+ Thêm khung giờ</button>
                        </div>
                        <table class="table table-sm table-borderless mb-0">
                            <tbody class="slot-body">
                                <tr>
                                    <td width="30%"><input type="text" name="courts[${idx}][time_slots][0][start_time]" class="form-control form-control-sm time-pick text-center" placeholder="Bắt đầu" required></td>
                                    <td width="30%"><input type="text" name="courts[${idx}][time_slots][0][end_time]" class="form-control form-control-sm time-pick text-center" placeholder="Kết thúc" required></td>
                                    <td width="30%"><input type="number" name="courts[${idx}][time_slots][0][price]" class="form-control form-control-sm text-center" placeholder="Giá" required></td>
                                    <td width="10%" class="text-center"><button type="button" class="btn btn-link text-success btn-add-slot p-0"><i class="fas fa-plus-circle"></i></button></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>`;

                $('#courts-wrapper').append(html);
                initTimePicker();
            });

            $(document).on('click', '.btn-remove-court', function() {
                $(this).closest('.court-item').remove();
                if ($('#courts-wrapper').children('.court-item').length === 0) $('#empty-court-msg').show();
            });

            $(document).on('click', '.btn-add-slot', function() {
                const tbody = $(this).closest('.court-item').find('.slot-body');
                const courtNameAttr = tbody.find('input').first().attr('name');
                const cIdx = courtNameAttr.match(/courts\[(\d+)\]/)[1];
                const sIdx = Date.now();

                const tr = `
                <tr>
                    <td><input type="text" name="courts[${cIdx}][time_slots][${sIdx}][start_time]" class="form-control form-control-sm time-pick text-center" required></td>
                    <td><input type="text" name="courts[${cIdx}][time_slots][${sIdx}][end_time]" class="form-control form-control-sm time-pick text-center" required></td>
                    <td><input type="number" name="courts[${cIdx}][time_slots][${sIdx}][price]" class="form-control form-control-sm text-center" required></td>
                    <td class="text-center"><button type="button" class="btn btn-link text-danger btn-rem-slot p-0"><i class="fas fa-minus-circle"></i></button></td>
                </tr>`;
                tbody.append(tr);
                initTimePicker();
            });

            $(document).on('click', '.btn-rem-slot', function() {
                $(this).closest('tr').remove();
            });

            function initTimePicker() {
                flatpickr(".time-pick, .flatpickr-time", {
                    enableTime: true,
                    noCalendar: true,
                    dateFormat: "H:i",
                    time_24hr: true
                });
            }
            initTimePicker();

            // ===============================================
            // 5. SUBMIT FORM
            // ===============================================
            $('#venue-form').submit(function(e) {
                // Sync Files
                const dtVenue = new DataTransfer();
                venueFiles.forEach(f => dtVenue.items.add(f));
                document.getElementById('final-venue-imgs').files = dtVenue.files;

                const dtDoc = new DataTransfer();
                docFiles.forEach(f => dtDoc.items.add(f));
                document.getElementById('final-doc-imgs').files = dtDoc.files;

                // Validate sơ bộ
                if ($('.chk-venue-type:checked').length === 0) {
                    alert('Vui lòng chọn ít nhất 1 loại hình kinh doanh.');
                    e.preventDefault();
                    return;
                }
                if (venueFiles.length === 0) {
                    alert('Vui lòng tải lên ít nhất 1 ảnh sân.');
                    e.preventDefault();
                    return;
                }
            });
        });
    </script>
@endsection
