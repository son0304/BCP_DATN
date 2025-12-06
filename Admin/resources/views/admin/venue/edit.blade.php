@extends('app')

@section('content')
    <div class="container-fluid py-4 ">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="h4 mb-0">Chỉnh sửa thương hiệu sân</h2>
                <p class="text-muted mb-0">Cập nhật thông tin cho: <strong>{{ $venue->name }}</strong></p>
            </div>
            <div>
                <a href="{{ route('brand.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Quay lại danh sách
                </a>
            </div>
        </div>

        <form action="{{ route('brand.update', $venue) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row">
                <div class="col-lg-8">
                    <div class="card shadow-sm">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Thông tin cơ bản</h5>
                        </div>
                        <div class="card-body">

                            {{-- Tên sân --}}
                            <div class="mb-3">
                                <label for="name" class="form-label fw-bold">Tên thương hiệu (sân)</label>
                                <input type="text" name="name"
                                    class="form-control @error('name') is-invalid @enderror" id="name"
                                    value="{{ old('name', $venue->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Chủ sở hữu --}}
                            <div class="mb-3">
                                <label for="owner_id" class="form-label fw-bold">Chủ sở hữu</label>
                                <select name="owner_id" id="owner_id"
                                    class="form-select @error('owner_id') is-invalid @enderror" required>
                                    @foreach ($owners as $owner)
                                        <option value="{{ $owner->id }}" @selected(old('owner_id', $venue->owner_id) == $owner->id)>
                                            {{ $owner->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('owner_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <hr class="my-4">
                            <h6 class="fw-bold">Thông tin địa chỉ</h6>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="province_id" class="form-label">Tỉnh/Thành</label>
                                    <select name="province_id" id="province_id"
                                        class="form-select @error('province_id') is-invalid @enderror" required>
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
                                    <label for="district_id" class="form-label">Quận/Huyện</label>
                                    <select name="district_id" id="district_id"
                                        class="form-select @error('district_id') is-invalid @enderror" required>
                                        @foreach ($districts as $district)
                                            <option value="{{ $district->id }}" @selected(old('district_id', $venue->district_id) == $district->id)>
                                                {{ $district->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('district_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="address_detail" class="form-label">Địa chỉ chi tiết</label>
                                <input type="text" name="address_detail" class="form-control" id="address_detail"
                                    required value="{{ old('address_detail', $venue->address_detail) }}"
                                    placeholder="Số nhà, tên đường, phường/xã...">
                            </div>

                            <hr class="my-4">
                            <h6 class="fw-bold">Giờ hoạt động</h6>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="start_time" class="form-label fw-bold">Giờ mở cửa</label>
                                    <input type="time" name="start_time" id="start_time" class="form-control w-50"
                                        value="{{ old('start_time', isset($venue->start_time) ? \Carbon\Carbon::parse($venue->start_time)->format('H:i') : '06:00') }}">
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="end_time" class="form-label fw-bold">Giờ đóng cửa</label>
                                    <input type="time" name="end_time" id="end_time" class="form-control w-50"
                                        value="{{ old('end_time', isset($venue->end_time) ? \Carbon\Carbon::parse($venue->end_time)->format('H:i') : '22:00') }}">
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                {{-- Cột phụ bên phải --}}
                <div class="col-lg-4">

                    <div class="card shadow-sm">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Thông tin bổ sung</h5>
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


                            <div>
                                <label class="form-label fw-bold d-block">Loại hình sân</label>
                                @foreach ($venue_types as $type)
                                    <div class="form-check form-check-inline">
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
                    </div>
                </div>
            </div>
            <div class="form-check form-switch d-flex justify-content-center align-items-center gap-3 mb-4">
                <input type="hidden" name="is_active" value="0">

                <div class="d-grid mt-4">
                    <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
                </div>
            </div>
        </form>
    </div>
@endsection
