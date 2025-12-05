@extends('app')

@section('content')
    {{-- Thêm <style> để định nghĩa màu xanh lá chủ đạo --}}
    <style>
        :root {
            --bs-primary: #348738;
            --bs-primary-rgb: 52, 135, 56;
        }

        .btn-primary {
            --bs-btn-hover-bg: #2d6a2d;
            --bs-btn-hover-border-color: #2d6a2d;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #84c887;
            box-shadow: 0 0 0 0.25rem rgba(52, 135, 56, 0.25);
        }

        .form-switch .form-check-input:checked {
            background-color: var(--bs-primary);
            border-color: var(--bs-primary);
        }
    </style>

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
            <div class="alert alert-danger">
                <strong>Có lỗi xảy ra!</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('owner.venues.update', $venue) }}" enctype="multipart/form-data" method="POST">
            @csrf
            @method('PUT')

            <div class="row">
                {{-- CỘT CHÍNH (BÊN TRÁI) --}}
                <div class="col-lg-8">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-white border-0 py-3">
                            <h5 class="card-title mb-0 text-primary">Thông tin cơ bản</h5>
                        </div>
                        <div class="card-body">
                            {{-- Tên sân --}}
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

                            {{-- Chủ sở hữu (Chỉ Admin mới thấy) --}}
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
                                @else
                                    {{-- Nếu là Venue Owner, gửi ID của họ một cách ngầm định --}}
                                    <input type="hidden" name="owner_id" value="{{ Auth::id() }}">
                            @endif

                            <hr class="my-4">
                            <h6 class="fw-bold">Thông tin địa chỉ</h6>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="province_id" class="form-label fw-bold">Tỉnh/Thành <span
                                            class="text-danger">*</span></label>
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
                                    <label for="district_id" class="form-label fw-bold">Quận/Huyện <span
                                            class="text-danger">*</span></label>
                                    <select name="district_id" id="district_id"
                                        class="form-select @error('district_id') is-invalid @enderror" required>
                                        {{-- Districts sẽ được load bằng JS --}}
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
                                    <input type="time" name="start_time" id="start_time" class="form-control"
                                        value="{{ old('start_time', $venue->start_time ? \Carbon\Carbon::parse($venue->start_time)->format('H:i') : '06:00') }}"
                                        required>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="end_time" class="form-label fw-bold">Giờ đóng cửa <span
                                            class="text-danger">*</span></label>
                                    <input type="time" name="end_time" id="end_time" class="form-control"
                                        value="{{ old('end_time', $venue->end_time ? \Carbon\Carbon::parse($venue->end_time)->format('H:i') : '22:00') }}"
                                        required>
                                </div>
                            </div>
                            <div class="card shadow-sm border-0 mb-4">
                                <div class="card-header bg-white border-0 py-3">
                                    <h5 class="card-title mb-0 text-primary">Quản lý Hình ảnh</h5>
                                    <p class="text-muted small mb-0">Quản lý các hình ảnh hiện có và tải lên hình ảnh mới
                                        cho Venue.</p>
                                </div>
                                <div class="card-body">

                                    <h6 class="fw-bold mb-3">Hình ảnh hiện tại ({{ $venue->images->count() }})</h6>
                                    <div class="row g-3 mb-4" id="current-images-container">
                                        @forelse ($venue->images as $image)
                                            @php
                                                $imageUrl = asset('storage/' . $image->url);
                                            @endphp
                                            <div
                                                class="col-md-4 col-6 position-relative border rounded p-2 image-item-{{ $image->id }}">

                                                <img src="{{ $imageUrl }}" alt="Venue Image"
                                                    class="img-fluid rounded shadow-sm"
                                                    style="height: 150px; width: 100%; object-fit: cover; cursor: pointer;"
                                                    data-bs-toggle="modal" data-bs-target="#imageModal"
                                                    data-image-url="{{ $imageUrl }}">
                                                <button type="button"
                                                    class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1 delete-image-btn"
                                                    title="Xóa ảnh này" data-image-id="{{ $image->id }}">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                                <input type="hidden" name="images_to_delete[{{ $image->id }}]"
                                                    id="delete_image_input_{{ $image->id }}" value="0">
                                                <div class="form-check mt-2">
                                                    <input class="form-check-input set-primary-image" type="radio"
                                                        name="primary_image_id" value="{{ $image->id }}"
                                                        id="primary_img_{{ $image->id }}" @checked(old('primary_image_id', $image->is_primary) == 1)>
                                                    <label class="form-check-label small"
                                                        for="primary_img_{{ $image->id }}">
                                                        Ảnh đại diện (Primary)
                                                    </label>
                                                </div>
                                            </div>
                                        @empty
                                            <div class="col-12 text-muted" id="no-images-message">Chưa có hình ảnh nào
                                                được tải lên.</div>
                                        @endforelse
                                    </div>

                                    <hr>
                                    <h6 class="fw-bold mb-3">Tải lên hình ảnh mới</h6>
                                    <div class="mb-3">
                                        <label for="new_images" class="form-label">Chọn tệp hình ảnh (Tối đa 5 ảnh, định
                                            dạng JPG, PNG)</label>
                                        <input class="form-control @error('new_images.*') is-invalid @enderror"
                                            type="file" id="new_images" name="new_images[]" multiple
                                            accept="image/*">
                                        @error('new_images.*')
                                            <div class="invalid-feedback">Đã xảy ra lỗi với một hoặc nhiều tệp tải lên. Vui
                                                lòng kiểm tra kích thước và định dạng tệp.</div>
                                        @enderror
                                        @error('new_images')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div id="image-preview-container" class="row g-3 mt-2">
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- CỘT PHỤ (BÊN PHẢI) --}}
                <div class="col-lg-4">
                    <div class="card shadow-sm border-0">
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
                                <label class="form-label fw-bold d-block">Loại hình sân</label>
                                <div class="py-2 px-3 border rounded" style="max-height: 200px; overflow-y: auto;">
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
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold d-block">Trạng thái</label>
                                <p class="mb-0">
                                    @if ($venue->is_active)
                                        <span class="badge bg-success">Hoạt động</span>
                                    @else
                                        <span class="badge bg-secondary">Không hoạt động</span>
                                    @endif
                                </p>
                            </div>


                        </div>
                    </div>
                </div>
            </div>

            {{-- NÚT LƯU --}}
            <div class="mt-4 text-end">
                <a href="{{ route('owner.venues.index') }}" class="btn btn-secondary">Hủy bỏ</a>
                <button type="submit" class="btn btn-primary btn-lg">Lưu thay đổi</button>
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
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const provinces = @json($provinces);
            const allDistricts = @json($districts);

            const provinceSelect = document.getElementById('province_id');
            const districtSelect = document.getElementById('district_id');

            const oldDistrictId = "{{ old('district_id', $venue->district_id) }}";

            function updateDistricts(provinceId) {
                // Xóa các lựa chọn cũ
                districtSelect.innerHTML = '<option value="">-- Chọn Quận/Huyện --</option>';

                if (provinceId) {
                    // Lọc các quận huyện dựa trên Tỉnh/TP đã chọn
                    const filteredDistricts = allDistricts.filter(d => d.province_id == provinceId);

                    // Thêm các quận/huyện mới vào
                    filteredDistricts.forEach(district => {
                        const option = document.createElement('option');
                        option.value = district.id;
                        option.textContent = district.name;
                        // Chọn lại quận/huyện cũ nếu có
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
            document.querySelectorAll('.delete-image-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const imageId = this.dataset.imageId;
                    const container = this.closest('.col-md-4');
                    const deleteInput = document.getElementById(`delete_image_input_${imageId}`);
                    const primaryRadio = document.getElementById(`primary_img_${imageId}`);
                    deleteInput.value = 1;
                    container.style.opacity = '0.3';
                    container.classList.add('border-danger');
                    this.disabled = true;
                    if (primaryRadio) {
                        primaryRadio.disabled = true;
                        if (primaryRadio.checked) {
                            primaryRadio.checked = false;
                        }
                    }
                });
            });
            const newImagesInput = document.getElementById('new_images');
            const previewContainer = document.getElementById('image-preview-container');

            newImagesInput.addEventListener('change', function() {
                previewContainer.innerHTML = '';
                if (this.files) {
                    Array.from(this.files).forEach(file => {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            const imageUrl = e.target.result;
                            const html = `
                                <div class="col-md-4 col-6">
                                    <div class="position-relative border rounded p-2">
                                        <img src="${imageUrl}" 
                                            class="img-fluid rounded shadow-sm" 
                                            style="height: 150px; width: 100%; object-fit: cover; cursor: pointer;" 
                                            alt="Preview"
                                            data-bs-toggle="modal" 
                                            data-bs-target="#imageModal"
                                            data-image-url="${imageUrl}">
                                        <span class="badge bg-info position-absolute top-0 start-0 m-1">Mới</span>
                                    </div>
                                </div>
                            `;
                            previewContainer.insertAdjacentHTML('beforeend', html);
                        }
                    });
                }
            });
        });
        //phong to anh 
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
@endpush
