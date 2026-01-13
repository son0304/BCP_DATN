@extends('app')

@section('content')
    {{-- CSS --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

    <style>
        .is-invalid {
            border-color: #dc3545 !important;
            background-image: none !important;
        }

        .invalid-feedback {
            display: block;
            font-size: 0.75rem;
            color: #dc3545;
            font-weight: 600;
            margin-top: 0.25rem;
        }

        #map {
            height: 350px;
            width: 100%;
            border-radius: 12px;
            border: 2px solid #e9ecef;
            z-index: 1;
        }

        .preview-box {
            position: relative;
            width: 100%;
            padding-top: 75%;
            border-radius: 8px;
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
            width: 22px;
            height: 22px;
            border-radius: 50%;
            background: #dc3545;
            color: #fff;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            z-index: 10;
            cursor: pointer;
        }

        .primary-badge {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            background: rgba(0, 0, 0, 0.6);
            color: #fff;
            font-size: 10px;
            text-align: center;
            padding: 4px 0;
            cursor: pointer;
        }

        .primary-badge.active {
            background: #198754;
            font-weight: bold;
        }

        .court-item {
            border: 2px solid #f1f5f9;
            border-radius: 12px;
            background: #fff;
            margin-bottom: 2rem;
            transition: all 0.3s;
        }

        .court-item:hover {
            border-color: #0d6efd;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }

        .court-header {
            background: #f8fafc;
            padding: 12px 20px;
            border-radius: 12px 12px 0 0;
            border-bottom: 1px solid #eee;
        }

        .table th {
            font-size: 11px;
            text-transform: uppercase;
            background: #f8f9fa;
            color: #666;
        }
    </style>

    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="h4 fw-bold text-uppercase mb-1">Thiết lập hệ thống sân mới</h2>
                <p class="text-muted small mb-0">Vui lòng điền đầy đủ thông tin để hoàn tất hồ sơ đăng ký.</p>
            </div>
            <a href="{{ url()->previous() }}" class="btn btn-outline-secondary btn-sm shadow-sm">
                <i class="fas fa-arrow-left me-1"></i> Quay lại
            </a>
        </div>

        <form id="venue-form"
            action="{{ route(auth()->user()->role->name === 'admin' ? 'admin.venues.store' : 'owner.venues.store') }}"
            method="POST" enctype="multipart/form-data" novalidate>
            @csrf

            <div class="row g-4">
                {{-- CỘT TRÁI: THÔNG TIN CHUNG & SÂN CON --}}
                <div class="col-lg-8">
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-white py-3 border-bottom">
                            <h6 class="fw-bold mb-0 text-primary uppercase"><i class="fas fa-id-card me-2"></i>1. Thông tin
                                thương hiệu & Vị trí</h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-7">
                                    <label class="form-label fw-bold small">Tên thương hiệu *</label>
                                    <input type="text" name="name"
                                        class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}"
                                        placeholder="VD: Sân bóng Hùng Vương">
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label fw-bold small">Chủ sở hữu</label>
                                    @if (auth()->user()->role->name === 'admin')
                                        <select name="owner_id" class="form-select @error('owner_id') is-invalid @enderror">
                                            <option value="">-- Chọn chủ sở hữu --</option>
                                            @foreach ($owners as $o)
                                                <option value="{{ $o->id }}">{{ $o->name }}</option>
                                            @endforeach
                                        </select>
                                    @else
                                        <input type="text" class="form-control bg-light"
                                            value="{{ auth()->user()->name }}" disabled>
                                    @endif
                                    @error('owner_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold small">Tỉnh/Thành phố *</label>
                                    <select name="province_id" id="province_id"
                                        class="form-select @error('province_id') is-invalid @enderror">
                                        <option value="">-- Chọn Tỉnh --</option>
                                        @foreach ($provinces as $p)
                                            <option value="{{ $p->id }}"
                                                {{ old('province_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('province_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold small">Quận/Huyện *</label>
                                    <select name="district_id" id="district_id"
                                        class="form-select @error('district_id') is-invalid @enderror" disabled>
                                        <option value="">-- Chọn Quận --</option>
                                    </select>
                                    @error('district_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label fw-bold small">Địa chỉ chi tiết *</label>
                                    <input type="text" name="address_detail"
                                        class="form-control @error('address_detail') is-invalid @enderror"
                                        value="{{ old('address_detail') }}" placeholder="Số nhà, đường...">
                                    @error('address_detail')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-12">
                                    <label class="small fw-bold text-success mb-2 d-block">Vị trí bản đồ (Kéo thả marker
                                        hoặc click bản đồ)</label>
                                    <div id="map"></div>
                                    <div class="row g-2 mt-2">
                                        <div class="col-6"><input type="text" name="lat" id="lat"
                                                class="form-control form-control-sm bg-light"
                                                value="{{ old('lat', '21.0285') }}" readonly></div>
                                        <div class="col-6"><input type="text" name="lng" id="lng"
                                                class="form-control form-control-sm bg-light"
                                                value="{{ old('lng', '105.8544') }}" readonly></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                            <h6 class="fw-bold mb-0 text-success uppercase"><i class="fas fa-volleyball-ball me-2"></i>2.
                                Danh sách sân con & Giá</h6>
                            <button type="button" class="btn btn-sm btn-success px-3 shadow-sm" id="btn-add-court">Thêm sân
                                con</button>
                        </div>
                        <div class="card-body bg-light p-4" id="courts-wrapper">
                            {{-- JS render sân con --}}
                        </div>
                    </div>
                </div>

                {{-- CỘT PHẢI: VẬN HÀNH & HÌNH ẢNH --}}
                <div class="col-lg-4">
                    <div class="card shadow-sm border-0 mb-4 sticky-top" style="top: 20px;">
                        <div class="card-header bg-dark text-white py-3">
                            <h6 class="fw-bold mb-0"><i class="fas fa-cog me-2"></i>3. Cài đặt vận hành</h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-4">
                                <label class="fw-bold small mb-2 d-block">Loại hình kinh doanh *</label>
                                <div class="p-3 border rounded bg-white @error('venue_types') is-invalid @enderror"
                                    id="venue_types_container" style="max-height: 180px; overflow-y: auto;">
                                    @foreach ($venue_types as $vt)
                                        <div class="form-check mb-2">
                                            <input class="form-check-input chk-venue-type" type="checkbox"
                                                name="venue_types[]" value="{{ $vt->id }}"
                                                id="vt_{{ $vt->id }}">
                                            <label class="form-check-label small"
                                                for="vt_{{ $vt->id }}">{{ $vt->name }}</label>
                                        </div>
                                    @endforeach
                                </div>
                                @error('venue_types')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="row g-2 mb-4">
                                <div class="col-6">
                                    <label class="small fw-bold">Giờ mở cửa *</label>
                                    <input type="text" name="start_time"
                                        class="form-control flatpickr-time text-center" value="05:00">
                                </div>
                                <div class="col-6">
                                    <label class="small fw-bold">Giờ đóng cửa *</label>
                                    <input type="text" name="end_time" class="form-control flatpickr-time text-center"
                                        value="23:00">
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="small fw-bold">Hotline liên hệ</label>
                                <input type="text" name="phone" class="form-control" placeholder="VD: 0912345678">
                            </div>

                            <hr>

                            <div class="mb-4">
                                <label class="small fw-bold mb-2 d-block">Ảnh sân (Tối đa 5) *</label>
                                <input type="file" id="input-venue-imgs" class="form-control form-control-sm mb-2"
                                    accept="image/*" multiple>
                                <input type="hidden" name="primary_image_index" id="primary_image_index"
                                    value="0">
                                <div class="row g-2" id="preview-venue-imgs"></div>
                            </div>

                            <div class="mb-4">
                                <label class="small fw-bold mb-2 d-block">Giấy tờ pháp lý *</label>
                                <input type="file" id="input-doc-imgs" class="form-control form-control-sm mb-2"
                                    accept="image/*" multiple>
                                <div class="row g-2" id="preview-doc-imgs"></div>
                            </div>

                            <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold shadow">HOÀN TẤT ĐĂNG
                                KÝ</button>
                        </div>
                    </div>
                </div>
            </div>

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
            // 1. UTILS (TIME CONVERSION)
            // ===============================================
            function timeToMinutes(str) {
                if (!str) return 0;
                let [h, m] = str.split(':').map(Number);
                if (str === "24:00") return 1440;
                return h * 60 + m;
            }

            function minutesToTime(mins) {
                let h = Math.floor(mins / 60);
                let m = mins % 60;
                if (h === 24) return "24:00";
                return `${String(h).padStart(2,'0')}:${String(m).padStart(2,'0')}`;
            }

            function initTimePicker(selector) {
                flatpickr(selector, {
                    enableTime: true,
                    noCalendar: true,
                    dateFormat: "H:i",
                    time_24hr: true,
                    minuteIncrement: 30,
                    allowInput: true
                });
            }

            // ===============================================
            // 2. IMAGE HANDLING
            // ===============================================
            let venueFiles = [];
            let docFiles = [];

            function renderPreviews(files, containerId, isVenue = false) {
                const $cont = $(`#${containerId}`).empty();
                files.forEach((file, i) => {
                    const url = URL.createObjectURL(file);
                    const isPrimary = isVenue && i == $('#primary_image_index').val();
                    $cont.append(`
                        <div class="col-4">
                            <div class="preview-box">
                                <img src="${url}">
                                <button type="button" class="btn-del-img" onclick="removeImg('${containerId}', ${i})">&times;</button>
                                ${isVenue ? `<div class="primary-badge ${isPrimary ? 'active' : ''}" onclick="setPrimary(${i})">${isPrimary ? 'ẢNH CHÍNH' : 'ĐẶT CHÍNH'}</div>` : ''}
                            </div>
                        </div>
                    `);
                });
            }

            window.setPrimary = (i) => {
                $('#primary_image_index').val(i);
                renderPreviews(venueFiles, 'preview-venue-imgs', true);
            };
            window.removeImg = (id, i) => {
                if (id.includes('venue')) {
                    venueFiles.splice(i, 1);
                    renderPreviews(venueFiles, id, true);
                } else {
                    docFiles.splice(i, 1);
                    renderPreviews(docFiles, id);
                }
            };

            $('#input-venue-imgs').change(function() {
                venueFiles = [...venueFiles, ...Array.from(this.files)].slice(0, 5);
                renderPreviews(venueFiles, 'preview-venue-imgs', true);
                $(this).val('');
            });
            $('#input-doc-imgs').change(function() {
                docFiles = [...docFiles, ...Array.from(this.files)];
                renderPreviews(docFiles, 'preview-doc-imgs');
                $(this).val('');
            });

            // ===============================================
            // 3. MAP
            // ===============================================
            const map = L.map('map').setView([21.0285, 105.8544], 14);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
            let marker = L.marker([21.0285, 105.8544], {
                draggable: true
            }).addTo(map);
            marker.on('dragend', () => {
                $('#lat').val(marker.getLatLng().lat.toFixed(6));
                $('#lng').val(marker.getLatLng().lng.toFixed(6));
            });
            map.on('click', (e) => {
                marker.setLatLng(e.latlng);
                $('#lat').val(e.latlng.lat.toFixed(6));
                $('#lng').val(e.latlng.lng.toFixed(6));
            });

            // ===============================================
            // 4. DISTRICT FILTER
            // ===============================================
            const allDistricts = @json($allDistricts);
            $('#province_id').change(function() {
                const pid = $(this).val();
                const $d = $('#district_id').empty().prop('disabled', !pid);
                if (pid) {
                    $d.append('<option value="">-- Chọn Quận --</option>');
                    allDistricts.filter(i => i.province_id == pid).forEach(i => $d.append(
                        `<option value="${i.id}">${i.name}</option>`));
                }
            });

            // ===============================================
            // 5. DYNAMIC COURT & SLOTS
            // ===============================================
            let courtIdx = 0;

            function createSlotRowHtml(cIdx, start = '', end = '', price = '') {
                const sIdx = Math.floor(Math.random() * 1000000);
                return `
                <tr class="slot-row">
                    <td><input type="text" name="courts[${cIdx}][time_slots][${sIdx}][start_time]" class="form-control form-control-sm time-start time-pick" value="${start}" placeholder="00:00" required></td>
                    <td><input type="text" name="courts[${cIdx}][time_slots][${sIdx}][end_time]" class="form-control form-control-sm time-end time-pick" value="${end}" placeholder="00:00" required></td>
                    <td><input type="number" name="courts[${cIdx}][time_slots][${sIdx}][price]" class="form-control form-control-sm time-price" value="${price}" placeholder="Giá VNĐ" required></td>
                    <td class="text-center"><button type="button" class="btn btn-sm text-danger remove-slot-btn"><i class="fas fa-trash"></i></button></td>
                </tr>`;
            }

            function updateTypeOptions() {
                let opts = '<option value="">-- Loại sân --</option>';
                $('.chk-venue-type:checked').each(function() {
                    opts += `<option value="${$(this).val()}">${$(this).next('label').text()}</option>`;
                });
                $('.court-type-select').each(function() {
                    const val = $(this).val();
                    $(this).html(opts).val(val);
                });
                return opts;
            }

            $('.chk-venue-type').change(updateTypeOptions);

            $('#btn-add-court').click(function() {
                const idx = courtIdx++;
                const types = updateTypeOptions();
                const html = `
                <div class="court-item shadow-sm" data-idx="${idx}">
                    <div class="court-header d-flex justify-content-between align-items-center">
                        <span class="fw-bold text-primary">SÂN CON #${idx + 1}</span>
                        <button type="button" class="btn btn-sm btn-outline-danger remove-court-btn">&times; Xóa sân</button>
                    </div>
                    <div class="p-3">
                        <div class="row g-2 mb-3">
                            <div class="col-md-5">
                                <label class="small fw-bold">Tên sân hiển thị *</label>
                                <input type="text" name="courts[${idx}][name]" class="form-control form-control-sm court-name-input" required>
                            </div>
                            <div class="col-md-4">
                                <label class="small fw-bold">Loại hình kinh doanh *</label>
                                <select name="courts[${idx}][venue_type_id]" class="form-select form-select-sm court-type-select" required>${types}</select>
                            </div>
                            <div class="col-md-3">
                                <label class="small fw-bold">Trong nhà/Ngoài trời</label>
                                <select name="courts[${idx}][is_indoor]" class="form-select form-select-sm">
                                    <option value="0">Ngoài trời</option>
                                    <option value="1">Trong nhà</option>
                                </select>
                            </div>
                        </div>
                        <div class="bg-light p-3 rounded-3 border">
                            <div class="d-flex justify-content-between mb-2 align-items-center">
                                <span class="small fw-bold uppercase text-muted" style="font-size:10px">Khung giờ & Giá (Tự động chia nhỏ nếu > 60p)</span>
                                <button type="button" class="btn btn-xs btn-outline-success add-slot-btn" style="font-size:10px">+ Thêm giờ</button>
                            </div>
                            <table class="table table-sm table-bordered bg-white mb-0">
                                <thead><tr class="text-center small"><th>Bắt đầu</th><th>Kết thúc</th><th>Giá (VNĐ)</th><th>#</th></tr></thead>
                                <tbody class="slot-container">${createSlotRowHtml(idx)}</tbody>
                            </table>
                        </div>
                    </div>
                </div>`;
                $('#courts-wrapper').append(html);
                initTimePicker('.time-pick');
            });

            // ===============================================
            // 6. SPLIT SLOT LOGIC (60 MINS)
            // ===============================================
            $(document).on('change', '.time-end, .time-price', function() {
                const row = $(this).closest('tr');
                const cIdx = $(this).closest('.court-item').data('idx');
                const startVal = row.find('.time-start').val();
                const endVal = row.find('.time-end').val();
                const priceVal = row.find('.time-price').val();

                if (!startVal || !endVal || !priceVal) return;
                const startMin = timeToMinutes(startVal);
                const endMin = (endVal === "00:00" || endVal === "24:00") ? 1440 : timeToMinutes(endVal);

                if (endMin <= startMin) {
                    if ($(this).hasClass('time-end')) {
                        alert('Giờ kết thúc phải lớn hơn giờ bắt đầu!');
                        $(this).val('');
                    }
                    return;
                }

                if ((endMin - startMin) > 60) {
                    if (confirm(
                            `Bạn nhập khoảng ${(endMin-startMin)} phút. Có muốn tự động chia thành các khung 60 phút không?`
                            )) {
                        const container = row.closest('.slot-container');
                        row.remove();
                        let curr = startMin;
                        while (curr < endMin) {
                            let next = Math.min(curr + 60, endMin);
                            const newRow = $(createSlotRowHtml(cIdx, minutesToTime(curr), minutesToTime(
                                next), priceVal));
                            container.append(newRow);
                            initTimePicker(newRow.find('.time-pick'));
                            curr = next;
                        }
                    }
                }
            });

            $(document).on('click', '.add-slot-btn', function() {
                const cIdx = $(this).closest('.court-item').data('idx');
                const row = $(createSlotRowHtml(cIdx));
                $(this).closest('.court-item').find('.slot-container').append(row);
                initTimePicker(row.find('.time-pick'));
            });

            $(document).on('click', '.remove-slot-btn', function() {
                const container = $(this).closest('.slot-container');
                if (container.find('tr').length > 1) $(this).closest('tr').remove();
            });

            $(document).on('click', '.remove-court-btn', function() {
                $(this).closest('.court-item').remove();
            });

            // ===============================================
            // 7. FINAL SUBMIT VALIDATION
            // ===============================================
            $('#venue-form').on('submit', function(e) {
                let valid = true;
                $('.is-invalid').removeClass('is-invalid');
                $('.invalid-feedback').remove();

                function setErr($el, msg) {
                    valid = false;
                    $el.addClass('is-invalid');
                    if ($el.next('.invalid-feedback').length === 0) {
                        $el.after(`<div class="invalid-feedback">${msg}</div>`);
                    }
                }

                // Brand Validation
                if (!$('input[name="name"]').val()) setErr($('input[name="name"]'),
                    'Tên thương hiệu không được trống.');
                if (!$('#province_id').val()) setErr($('#province_id'), 'Vui lòng chọn Tỉnh.');
                if (!$('#district_id').val()) setErr($('#district_id'), 'Vui lòng chọn Huyện.');
                if (!$('input[name="address_detail"]').val()) setErr($('input[name="address_detail"]'),
                    'Vui lòng nhập địa chỉ.');
                if (!$('.chk-venue-type:checked').length) {
                    $('#venue_types_container').addClass('is-invalid');
                    $('#venue_types_container').after(
                        '<div class="invalid-feedback">Chọn ít nhất 1 loại hình kinh doanh.</div>');
                    valid = false;
                }

                // Court Validation
                if ($('.court-item').length === 0) {
                    alert('Vui lòng thêm ít nhất 1 sân.');
                    valid = false;
                }

                $('.court-item').each(function() {
                    const name = $(this).find('.court-name-input');
                    if (!name.val()) setErr(name, 'Nhập tên sân.');

                    const typeS = $(this).find('.court-type-select');
                    if (!typeS.val()) setErr(typeS, 'Chọn loại hình cho sân này.');

                    $(this).find('.slot-row').each(function() {
                        const s = $(this).find('.time-start'),
                            e = $(this).find('.time-end'),
                            p = $(this).find('.time-price');
                        if (!s.val()) s.addClass('is-invalid');
                        if (!e.val()) e.addClass('is-invalid');
                        if (!p.val()) p.addClass('is-invalid');
                        if (s.val() && e.val() && timeToMinutes(e.val()) <= timeToMinutes(s
                                .val())) {
                            setErr(e, 'Giờ không hợp lệ.');
                        }
                    });
                });

                // Image Validation
                if (venueFiles.length === 0) {
                    alert('Phải có ít nhất 1 ảnh sân.');
                    valid = false;
                }
                if (docFiles.length === 0) {
                    alert('Phải có ít nhất 1 ảnh giấy tờ.');
                    valid = false;
                }

                if (!valid) {
                    e.preventDefault();
                    $('html, body').animate({
                        scrollTop: $('.is-invalid').first().offset().top - 120
                    }, 200);
                } else {
                    // Sync Files
                    const dtV = new DataTransfer();
                    venueFiles.forEach(f => dtV.items.add(f));
                    document.getElementById('final-venue-imgs').files = dtV.files;
                    const dtD = new DataTransfer();
                    docFiles.forEach(f => dtD.items.add(f));
                    document.getElementById('final-doc-imgs').files = dtD.files;
                }
            });

            initTimePicker('.flatpickr-time');
            $('#btn-add-court').trigger('click');
        });
    </script>
@endsection
