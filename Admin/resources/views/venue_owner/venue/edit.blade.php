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
            {{-- CỘT CHÍNH (BÊN TRÁI) --}}
            <div class="col-lg-8">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="card-title mb-0 text-primary">Thông tin cơ bản</h5>
                    </div>
                    <div class="card-body">
                        {{-- Tên sân --}}
                        <div class="mb-3">
                            <label for="name" class="form-label fw-bold">Tên thương hiệu (sân) <span class="text-danger">*</span></label>
                            <input type="text" name="name"
                                class="form-control @error('name') is-invalid @enderror" id="name"
                                value="{{ old('name', $venue->name) }}" required>
                            @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Chủ sở hữu (Chỉ Admin mới thấy) --}}
                        @if (Auth::user()->role->name === 'admin')
                        <div class="mb-3">
                            <label for="owner_id" class="form-label fw-bold">Chủ sở hữu <span class="text-danger">*</span></label>
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
                                <label for="province_id" class="form-label fw-bold">Tỉnh/Thành <span class="text-danger">*</span></label>
                                <select name="province_id" id="province_id"
                                    class="form-select @error('province_id') is-invalid @enderror" required>
                                    <option value="">-- Chọn Tỉnh/Thành --</option>
                                    @foreach ($provinces as $province)
                                    <option value="{{ $province->id }}" @selected(old('province_id', $venue->province_id) == $province->id)>
                                        {{ $province->name }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('province_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="district_id" class="form-label fw-bold">Quận/Huyện <span class="text-danger">*</span></label>
                                <select name="district_id" id="district_id"
                                    class="form-select @error('district_id') is-invalid @enderror" required>
                                    <option value="">-- Chọn Quận/Huyện --</option>
                                    {{-- JS sẽ điền data vào đây --}}
                                </select>
                                @error('district_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="address_detail" class="form-label fw-bold">Địa chỉ chi tiết <span class="text-danger">*</span></label>
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
                                <label for="start_time" class="form-label fw-bold">Giờ mở cửa <span class="text-danger">*</span></label>
                                <input type="time" name="start_time" id="start_time"
                                    class="form-control @error('start_time') is-invalid @enderror"
                                    value="{{ old('start_time', $venue->start_time ? \Carbon\Carbon::parse($venue->start_time)->format('H:i') : '06:00') }}"
                                    required>
                                @error('start_time')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="end_time" class="form-label fw-bold">Giờ đóng cửa <span class="text-danger">*</span></label>
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

            {{-- CỘT PHỤ (BÊN PHẢI) --}}
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
                            <label class="form-label fw-bold d-block">Loại hình sân <span class="text-danger">*</span></label>

                            <div class="py-2 px-3 border rounded bg-light @error('venue_types') border-danger @enderror"
                                style="max-height: 200px; overflow-y: auto;">

                                @foreach ($venue_types as $type)
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox"
                                        name="venue_types[]"
                                        value="{{ $type->id }}"
                                        id="type_{{ $type->id }}"
                                        @checked( in_array($type->id, old('venue_types', $venue->venueTypes->pluck('id')->toArray())) )
                                    >
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
                                <button class="nav-link active" id="file-tab" data-bs-toggle="tab" data-bs-target="#file-tab-pane" type="button" role="tab" aria-controls="file-tab-pane" aria-selected="true">Tải file</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="link-tab" data-bs-toggle="tab" data-bs-target="#link-tab-pane" type="button" role="tab" aria-controls="link-tab-pane" aria-selected="false">Chèn link</button>
                            </li>
                        </ul>

                        <div class="tab-content border border-top-0 p-3 rounded-bottom" id="imageTabContent">
                            {{-- Tab 1: Tải file --}}
                            <div class="tab-pane fade show active" id="file-tab-pane" role="tabpanel" aria-labelledby="file-tab" tabindex="0">
                                <input type="file" name="new_files[]" id="new_files_input"
                                    class="form-control @error('new_files') is-invalid @enderror @error('new_files.*') is-invalid @enderror"
                                    accept="image/jpeg,image/png,image/jpg,image/gif,image/webp" multiple>
                                <div class="form-text text-muted">Chọn file ảnh. (Tối đa 5 ảnh mới)</div>
                            </div>

                            {{-- Tab 2: Chèn link --}}
                            <div class="tab-pane fade" id="link-tab-pane" role="tabpanel" aria-labelledby="link-tab" tabindex="0">
                                <div id="image-links-container">
                                    {{-- JS sẽ điền các input URL vào đây --}}
                                </div>
                                <button type="button" id="add-link-btn" class="btn btn-sm btn-outline-primary mt-2">
                                    <i class="fas fa-plus me-1"></i> Thêm link ảnh
                                </button>
                            </div>
                        </div>

                        @php
                            $primaryImageId = $venue->images->firstWhere('is_primary', true)->id ?? null;
                        @endphp
                        <input type="hidden" name="primary_image_index" id="primary_image_index" value="{{ old('primary_image_index', $primaryImageId) }}">

                        {{-- Ảnh hiện có sẽ được giữ lại, các ID trong đây sẽ bị xóa --}}
                        <input type="hidden" name="existing_images_to_delete" id="images-to-delete" value="{{ old('existing_images_to_delete') }}">
                        
                        {{-- INPUT ẨN ĐỂ CHUYỂN CÁC LINK MỚI LÊN SERVER --}}
                        <div id="new-links-hidden-inputs">
                            {{-- JS sẽ thêm các input: <input type="hidden" name="image_links[]" value="URL"> vào đây --}}
                        </div>

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
                        <div id="images-preview" class="row g-2 mb-4">
                            {{-- Ảnh preview sẽ được thêm vào đây bằng JS --}}
                        </div>

                        {{-- Hiển thị danh sách ảnh hiện có (Để JS dễ thao tác) --}}
                        <div id="existing-images-data" data-images='@json($venue->images)'></div>

                    </div>
                </div>

            </div>
        </div>

        {{-- NÚT LƯU --}}
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
<script>
    document.addEventListener('DOMContentLoaded', function() {

    const allDistricts = @json($districts);
    const provinceSelect = document.getElementById('province_id');
    const districtSelect = document.getElementById('district_id');
    const oldDistrictId = "{{ old('district_id', $venue->district_id) }}";

    function updateDistricts(provinceId) {
        districtSelect.innerHTML = '<option value="">-- Chọn Quận/Huyện --</option>';
        if (provinceId) {
            const filteredDistricts = allDistricts.filter(d => d.province_id == provinceId);
            filteredDistricts.forEach(district => {
                const option = document.createElement('option');
                option.value = district.id;
                option.textContent = district.name;
                if (district.id == oldDistrictId) {
                    option.selected = true;
                }
                districtSelect.appendChild(option);
            });
        }
    }

    provinceSelect.addEventListener('change', function() {
        updateDistricts(this.value);
    });

    if (provinceSelect.value) {
        updateDistricts(provinceSelect.value);
    }
    
    // ===== PHẦN XỬ LÝ QUẢN LÝ ẢNH - ĐÃ SỬA LỖI =====
    const newFilesInput = document.getElementById('new_files_input');
    const imagesPreviewContainer = document.getElementById('images-preview');
    const primaryImageIndexInput = document.getElementById('primary_image_index');
    const addLinkBtn = document.getElementById('add-link-btn');
    const imageLinksContainer = document.getElementById('image-links-container');
    const imagesToDeleteInput = document.getElementById('images-to-delete');
    const newLinksHiddenInputs = document.getElementById('new-links-hidden-inputs');
    const MAX_FILES = 5;

    // Biến toàn cục
    let newFileItems = []; 
    let newLinkItems = []; 
    let existingItems = []; 
    let imagesToDelete = []; 

    // Khởi tạo dữ liệu ban đầu
    function initializeImageData() {
        // 1. Khởi tạo ảnh hiện có
        const rawExistingImages = JSON.parse(document.getElementById('existing-images-data').dataset.images);
        existingItems = rawExistingImages.map(img => ({
            type: 'existing',
            id: img.id,
            url: img.url,
        }));
        
        // 2. Khởi tạo imagesToDelete từ old() hoặc giá trị input hidden
        const oldDeletedIds = imagesToDeleteInput.value || "{{ old('existing_images_to_delete', '') }}";
        if(oldDeletedIds) {
            imagesToDelete = oldDeletedIds.split(',').map(Number).filter(id => id > 0);
        }
        
        // 3. Khởi tạo link từ old()
        const oldLinks = {!! json_encode(old('image_links', [])) !!}; 
        
        // Render tất cả input link (kể cả rỗng)
        oldLinks.forEach(url => {
            addImageLinkInput(url, false);
        });
        
        // Chỉ lưu link có giá trị vào newLinkItems
        newLinkItems = oldLinks.filter(url => url && url.trim().length > 0).map(url => ({
            type: 'link',
            url: url.trim()
        }));
    }
    
    // Lấy tất cả ảnh đang hoạt động
    function getCombinedActiveItems() {
        const activeExisting = existingItems.filter(item => !imagesToDelete.includes(item.id));
        let combinedArray = [...activeExisting, ...newFileItems, ...newLinkItems];
        
        // Xử lý giới hạn 5 ảnh
        if (combinedArray.length > MAX_FILES) {
            const numActiveExisting = activeExisting.length;
            const newItemsToKeep = combinedArray.slice(numActiveExisting, MAX_FILES);
            
            const oldFileCount = newFileItems.length;
            newFileItems = newItemsToKeep.filter(item => item.type === 'file');
            newLinkItems = newItemsToKeep.filter(item => item.type === 'link');
            
            // Reset input file nếu bị cắt
            if (newFileItems.length < oldFileCount) {
                newFileItems = [];
                newFilesInput.value = null;
                alert(`Vượt quá giới hạn ${MAX_FILES} ảnh. File đã được reset.`);
            }
            
            combinedArray = [...activeExisting, ...newFileItems, ...newLinkItems];
        }
        
        return combinedArray;
    }

    // Cập nhật preview và primary index
    function updateCombinedPreview() {
        imagesPreviewContainer.innerHTML = '';
        
        const combinedArray = getCombinedActiveItems();
        const finalCount = combinedArray.length;

        // 1. Xử lý ảnh chính
        let currentPrimaryValue = primaryImageIndexInput.value;
        let currentPrimaryItem = null; 
        let foundPrimary = false;

        if (currentPrimaryValue) {
            const primaryValue = String(currentPrimaryValue).trim();
            
            // Kiểm tra nếu là ID ảnh cũ (số dương)
            const numericValue = parseInt(primaryValue);
            if (!isNaN(numericValue) && numericValue > 0) {
                currentPrimaryItem = combinedArray.find(item => 
                    item.type === 'existing' && item.id == numericValue
                );
                if (currentPrimaryItem) foundPrimary = true;
            } 
            
            // Kiểm tra nếu là index ảnh mới (0, 1, 2...)
            if (!foundPrimary && !isNaN(numericValue) && numericValue >= 0) {
                const activeExistingCount = existingItems.filter(item => !imagesToDelete.includes(item.id)).length;
                const indexInCombined = numericValue + activeExistingCount;
                
                if (indexInCombined < finalCount) {
                    currentPrimaryItem = combinedArray[indexInCombined];
                    if (currentPrimaryItem && currentPrimaryItem.type !== 'existing') {
                        foundPrimary = true;
                    } else {
                        currentPrimaryItem = null;
                    }
                }
            }
        }
        
        // Mặc định chọn ảnh đầu tiên nếu không tìm thấy
        if (!foundPrimary && finalCount > 0) {
            currentPrimaryItem = combinedArray[0];
        }

        // 2. Cập nhật input hidden primary_image_index
        if (currentPrimaryItem) {
            if (currentPrimaryItem.type === 'existing') {
                primaryImageIndexInput.value = currentPrimaryItem.id; 
            } else {
                const activeExistingCount = existingItems.filter(item => !imagesToDelete.includes(item.id)).length;
                const indexInCombined = combinedArray.indexOf(currentPrimaryItem);
                primaryImageIndexInput.value = indexInCombined - activeExistingCount; 
            }
        } else {
            primaryImageIndexInput.value = ''; 
        }
        
        // 3. Render Preview
        combinedArray.forEach((item, index) => {
            let src = '';
            const isPrimary = (item === currentPrimaryItem); 
            let valueForRadio = '';
            let deleteButton = '';
            
            if (item.type === 'existing') {
                // Xử lý URL ảnh cũ: Kiểm tra xem có phải link ngoài không
                if (item.url.startsWith('http://') || item.url.startsWith('https://')) {
                    src = item.url; // Link ngoài, dùng trực tiếp
                } else {
                    src = `{{ asset('storage') }}/${item.url}`; // File local
                }
                
                valueForRadio = item.id;
                deleteButton = `
                    <button type="button" class="btn btn-sm btn-danger btn-delete-image position-absolute top-0 start-100 translate-middle rounded-circle p-1" style="width: 24px; height: 24px; line-height: 1;" title="Xóa ảnh" data-type="existing" data-id="${item.id}">
                        <i class="fas fa-times fa-sm"></i>
                    </button>
                `;
                appendPreviewElement(src, index, isPrimary, valueForRadio, deleteButton);
                
            } else {
                const activeExistingCount = existingItems.filter(item => !imagesToDelete.includes(item.id)).length;
                const indexForNew = index - activeExistingCount;
                valueForRadio = indexForNew;

                if (item.type === 'file') {
                    deleteButton = `
                        <button type="button" class="btn btn-sm btn-danger btn-delete-image position-absolute top-0 start-100 translate-middle rounded-circle p-1" style="width: 24px; height: 24px; line-height: 1;" title="Xóa file" data-type="file" data-index="${indexForNew}">
                            <i class="fas fa-times fa-sm"></i>
                        </button>
                    `;

                    const reader = new FileReader();
                    reader.onload = (function(idx, isPrim, valRadio, delBtn) {
                        return function(e) {
                            appendPreviewElement(e.target.result, idx, isPrim, valRadio, delBtn);
                        };
                    })(index, isPrimary, valueForRadio, deleteButton);
                    
                    reader.readAsDataURL(item.file);
                    
                } else if (item.type === 'link') {
                    src = item.url;
                    if (!src || !src.match(/^https?:\/\//i)) {
                        src = 'https://via.placeholder.com/150?text=Invalid+Link'; 
                    }
                    
                    deleteButton = `
                        <button type="button" class="btn btn-sm btn-danger btn-delete-image position-absolute top-0 start-100 translate-middle rounded-circle p-1" style="width: 24px; height: 24px; line-height: 1;" title="Xóa link" data-type="link" data-index="${indexForNew}">
                            <i class="fas fa-times fa-sm"></i>
                        </button>
                    `;
                    appendPreviewElement(src, index, isPrimary, valueForRadio, deleteButton);
                }
            }
        });
        
        // Cập nhật input hidden cho links
        updateHiddenLinks();
        
        // Cập nhật input hidden cho imagesToDelete
        imagesToDeleteInput.value = imagesToDelete.join(',');
    }
    
    // Tạo element preview
    function appendPreviewElement(src, index, isPrimary, valueForRadio, deleteButton) {
        let existingCol = imagesPreviewContainer.querySelector(`[data-preview-index="${index}"]`);
        
        if (existingCol) {
            const radio = existingCol.querySelector('.primary-image-radio');
            if (radio) {
                radio.checked = isPrimary;
            }
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
                    <input class="form-check-input primary-image-radio" type="radio" 
                        name="selected_primary_image" id="primary_${index}" 
                        value="${valueForRadio}" ${isPrimary ? 'checked' : ''}>
                    <label class="form-check-label small fw-bold" for="primary_${index}">
                        ${index + 1}. Ảnh chính
                    </label>
                </div>
            </div>
        `;
        
        imagesPreviewContainer.appendChild(col);
    }
    
    // Thêm input link
    function addImageLinkInput(url = '', focus = true) {
        const currentCount = existingItems.filter(item => !imagesToDelete.includes(item.id)).length + 
                            newFileItems.length + newLinkItems.length;
        
        if (currentCount >= MAX_FILES && !url) { 
            alert(`Chỉ được phép thêm tối đa ${MAX_FILES} ảnh.`);
            return;
        }
        
        const linkDiv = document.createElement('div');
        linkDiv.className = 'input-group mb-2 image-link-item';
        linkDiv.innerHTML = `
            <input type="url" class="form-control form-control-sm image-link-input" value="${url}" placeholder="https://...">
            <button class="btn btn-outline-danger btn-sm remove-link-btn" type="button"><i class="fas fa-trash"></i></button>
        `;
        imageLinksContainer.appendChild(linkDiv);
        
        if(focus) {
            linkDiv.querySelector('input').focus();
        }
    }
    
    // Cập nhật input hidden cho links
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

    // Xử lý chọn file mới
    newFilesInput.addEventListener('change', function(event) {
        const files = Array.from(event.target.files);
        const activeExistingCount = existingItems.filter(item => !imagesToDelete.includes(item.id)).length;
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

    // Xử lý thêm link
    addLinkBtn.addEventListener('click', () => {
        addImageLinkInput('', true);
    });
    
    // Xử lý xóa
    document.addEventListener('click', function(e) {
        // Xóa link
        if (e.target.closest('.remove-link-btn')) {
            const itemDiv = e.target.closest('.image-link-item');
            itemDiv.remove();
            
            // Cập nhật lại newLinkItems
            newLinkItems = [];
            imageLinksContainer.querySelectorAll('.image-link-input').forEach(input => {
                const val = input.value.trim();
                if (val.length > 0) {
                    newLinkItems.push({ type: 'link', url: val });
                }
            });

            updateCombinedPreview();
        }
        
        // Xóa ảnh cũ/file/link từ preview
        if (e.target.closest('.btn-delete-image')) {
            const button = e.target.closest('.btn-delete-image');
            const type = button.dataset.type;
            
            const currentPrimaryValue = String(primaryImageIndexInput.value).trim();
            let primaryWasDeleted = false;
            
            if (type === 'existing') {
                const imageId = parseInt(button.dataset.id);
                
                if (currentPrimaryValue == imageId) {
                    primaryWasDeleted = true;
                }

                if (!imagesToDelete.includes(imageId)) {
                    imagesToDelete.push(imageId);
                }

            } else if (type === 'file') {
                const indexInNew = parseInt(button.dataset.index);
                
                if (!isNaN(parseInt(currentPrimaryValue)) && parseInt(currentPrimaryValue) === indexInNew) {
                    primaryWasDeleted = true;
                }
                
                newFileItems = []; 
                newFilesInput.value = null; 
                alert('Đã xóa tất cả file mới. Vui lòng chọn lại nếu cần.');

            } else if (type === 'link') {
                const indexInNew = parseInt(button.dataset.index);
                
                if (!isNaN(parseInt(currentPrimaryValue)) && parseInt(currentPrimaryValue) === indexInNew) {
                    primaryWasDeleted = true;
                }

                // Tìm link cần xóa trong combinedArray
                const combinedArray = getCombinedActiveItems();
                const activeExistingCount = existingItems.filter(item => !imagesToDelete.includes(item.id)).length;
                const indexInCombined = activeExistingCount + indexInNew;
                
                if (indexInCombined < combinedArray.length) {
                    const itemToDelete = combinedArray[indexInCombined];
                    
                    if (itemToDelete && itemToDelete.type === 'link') {
                        // Xóa khỏi mảng newLinkItems
                        const linkIndex = newLinkItems.findIndex(item => item.url === itemToDelete.url);
                        if (linkIndex !== -1) {
                            newLinkItems.splice(linkIndex, 1);
                        }
                        
                        // Xóa input HTML tương ứng
                        const linkInputs = Array.from(imageLinksContainer.querySelectorAll('.image-link-item'));
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
            
            if (primaryWasDeleted) {
                primaryImageIndexInput.value = '';
            }
            
            updateCombinedPreview();
        }
    });
    
    // Xử lý thay đổi link
    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('image-link-input')) {
            newLinkItems = [];
            imageLinksContainer.querySelectorAll('.image-link-input').forEach(input => {
                const val = input.value.trim();
                if (val.length > 0) { 
                    newLinkItems.push({ type: 'link', url: val });
                }
            });
            updateCombinedPreview();
        }
    });

    // Xử lý chọn ảnh chính
    document.addEventListener('change', function(e) {
        if (e.target && e.target.classList.contains('primary-image-radio')) {
            primaryImageIndexInput.value = e.target.value;
        }
    });
    
    // Khởi tạo
    initializeImageData();
    updateCombinedPreview();
    
    // DEBUG: Log trước khi submit
    document.querySelector('form').addEventListener('submit', function(e) {
        console.log('=== FORM SUBMIT DEBUG ===');
        console.log('Primary Image Index:', primaryImageIndexInput.value);
        console.log('Images To Delete:', imagesToDeleteInput.value);
        console.log('imagesToDelete Array:', imagesToDelete);
        console.log('existingItems:', existingItems);
        console.log('newFileItems:', newFileItems);
        console.log('newLinkItems:', newLinkItems);
        
        // Kiểm tra hidden inputs
        const hiddenLinks = [];
        newLinksHiddenInputs.querySelectorAll('input[name="image_links[]"]').forEach(input => {
            hiddenLinks.push(input.value);
        });
        console.log('Hidden Links:', hiddenLinks);
        
        // Kiểm tra file input
        console.log('Files selected:', newFilesInput.files.length);
        
        // Tính toán số ảnh cuối cùng
        const activeExisting = existingItems.filter(item => !imagesToDelete.includes(item.id));
        const totalImages = activeExisting.length + newFileItems.length + newLinkItems.length;
        console.log('Total images after submit:', totalImages);
        console.log('Active existing:', activeExisting.length);
        
        // KHÔNG CHẶN SUBMIT, chỉ log để debug
    });
    
});
</script>
@endpush