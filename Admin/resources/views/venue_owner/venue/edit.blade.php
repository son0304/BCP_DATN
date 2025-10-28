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
                <a href="{{ route('venue.index') }}" class="btn btn-outline-secondary">
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

        <form action="{{ route('venue.update', $venue) }}" method="POST">
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
                <a href="{{ route('venue.index') }}" class="btn btn-secondary">Hủy bỏ</a>
                <button type="submit" class="btn btn-primary btn-lg">Lưu thay đổi</button>
            </div>
        </form>
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

            // Cập nhật khi thay đổi tỉnh
            provinceSelect.addEventListener('change', function() {
                updateDistricts(this.value);
            });

            // Tải danh sách quận/huyện lần đầu khi trang load
            if (provinceSelect.value) {
                updateDistricts(provinceSelect.value);
            }
        });
    </script>
@endpush
