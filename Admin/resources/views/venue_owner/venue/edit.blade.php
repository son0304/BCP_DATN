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

    <form action="{{ route('owner.venues.update', $venue) }}" method="POST">
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
        // Dữ liệu được lấy từ Controller (Đã lấy ALL districts)
        const allDistricts = @json($districts);

        const provinceSelect = document.getElementById('province_id');
        const districtSelect = document.getElementById('district_id');

        // Lấy giá trị cũ (khi validate fail) hoặc giá trị hiện tại của Venue
        const oldDistrictId = "{{ old('district_id', $venue->district_id) }}";

        function updateDistricts(provinceId) {
            // Reset dropdown
            districtSelect.innerHTML = '<option value="">-- Chọn Quận/Huyện --</option>';

            if (provinceId) {
                // Lọc quận/huyện theo province_id
                const filteredDistricts = allDistricts.filter(d => d.province_id == provinceId);

                filteredDistricts.forEach(district => {
                    const option = document.createElement('option');
                    option.value = district.id;
                    option.textContent = district.name;

                    // Nếu district này trùng với giá trị cũ -> selected
                    if (district.id == oldDistrictId) {
                        option.selected = true;
                    }
                    districtSelect.appendChild(option);
                });
            }
        }

        // Sự kiện khi đổi Tỉnh
        provinceSelect.addEventListener('change', function() {
            updateDistricts(this.value);
        });

        // Chạy lần đầu khi trang load để hiển thị đúng quận/huyện của venue hiện tại
        if (provinceSelect.value) {
            updateDistricts(provinceSelect.value);
        }
    });
</script>
@endpush