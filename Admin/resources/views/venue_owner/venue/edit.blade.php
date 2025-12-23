@extends('app')

@section('content')

    <div class="container-fluid py-4">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h4 mb-0">Chỉnh sửa thương hiệu sân</h1>
                <p class="text-muted mb-0">Cập nhật thông tin cho: <strong>{{ $venue->name }}</strong></p>
            </div>
            <div>
                <a href="{{ route('owner.venues.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Quay lại danh sách
                </a>
            </div>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong><i class="fas fa-exclamation-triangle"></i> Vui lòng kiểm tra lại dữ liệu!</strong>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <form action="{{ route('owner.venues.update', $venue) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="row">
                <div class="col-lg-8">
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-white border-0 py-3">
                            <h5 class="card-title mb-0 text-primary">Thông tin cơ bản</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="name" class="form-label fw-bold">Tên thương hiệu (sân) <span
                                        class="text-danger">*</span></label>
                                <input type="text" name="name"
                                    class="form-control @error('name') is-invalid @enderror" id="name"
                                    value="{{ old('name', $venue->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            @if (Auth::user()->role->name === 'admin')
                                <div class="mb-3">
                                    <label for="owner_id" class="form-label fw-bold">Chủ sở hữu <span
                                            class="text-danger">*</span></label>
                                    <select name="owner_id" id="owner_id"
                                        class="form-select @error('owner_id') is-invalid @enderror" required>
                                        @foreach ($owners as $owner)
                                            <option value="{{ $owner->id }}" @selected(old('owner_id', $venue->owner_id) == $owner->id)>
                                                {{ $owner->name }} ({{ $owner->email }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('owner_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            @else
                                <input type="hidden" name="owner_id" value="{{ Auth::id() }}">
                            @endif

                            <hr class="my-4">
                            <h6 class="fw-bold">Thông tin địa chỉ</h6>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="province_id" class="form-label fw-bold">Tỉnh/Thành <span
                                            class="text-danger">*</span></label>
                                    <select name="province_id" id="province_id"
                                        class="form-select @error('province_id') is-invalid @enderror"
                                        data-old="{{ old('province_id', $venue->province_id) }}" required>
                                        <option value="">-- Đang tải... --</option>
                                    </select>
                                    @error('province_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="district_id" class="form-label fw-bold">Quận/Huyện <span
                                            class="text-danger">*</span></label>
                                    <select name="district_id" id="district_id"
                                        class="form-select @error('district_id') is-invalid @enderror"
                                        data-old="{{ old('district_id', $venue->district_id) }}" required disabled>
                                        <option value="">-- Chọn Tỉnh/Thành trước --</option>
                                    </select>
                                    @error('district_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="address_detail" class="form-label fw-bold">Địa chỉ chi tiết <span
                                        class="text-danger">*</span></label>
                                <input type="text" name="address_detail"
                                    class="form-control @error('address_detail') is-invalid @enderror" id="address_detail"
                                    required value="{{ old('address_detail', $venue->address_detail) }}"
                                    placeholder="Số nhà, tên đường, phường/xã...">
                                @error('address_detail')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <hr class="my-4">
                            <h6 class="fw-bold">Giờ hoạt động</h6>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="start_time" class="form-label fw-bold">Giờ mở cửa <span
                                            class="text-danger">*</span></label>
                                    <input type="time" name="start_time" id="start_time"
                                        class="form-control @error('start_time') is-invalid @enderror"
                                        value="{{ old('start_time', $venue->start_time ? \Carbon\Carbon::parse($venue->start_time)->format('H:i') : '06:00') }}"
                                        required>
                                    @error('start_time')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="end_time" class="form-label fw-bold">Giờ đóng cửa <span
                                            class="text-danger">*</span></label>
                                    <input type="time" name="end_time" id="end_time"
                                        class="form-control @error('end_time') is-invalid @enderror"
                                        value="{{ old('end_time', $venue->end_time ? \Carbon\Carbon::parse($venue->end_time)->format('H:i') : '22:00') }}"
                                        required>
                                    @error('end_time')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-white border-0 py-3">
                            <h5 class="card-title mb-0 text-primary">Thông tin bổ sung</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="phone" class="form-label fw-bold">Số điện thoại</label>
                                <input type="tel" name="phone"
                                    class="form-control @error('phone') is-invalid @enderror" id="phone"
                                    value="{{ old('phone', $venue->phone) }}" placeholder="09xxxxxxxx">
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold d-block">Loại hình sân <span
                                        class="text-danger">*</span></label>

                                <div class="py-2 px-3 border rounded bg-light @error('venue_types') border-danger @enderror"
                                    style="max-height: 200px; overflow-y: auto;">

                                    @foreach ($venue_types as $type)
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="venue_types[]"
                                                value="{{ $type->id }}" id="type_{{ $type->id }}"
                                                @checked(in_array($type->id, old('venue_types', $venue->venueTypes->pluck('id')->toArray())))>
                                            <label class="form-check-label" for="type_{{ $type->id }}">
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

                            <div class="mb-3">
                                <label class="form-label fw-bold d-block">Trạng thái</label>
                                <p class="mb-0">
                                    @if ($venue->is_active)
                                        <span class="badge bg-success">Hoạt động</span>
                                    @else
                                        <span class="badge bg-secondary">Đã khóa</span>
                                    @endif
                                </p>
                                <div class="form-check form-switch mt-2">
                                    <input class="form-check-input" type="checkbox" id="is_active_input"
                                        name="is_active" value="1" @checked($venue->is_active)>
                                    <label class="form-check-label" for="is_active_input">Kích hoạt hiển thị</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-white border-0 py-3">
                            <h5 class="card-title mb-0 text-primary">Quản lý Hình ảnh</h5>
                        </div>
                        <div class="card-body">

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
                                <div class="tab-pane fade show active" id="file-tab-pane" role="tabpanel"
                                    aria-labelledby="file-tab" tabindex="0">
                                    <input type="file" name="new_files[]" id="new_files_input"
                                        class="form-control @error('new_files') is-invalid @enderror @error('new_files.*') is-invalid @enderror"
                                        accept="image/jpeg,image/png,image/jpg,image/gif,image/webp" multiple>
                                    <div class="form-text text-muted">Chọn file ảnh. (Tối đa 5 ảnh mới)</div>
                                </div>

                                <div class="tab-pane fade" id="link-tab-pane" role="tabpanel" aria-labelledby="link-tab"
                                    tabindex="0">
                                    <div id="image-links-container"></div>
                                    <button type="button" id="add-link-btn" class="btn btn-sm btn-outline-primary mt-2">
                                        <i class="fas fa-plus me-1"></i> Thêm link ảnh
                                    </button>
                                </div>
                            </div>

                            @php
                                $primaryImageId = $venue->images->firstWhere('is_primary', true)->id ?? null;
                            @endphp
                            <input type="hidden" name="primary_image_index" id="primary_image_index"
                                value="{{ old('primary_image_index', $primaryImageId) }}">
                            <input type="hidden" name="existing_images_to_delete" id="images-to-delete"
                                value="{{ old('existing_images_to_delete') }}">

                            <div id="new-links-hidden-inputs"></div>

                            @error('new_files')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            @error('new_files.*')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            @error('image_links')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            @error('image_links.*')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            @error('primary_image_index')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror

                            <hr class="my-3">

                            <h6 class="fw-bold mb-3">Xem trước và chọn ảnh chính</h6>
                            <div id="images-preview" class="row g-2 mb-4"></div>

                            <div id="existing-images-data" data-images='@json($venue->images)'></div>

                        </div>
                    </div>

                </div>
            </div>

            <div class="mt-4 text-end">
                <a href="{{ route('owner.venues.index') }}" class="btn btn-secondary btn-lg">
                    Hủy bỏ
                </a>
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="fas fa-save me-2"></i> Lưu thay đổi
                </button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            const apiHost = '/api-proxy/provinces';
            const $province = $('#province_id');
            const $district = $('#district_id');
            const oldProvinceCode = $province.data('old');
            const oldDistrictCode = $district.data('old');

            function renderOptions($select, data, placeholder, selectedValue = null) {
                let html = `<option value="">${placeholder}</option>`;
                data.forEach(item => {
                    const isSelected = selectedValue == item.code ? 'selected' : '';
                    html += `<option value="${item.code}" ${isSelected}>${item.name}</option>`;
                });
                $select.html(html);
                $select.prop('disabled', false);
            }

            $.ajax({
                url: apiHost,
                method: 'GET',
                success: function(data) {
                    renderOptions($province, data, '-- Chọn Tỉnh/Thành --', oldProvinceCode);
                    if (oldProvinceCode) {
                        loadDistricts(oldProvinceCode, oldDistrictCode);
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    $province.html('<option value="">Lỗi tải dữ liệu</option>');
                }
            });

            function loadDistricts(provinceCode, selectedCode = null) {
                $district.html('<option value="">-- Đang tải... --</option>').prop('disabled', true);
                const url = '/api-proxy/districts/' + provinceCode;
                $.ajax({
                    url: url,
                    method: 'GET',
                    success: function(data) {
                        if (data && data.length > 0) {
                            renderOptions($district, data, '-- Chọn Quận/Huyện --', selectedCode);
                        } else {
                            $district.html('<option value="">Không tìm thấy huyện</option>');
                        }
                    },
                    error: function() {
                        $district.html('<option value="">Lỗi tải dữ liệu</option>');
                    }
                });
            }

            $province.on('change', function() {
                const provinceCode = $(this).val();
                if (provinceCode) {
                    loadDistricts(provinceCode);
                } else {
                    $district.html('<option value="">-- Chọn Tỉnh/Thành trước --</option>').prop('disabled',
                        true);
                }
            });
        });

        document.addEventListener('DOMContentLoaded', function() {
            const newFilesInput = document.getElementById('new_files_input');
            const imagesPreviewContainer = document.getElementById('images-preview');
            const primaryImageIndexInput = document.getElementById('primary_image_index');
            const addLinkBtn = document.getElementById('add-link-btn');
            const imageLinksContainer = document.getElementById('image-links-container');
            const imagesToDeleteInput = document.getElementById('images-to-delete');
            const newLinksHiddenInputs = document.getElementById('new-links-hidden-inputs');
            const MAX_FILES = 5;

            let newFileItems = [];
            let newLinkItems = [];
            let existingItems = [];
            let imagesToDelete = [];

            function initializeImageData() {
                const rawExistingImages = JSON.parse(document.getElementById('existing-images-data').dataset
                .images);
                existingItems = rawExistingImages.map(img => ({
                    type: 'existing',
                    id: img.id,
                    url: img.url,
                }));

                const oldDeletedIds = imagesToDeleteInput.value || "{{ old('existing_images_to_delete', '') }}";
                if (oldDeletedIds) {
                    imagesToDelete = oldDeletedIds.split(',').map(Number).filter(id => id > 0);
                }

                const oldLinks = {!! json_encode(old('image_links', [])) !!};
                oldLinks.forEach(url => {
                    addImageLinkInput(url, false);
                });
                newLinkItems = oldLinks.filter(url => url && url.trim().length > 0).map(url => ({
                    type: 'link',
                    url: url.trim()
                }));
            }

            function getCombinedActiveItems() {
                const activeExisting = existingItems.filter(item => !imagesToDelete.includes(item.id));
                let combinedArray = [...activeExisting, ...newFileItems, ...newLinkItems];

                if (combinedArray.length > MAX_FILES) {
                    const numActiveExisting = activeExisting.length;
                    const newItemsToKeep = combinedArray.slice(numActiveExisting, MAX_FILES);
                    const oldFileCount = newFileItems.length;
                    newFileItems = newItemsToKeep.filter(item => item.type === 'file');
                    newLinkItems = newItemsToKeep.filter(item => item.type === 'link');
                    if (newFileItems.length < oldFileCount) {
                        newFileItems = [];
                        newFilesInput.value = null;
                        alert(`Vượt quá giới hạn ${MAX_FILES} ảnh. File đã được reset.`);
                    }
                    combinedArray = [...activeExisting, ...newFileItems, ...newLinkItems];
                }
                return combinedArray;
            }

            function updateCombinedPreview() {
                imagesPreviewContainer.innerHTML = '';
                const combinedArray = getCombinedActiveItems();
                const finalCount = combinedArray.length;

                let currentPrimaryValue = primaryImageIndexInput.value;
                let currentPrimaryItem = null;
                let foundPrimary = false;

                if (currentPrimaryValue) {
                    const primaryValue = String(currentPrimaryValue).trim();
                    const numericValue = parseInt(primaryValue);
                    if (!isNaN(numericValue) && numericValue > 0) {
                        currentPrimaryItem = combinedArray.find(item => item.type === 'existing' && item.id ==
                            numericValue);
                        if (currentPrimaryItem) foundPrimary = true;
                    }
                    if (!foundPrimary && !isNaN(numericValue) && numericValue >= 0) {
                        const activeExistingCount = existingItems.filter(item => !imagesToDelete.includes(item.id))
                            .length;
                        const indexInCombined = numericValue + activeExistingCount;
                        if (indexInCombined < finalCount) {
                            currentPrimaryItem = combinedArray[indexInCombined];
                            if (currentPrimaryItem && currentPrimaryItem.type !== 'existing') foundPrimary = true;
                            else currentPrimaryItem = null;
                        }
                    }
                }

                if (!foundPrimary && finalCount > 0) currentPrimaryItem = combinedArray[0];

                if (currentPrimaryItem) {
                    if (currentPrimaryItem.type === 'existing') {
                        primaryImageIndexInput.value = currentPrimaryItem.id;
                    } else {
                        const activeExistingCount = existingItems.filter(item => !imagesToDelete.includes(item.id))
                            .length;
                        const indexInCombined = combinedArray.indexOf(currentPrimaryItem);
                        primaryImageIndexInput.value = indexInCombined - activeExistingCount;
                    }
                } else {
                    primaryImageIndexInput.value = '';
                }

                combinedArray.forEach((item, index) => {
                    let src = '';
                    const isPrimary = (item === currentPrimaryItem);
                    let valueForRadio = '';
                    let deleteButton = '';

                    if (item.type === 'existing') {
                        src = item.url.replace('{{ url('/') }}', '{{ url('/storage') }}');

                        valueForRadio = item.id;
                        deleteButton = `
                            <button type="button"
                                class="btn btn-sm btn-danger btn-delete-image position-absolute top-0 start-100 translate-middle rounded-circle p-1"
                                style="width: 24px; height: 24px; line-height: 1;"
                                title="Xóa ảnh"
                                data-type="existing"
                                data-id="${item.id}">
                                <i class="fas fa-times fa-sm"></i>
                            </button>`;

                        appendPreviewElement(src, index, isPrimary, valueForRadio, deleteButton);
                    } else {
                        const activeExistingCount = existingItems.filter(item => !imagesToDelete.includes(
                            item.id)).length;
                        const indexForNew = index - activeExistingCount;
                        valueForRadio = indexForNew;

                        if (item.type === 'file') {
                            deleteButton =
                                `<button type="button" class="btn btn-sm btn-danger btn-delete-image position-absolute top-0 start-100 translate-middle rounded-circle p-1" style="width: 24px; height: 24px; line-height: 1;" title="Xóa file" data-type="file" data-index="${indexForNew}"><i class="fas fa-times fa-sm"></i></button>`;
                            const reader = new FileReader();
                            reader.onload = (function(idx, isPrim, valRadio, delBtn) {
                                return function(e) {
                                    appendPreviewElement(e.target.result, idx, isPrim, valRadio,
                                        delBtn);
                                };
                            })(index, isPrimary, valueForRadio, deleteButton);
                            reader.readAsDataURL(item.file);
                        } else if (item.type === 'link') {
                            src = item.url;
                            if (!src || !src.match(/^https?:\/\//i)) src =
                                'https://via.placeholder.com/150?text=Invalid+Link';
                            deleteButton =
                                `<button type="button" class="btn btn-sm btn-danger btn-delete-image position-absolute top-0 start-100 translate-middle rounded-circle p-1" style="width: 24px; height: 24px; line-height: 1;" title="Xóa link" data-type="link" data-index="${indexForNew}"><i class="fas fa-times fa-sm"></i></button>`;
                            appendPreviewElement(src, index, isPrimary, valueForRadio, deleteButton);
                        }
                    }
                });
                updateHiddenLinks();
                imagesToDeleteInput.value = imagesToDelete.join(',');
            }

            function appendPreviewElement(src, index, isPrimary, valueForRadio, deleteButton) {
                let existingCol = imagesPreviewContainer.querySelector(`[data-preview-index="${index}"]`);
                if (existingCol) {
                    const radio = existingCol.querySelector('.primary-image-radio');
                    if (radio) radio.checked = isPrimary;
                    return;
                }
                const col = document.createElement('div');
                col.className = 'col-6 col-md-4 col-lg-6 col-xl-4 image-preview-item';
                col.setAttribute('data-preview-index', index);
                col.innerHTML = `
                <div class="position-relative border rounded p-2 text-center">
                    <img src="${src}" alt="Preview ${index + 1}" class="img-fluid rounded" style="height: 80px; object-fit: cover; width: 100%;" onerror="this.onerror=null;this.src='https://via.placeholder.com/150?text=Error';">
                    ${deleteButton}
                    <div class="mt-2 form-check">
                        <input class="form-check-input primary-image-radio" type="radio" name="selected_primary_image" id="primary_${index}" value="${valueForRadio}" ${isPrimary ? 'checked' : ''}>
                        <label class="form-check-label small fw-bold" for="primary_${index}">${index + 1}. Ảnh chính</label>
                    </div>
                </div>`;
                imagesPreviewContainer.appendChild(col);
            }

            function addImageLinkInput(url = '', focus = true) {
                const currentCount = existingItems.filter(item => !imagesToDelete.includes(item.id)).length +
                    newFileItems.length + newLinkItems.length;
                if (currentCount >= MAX_FILES && !url) {
                    alert(`Chỉ được phép thêm tối đa ${MAX_FILES} ảnh.`);
                    return;
                }
                const linkDiv = document.createElement('div');
                linkDiv.className = 'input-group mb-2 image-link-item';
                linkDiv.innerHTML =
                    `<input type="url" class="form-control form-control-sm image-link-input" value="${url}" placeholder="https://..."><button class="btn btn-outline-danger btn-sm remove-link-btn" type="button"><i class="fas fa-trash"></i></button>`;
                imageLinksContainer.appendChild(linkDiv);
                if (focus) linkDiv.querySelector('input').focus();
            }

            function updateHiddenLinks() {
                newLinksHiddenInputs.innerHTML = '';
                imageLinksContainer.querySelectorAll('.image-link-input').forEach(input => {
                    const inputHidden = document.createElement('input');
                    inputHidden.type = 'hidden';
                    inputHidden.name = 'image_links[]';
                    inputHidden.value = input.value.trim();
                    newLinksHiddenInputs.appendChild(inputHidden);
                });
            }

            newFilesInput.addEventListener('change', function(event) {
                const files = Array.from(event.target.files);
                const activeExistingCount = existingItems.filter(item => !imagesToDelete.includes(item.id))
                    .length;
                const linkCount = newLinkItems.length;
                const availableSlots = MAX_FILES - activeExistingCount - linkCount;
                if (files.length > availableSlots) {
                    alert(`Chỉ còn ${availableSlots} chỗ trống. Vui lòng chọn ít hơn.`);
                    event.target.value = null;
                    newFileItems = [];
                    updateCombinedPreview();
                    return;
                }
                newFileItems = files.map(file => ({
                    type: 'file',
                    file: file
                }));
                updateCombinedPreview();
            });

            addLinkBtn.addEventListener('click', () => {
                addImageLinkInput('', true);
            });

            document.addEventListener('click', function(e) {
                if (e.target.closest('.remove-link-btn')) {
                    e.target.closest('.image-link-item').remove();
                    newLinkItems = [];
                    imageLinksContainer.querySelectorAll('.image-link-input').forEach(input => {
                        const val = input.value.trim();
                        if (val.length > 0) newLinkItems.push({
                            type: 'link',
                            url: val
                        });
                    });
                    updateCombinedPreview();
                }
                if (e.target.closest('.btn-delete-image')) {
                    const button = e.target.closest('.btn-delete-image');
                    const type = button.dataset.type;
                    const currentPrimaryValue = String(primaryImageIndexInput.value).trim();
                    let primaryWasDeleted = false;

                    if (type === 'existing') {
                        const imageId = parseInt(button.dataset.id);
                        if (currentPrimaryValue == imageId) primaryWasDeleted = true;
                        if (!imagesToDelete.includes(imageId)) imagesToDelete.push(imageId);
                    } else if (type === 'file') {
                        const indexInNew = parseInt(button.dataset.index);
                        if (!isNaN(parseInt(currentPrimaryValue)) && parseInt(currentPrimaryValue) ===
                            indexInNew) primaryWasDeleted = true;
                        newFileItems = [];
                        newFilesInput.value = null;
                        alert('Đã xóa tất cả file mới. Vui lòng chọn lại nếu cần.');
                    } else if (type === 'link') {
                        const indexInNew = parseInt(button.dataset.index);
                        if (!isNaN(parseInt(currentPrimaryValue)) && parseInt(currentPrimaryValue) ===
                            indexInNew) primaryWasDeleted = true;
                        const combinedArray = getCombinedActiveItems();
                        const activeExistingCount = existingItems.filter(item => !imagesToDelete.includes(
                            item.id)).length;
                        const indexInCombined = activeExistingCount + indexInNew;
                        if (indexInCombined < combinedArray.length) {
                            const itemToDelete = combinedArray[indexInCombined];
                            if (itemToDelete && itemToDelete.type === 'link') {
                                const linkIndex = newLinkItems.findIndex(item => item.url === itemToDelete
                                    .url);
                                if (linkIndex !== -1) newLinkItems.splice(linkIndex, 1);
                                const linkInputs = Array.from(imageLinksContainer.querySelectorAll(
                                    '.image-link-item'));
                                for (let i = 0; i < linkInputs.length; i++) {
                                    const inputVal = linkInputs[i].querySelector('input').value.trim();
                                    if (inputVal === itemToDelete.url) {
                                        linkInputs[i].remove();
                                        break;
                                    }
                                }
                            }
                        }
                    }
                    if (primaryWasDeleted) primaryImageIndexInput.value = '';
                    updateCombinedPreview();
                }
            });

            document.addEventListener('input', function(e) {
                if (e.target.classList.contains('image-link-input')) {
                    newLinkItems = [];
                    imageLinksContainer.querySelectorAll('.image-link-input').forEach(input => {
                        const val = input.value.trim();
                        if (val.length > 0) newLinkItems.push({
                            type: 'link',
                            url: val
                        });
                    });
                    updateCombinedPreview();
                }
            });

            document.addEventListener('change', function(e) {
                if (e.target && e.target.classList.contains('primary-image-radio')) {
                    primaryImageIndexInput.value = e.target.value;
                }
            });

            initializeImageData();
            updateCombinedPreview();
        });
    </script>
@endpush
