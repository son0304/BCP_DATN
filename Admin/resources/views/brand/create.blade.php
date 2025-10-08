@extends('app')

@section('content')
<div class="container-fluid py-4"> =
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h4 mb-0">Tạo thương hiệu sân mới</h2>
            <p class="text-muted mb-0">Nhập thông tin chi tiết cho thương hiệu sân.</p>
        </div>
        <div>
            <a href="{{ route('brand.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Quay lại danh sách
            </a>
        </div>
    </div>

    <form action="{{ route('brand.store') }}" method="POST">
        @csrf
        <div class="row">
            <div class="col-lg-8">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Thông tin cơ bản</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="name" class="form-label fw-bold">Tên thương hiệu (sân)</label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" id="name" value="{{ old('name') }}" placeholder="Ví dụ: Sân bóng đá Thống Nhất" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="owner_id" class="form-label fw-bold">Chủ sở hữu</label>
                            <select name="owner_id" id="owner_id" class="form-select @error('owner_id') is-invalid @enderror" required>
                                <option value="" disabled selected>-- Chọn chủ sở hữu --</option>
                                @foreach($owners as $owner)
                                    <option value="{{ $owner->id }}" {{ old('owner_id') == $owner->id ? 'selected' : '' }}>{{ $owner->name }}</option>
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
                                <select name="province_id" id="province_id" class="form-select @error('province_id') is-invalid @enderror" required>
                                     <option value="" disabled selected>-- Chọn Tỉnh/Thành --</option>
                                     @foreach($provinces as $province)
                                        <option value="{{ $province->id }}" {{ old('province_id') == $province->id ? 'selected' : '' }}>{{ $province->name }}</option>
                                    @endforeach
                                </select>
                                @error('province_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="district_id" class="form-label">Quận/Huyện</label>
                                <select name="district_id" id="district_id" class="form-select @error('district_id') is-invalid @enderror" required>
                                     <option value="" disabled selected>-- Chọn Quận/Huyện --</option>
                                     @foreach($districts as $district)
                                        <option value="{{ $district->id }}" {{ old('district_id') == $district->id ? 'selected' : '' }}>{{ $district->name }}</option>
                                    @endforeach
                                </select>
                                @error('district_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="address_detail" class="form-label">Địa chỉ chi tiết</label>
                            <input type="text" name="address_detail" class="form-control" id="address_detail" value="{{ old('address_detail') }}" placeholder="Số nhà, tên đường, phường/xã...">
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card shadow-sm mb-4">
                     <div class="card-header">
                        <h5 class="card-title mb-0">Hành động</h5>
                    </div>
                    <div class="card-body">
                         <div class="form-check form-switch mb-3">
                            <input type="hidden" name="is_active" value="0">
                            <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" checked>
                            <label class="form-check-label" for="is_active">Kích hoạt thương hiệu</p></label>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Lưu và tạo mới</button>
                        </div>
                    </div>
                </div>
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Thông tin bổ sung</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="phone" class="form-label fw-bold">Số điện thoại</label>
                            <input type="tel" name="phone" class="form-control" id="phone" value="{{ old('phone') }}" placeholder="09xxxxxxxx">
                        </div>
                        
                        <div>
                            <label class="form-label fw-bold d-block">Loại hình sân</label>
                            @foreach($venue_types as $type)
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" name="venue_types[]" value="{{ $type->id }}" id="type_{{ $type->id }}">
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
    </form>
</div>
@endsection