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

        .custom-error-msg {
            color: #dc3545;
            font-size: 0.875em;
            margin-top: 0.25rem;
        }

        .preview-box {
            height: 100px;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f8f9fa;
            border-radius: 4px;
            border: 1px solid #dee2e6;
        }

        .preview-box img {
            max-height: 100%;
            max-width: 100%;
            object-fit: cover;
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

        <form action="{{ route('owner.venues.update', $venue->id) }}" method="POST" enctype="multipart/form-data"
            id="venue-edit-form" novalidate>
            @csrf
            @method('PUT')

            <div class="row g-4">
                {{-- CỘT TRÁI: THÔNG TIN CƠ BẢN & MAP --}}
                <div class="col-lg-8">
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-white py-3 border-bottom">
                            <h6 class="mb-0 fw-bold text-uppercase text-muted"><i class="fas fa-info-circle me-1"></i> Thông
                                tin cơ bản</h6>
                        </div>
                        <div class="card-body">
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

                            <h6 class="fw-bold small text-muted text-uppercase mb-3">Vị trí & Bản đồ</h6>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Tỉnh/Thành <span class="text-danger">*</span></label>
                                    <select name="province_id" id="province_id" class="form-select"
                                        data-old="{{ old('province_id', $venue->province_id) }}" required></select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Quận/Huyện <span class="text-danger">*</span></label>
                                    <select name="district_id" id="district_id" class="form-select"
                                        data-old="{{ old('district_id', $venue->district_id) }}" required
                                        disabled></select>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Địa chỉ chi tiết <span class="text-danger">*</span></label>
                                <input type="text" name="address_detail" class="form-control"
                                    value="{{ old('address_detail', $venue->address_detail) }}" required>
                            </div>

                            <div id="map"></div>
                            <input type="hidden" name="lat" id="lat" value="{{ old('lat', $venue->lat) }}">
                            <input type="hidden" name="lng" id="lng" value="{{ old('lng', $venue->lng) }}">
                        </div>
                    </div>
                </div>

                {{-- CỘT PHẢI: CẤU HÌNH & ẢNH --}}
                <div class="col-lg-4">
                    {{-- CẤU HÌNH --}}
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Số điện thoại</label>
                                <input type="tel" name="phone" class="form-control"
                                    value="{{ old('phone', $venue->phone) }}">
                            </div>
                            <div class="row">
                                <div class="col-6 mb-3">
                                    <label class="form-label fw-bold small">Mở cửa</label>
                                    <input type="text" name="start_time" class="form-control time-picker"
                                        value="{{ old('start_time', substr($venue->start_time, 0, 5)) }}">
                                </div>
                                <div class="col-6 mb-3">
                                    <label class="form-label fw-bold small">Đóng cửa</label>
                                    <input type="text" name="end_time" class="form-control time-picker"
                                        value="{{ old('end_time', $venue->end_time == '23:59:59' ? '24:00' : substr($venue->end_time, 0, 5)) }}">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold small">Loại hình sân</label>
                                <div class="border rounded p-2 bg-light" style="max-height: 120px; overflow-y: auto;">
                                    @foreach ($venue_types as $type)
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="venue_types[]"
                                                value="{{ $type->id }}" id="vtype_{{ $type->id }}"
                                                {{ in_array($type->id, old('venue_types', $venue->venueTypes->pluck('id')->toArray())) ? 'checked' : '' }}>
                                            <label class="form-check-label small"
                                                for="vtype_{{ $type->id }}">{{ $type->name }}</label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ẢNH THƯƠNG HIỆU (VENUE) --}}
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-white py-3 border-bottom">
                            <h6 class="mb-0 fw-bold text-uppercase text-muted small"><i class="fas fa-camera me-1"></i>
                                Ảnh thương hiệu (Max 5)</h6>
                        </div>
                        <div class="card-body">
                            <ul class="nav nav-tabs nav-fill small mb-2" id="venueImgTabs">
                                <li class="nav-item"><button class="nav-link active py-1" data-bs-toggle="tab"
                                        data-bs-target="#v-file" type="button">File</button></li>
                                <li class="nav-item"><button class="nav-link py-1" data-bs-toggle="tab"
                                        data-bs-target="#v-link" type="button">Link</button></li>
                            </ul>
                            <div class="tab-content border p-2 mb-3 bg-light rounded">
                                <div class="tab-pane fade show active" id="v-file">
                                    <input type="file" id="venue_file_input" class="form-control form-control-sm"
                                        accept="image/*" multiple>
                                </div>
                                <div class="tab-pane fade" id="v-link">
                                    <div class="input-group input-group-sm">
                                        <input type="url" id="venue_link_input" class="form-control"
                                            placeholder="https://...">
                                        <button type="button" class="btn btn-primary" id="btn-add-venue-link">+</button>
                                    </div>
                                </div>
                            </div>
                            <div id="venue-preview-container" class="row g-2"></div>
                        </div>
                    </div>

                    {{-- GIẤY TỜ PHÁP LÝ (DOCUMENT) --}}
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-white py-3 border-bottom">
                            <h6 class="mb-0 fw-bold text-uppercase text-primary small"><i
                                    class="fas fa-file-invoice me-1"></i> Giấy tờ pháp lý</h6>
                        </div>
                        <div class="card-body">
                            <input type="file" id="doc_file_input" class="form-control form-control-sm mb-3"
                                accept="image/*" multiple>
                            <div id="doc-preview-container" class="row g-2"></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Hidden Inputs để submit --}}
            <div id="hidden-inputs">
                <input type="hidden" name="primary_image_index" id="primary_image_index">
                <input type="hidden" name="deleted_image_ids" id="deleted_image_ids">
                <input type="hidden" name="deleted_document_ids" id="deleted_document_ids">
            </div>

            <div class="text-end mt-4 pb-5">
                <button type="submit" class="btn btn-primary btn-lg px-5 shadow"><i class="fas fa-save me-2"></i> Lưu
                    thay đổi</button>
            </div>
        </form>
    </div>

    {{-- DỮ LIỆU JSON ĐỂ JS KHÔI PHỤC --}}
    <script id="venue-images-json" type="application/json">{!! json_encode($venue->images) !!}</script>

    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
        // --- 1. MAP & TIME ---
        flatpickr(".time-picker", {
            enableTime: true,
            noCalendar: true,
            dateFormat: "H:i",
            time_24hr: true
        });

        let lat = parseFloat($('#lat').val()) || 21.028511,
            lng = parseFloat($('#lng').val()) || 105.854444;
        var map = L.map('map').setView([lat, lng], 13);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
        var marker = L.marker([lat, lng], {
            draggable: true
        }).addTo(map);
        marker.on('dragend', function(e) {
            let c = e.target.getLatLng();
            $('#lat').val(c.lat.toFixed(6));
            $('#lng').val(c.lng.toFixed(6));
        });

        // --- 2. ADDRESS API ---
        const $province = $('#province_id'),
            $district = $('#district_id');

        function loadLoc(el, url, sel = null) {
            $.get(url, (data) => {
                let h = '<option value="">-- Chọn --</option>';
                data.forEach(d => h +=
                    `<option value="${d.code}" ${sel == d.code ? 'selected' : ''}>${d.name}</option>`);
                el.html(h).prop('disabled', false);
            });
        }
        loadLoc($province, '/api-proxy/provinces', $province.data('old'));
        if ($province.data('old')) loadLoc($district, '/api-proxy/districts/' + $province.data('old'), $district.data(
            'old'));

        $province.change(function() {
            if ($(this).val()) loadLoc($district, '/api-proxy/districts/' + $(this).val());
            else $district.prop('disabled', true).html('');
        });

        // --- 3. QUẢN LÝ ẢNH (LOGIC CHÍNH) ---
        $(document).ready(function() {
            const allImgs = JSON.parse($('#venue-images-json').text() || '[]');

            // State quản lý riêng 2 nhóm
            let state = {
                venue: {
                    existing: allImgs.filter(i => i.type === 'venue').map(i => ({
                        ...i,
                        key: `existing_${i.id}`
                    })),
                    newFiles: [],
                    newLinks: [],
                    deleted: []
                },
                doc: {
                    existing: allImgs.filter(i => i.type === 'document').map(i => ({
                        ...i,
                        key: `doc_existing_${i.id}`
                    })),
                    newFiles: [],
                    deleted: []
                }
            };

            function render() {
                // RENDER VENUE
                const $vBox = $('#venue-preview-container');
                $vBox.empty();
                const vList = [...state.venue.existing.filter(i => !state.venue.deleted.includes(String(i.id))), ...
                    state.venue.newFiles, ...state.venue.newLinks
                ];

                let curP = $('#primary_image_index').val();
                if (!vList.some(i => i.key === curP)) {
                    let def = vList.find(i => i.is_primary == 1) || vList[0];
                    curP = def ? def.key : '';
                    $('#primary_image_index').val(curP);
                }

                vList.forEach(img => {
                    let src = img.file ? URL.createObjectURL(img.file) : (img.url.startsWith('http') ? img
                        .url : '/' + img.url);
                    $vBox.append(`
                        <div class="col-4"><div class="preview-box position-relative">
                            <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 p-0 px-1" onclick="removeImg('venue', '${img.key}')">&times;</button>
                            <img src="${src}">
                            <div class="position-absolute bottom-0 start-0 w-100 bg-white bg-opacity-75 text-center small">
                                <input type="radio" name="p_radio" ${img.key === curP ? 'checked' : ''} onchange="setPrimary('${img.key}')"> Chính
                            </div>
                        </div></div>`);
                });

                // RENDER DOC
                const $dBox = $('#doc-preview-container');
                $dBox.empty();
                const dList = [...state.doc.existing.filter(i => !state.doc.deleted.includes(String(i.id))), ...
                    state.doc.newFiles
                ];

                dList.forEach(img => {
                    let src = img.file ? URL.createObjectURL(img.file) : '/' + img.url;
                    $dBox.append(`
                        <div class="col-4"><div class="preview-box position-relative">
                            <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 p-0 px-1" onclick="removeImg('doc', '${img.key}')">&times;</button>
                            <img src="${src}">
                        </div></div>`);
                });

                $('#deleted_image_ids').val(state.venue.deleted.join(','));
                $('#deleted_document_ids').val(state.doc.deleted.join(','));
            }

            window.setPrimary = (k) => $('#primary_image_index').val(k);
            window.removeImg = (type, k) => {
                if (type === 'venue') {
                    if (k.startsWith('existing_')) state.venue.deleted.push(k.replace('existing_', ''));
                    else state.venue.newFiles = state.venue.newFiles.filter(i => i.key !== k);
                    state.venue.newLinks = state.venue.newLinks.filter(i => i.key !== k);
                } else {
                    if (k.startsWith('doc_existing_')) state.doc.deleted.push(k.replace('doc_existing_', ''));
                    else state.doc.newFiles = state.doc.newFiles.filter(i => i.key !== k);
                }
                render();
            };

            $('#venue_file_input').change(function() {
                Array.from(this.files).forEach(f => state.venue.newFiles.push({
                    key: 'nf_' + Math.random(),
                    file: f
                }));
                render();
                $(this).val('');
            });
            $('#btn-add-venue-link').click(function() {
                let u = $('#venue_link_input').val();
                if (u) state.venue.newLinks.push({
                    key: 'nl_' + Math.random(),
                    url: u
                });
                render();
                $('#venue_link_input').val('');
            });
            $('#doc_file_input').change(function() {
                Array.from(this.files).forEach(f => state.doc.newFiles.push({
                    key: 'nd_' + Math.random(),
                    file: f
                }));
                render();
                $(this).val('');
            });

            $('#venue-edit-form').submit(function(e) {
                const vDT = new DataTransfer();
                state.venue.newFiles.forEach(i => vDT.items.add(i.file));
                const dDT = new DataTransfer();
                state.doc.newFiles.forEach(i => dDT.items.add(i.file));

                const $h = $('#hidden-inputs');
                $h.find('.dynamic-input').remove();

                const vf = $('<input type="file" name="new_files[]" class="d-none dynamic-input" multiple>')
                    .prop('files', vDT.files);
                const df = $(
                    '<input type="file" name="new_document_files[]" class="d-none dynamic-input" multiple>'
                    ).prop('files', dDT.files);
                $h.append(vf).append(df);

                state.venue.newLinks.forEach(l => $h.append(
                    `<input type="hidden" name="image_links[]" value="${l.url}" class="dynamic-input">`
                    ));
            });

            render();
        });
    </script>
@endsection
