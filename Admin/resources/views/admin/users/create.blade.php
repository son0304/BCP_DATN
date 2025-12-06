@extends('app')

@section('content')
<div class="container-fluid">
    {{-- Hiển thị thông báo lỗi chung từ Controller (nếu có) --}}
    @if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-triangle mr-2"></i> {{ session('error') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    @endif

    {{-- Hiển thị lỗi validation tổng quát (nếu muốn gom lại 1 chỗ) --}}
    @if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <strong><i class="fas fa-exclamation-circle mr-1"></i> Vui lòng kiểm tra lại dữ liệu bên dưới:</strong>
        <ul class="mb-0 mt-2 pl-4">
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    @endif

    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0 text-primary font-weight-bold">Thêm Người dùng mới</h4>
                        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left mr-1"></i> Quay lại danh sách
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <form method="POST" action="{{ route('admin.users.store') }}" id="createUserForm">
                        @csrf

                        <h6 class="text-muted mb-3 border-bottom pb-2">Thông tin tài khoản</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">Họ và tên <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                                        id="name" name="name" value="{{ old('name') }}"
                                        placeholder="Nhập họ và tên" required autofocus>
                                    @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="email">Email đăng nhập <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror"
                                        id="email" name="email" value="{{ old('email') }}"
                                        placeholder="example@email.com" required>
                                    @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="password">Mật khẩu <span class="text-danger">*</span></label>
                                    <input type="password" class="form-control @error('password') is-invalid @enderror"
                                        id="password" name="password" required autocomplete="new-password">
                                    @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="password_confirmation">Xác nhận mật khẩu <span class="text-danger">*</span></label>
                                    <input type="password" class="form-control"
                                        id="password_confirmation" name="password_confirmation" required>
                                </div>
                            </div>
                        </div>

                        <h6 class="text-muted mb-3 border-bottom pb-2 mt-4">Thông tin cá nhân & Vai trò</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="role_id">Vai trò hệ thống <span class="text-danger">*</span></label>
                                    <select class="form-control custom-select @error('role_id') is-invalid @enderror"
                                        id="role_id" name="role_id" required>
                                        <option value="">Chọn vai trò</option>

                                        @foreach($roles as $role)

                                        <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>

                                            @if($role->name === 'admin')

                                            👑 Admin - Quản trị viên

                                            @elseif($role->name === 'venue_owner')

                                            👔 Venue Owner - Chủ sân

                                            @else

                                            👤 User - Khách hàng

                                            @endif

                                        </option>

                                        @endforeach

                                    </select>
                                    @error('role_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="phone">Số điện thoại</label>
                                    <input type="text" class="form-control @error('phone') is-invalid @enderror"
                                        id="phone" name="phone" value="{{ old('phone') }}"
                                        placeholder="09xxxxxxxx">
                                    @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="province_id">Tỉnh/Thành phố <span class="text-danger">*</span></label>
                                    <select class="form-control @error('province_id') is-invalid @enderror" id="province_id" name="province_id">
                                        <option value="">-- Chọn Tỉnh/Thành phố --</option>
                                        @foreach($provinces as $province)
                                        <option value="{{ $province->id }}" {{ old('province_id') == $province->id ? 'selected' : '' }}>
                                            {{ $province->name }}
                                        </option>
                                        @endforeach
                                    </select>
                                    @error('province_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="district_id">Quận/Huyện <span class="text-danger">*</span></label>
                                    {{-- 👇 Thêm thuộc tính disabled mặc định --}}
                                    <select class="form-control @error('district_id') is-invalid @enderror" id="district_id" name="district_id" disabled>
                                        <option value="">-- Vui lòng chọn Tỉnh/Thành trước --</option>
                                        {{-- Render tất cả options nhưng ẩn đi, JS sẽ lọc lại --}}
                                        @foreach($districts as $district)
                                        <option value="{{ $district->id }}"
                                            class="district-option province-{{ $district->province_id }}"
                                            {{ old('district_id') == $district->id ? 'selected' : '' }}>
                                            {{ $district->name }}
                                        </option>
                                        @endforeach
                                    </select>
                                    @error('district_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-12">
                                <div class="form-group bg-light p-3 rounded border">
                                    <div class="custom-control custom-switch">
                                        {{-- Logic checked: Nếu có old('is_active') thì dùng, nếu không (lần đầu load) thì mặc định unchecked --}}
                                        <input type="checkbox" class="custom-control-input" id="is_active" name="is_active"
                                            value="1" {{ old('is_active') ? 'checked' : '' }}>
                                        <label class="custom-control-label font-weight-bold" for="is_active">
                                            Kích hoạt tài khoản này ngay lập tức
                                        </label>
                                    </div>
                                    <small class="form-text text-muted ml-4">Nếu tắt, người dùng sẽ không thể đăng nhập vào hệ thống.</small>
                                </div>
                            </div>
                        </div>

                        <div class="form-group mt-4 d-flex justify-content-end">
                            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary mr-2">
                                <i class="fas fa-times mr-1"></i> Hủy bỏ
                            </a>
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="fas fa-save mr-1"></i> Lưu người dùng
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Script đơn giản để lọc Quận/Huyện (Nếu chưa có API Ajax) --}}
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const provinceSelect = document.getElementById('province_id');
        const districtSelect = document.getElementById('district_id');

        // 1. Sao chép toàn bộ options của quận huyện vào bộ nhớ đệm (bỏ option đầu tiên)
        // Lý do: Để khi lọc không bị mất dữ liệu gốc
        const allDistricts = Array.from(districtSelect.querySelectorAll('.district-option'));

        function updateDistrictOptions(provinceId) {
            // Xóa hết options hiện tại trong district select
            districtSelect.innerHTML = '';

            if (!provinceId) {
                // Nếu không chọn tỉnh -> Disable quận và hiện thông báo
                const defaultOption = document.createElement('option');
                defaultOption.text = "-- Vui lòng chọn Tỉnh/Thành trước --";
                defaultOption.value = "";
                districtSelect.add(defaultOption);
                districtSelect.disabled = true;
                return;
            }

            // Nếu đã chọn tỉnh -> Enable quận
            districtSelect.disabled = false;

            // Thêm option mặc định
            const defaultOption = document.createElement('option');
            defaultOption.text = "-- Chọn Quận/Huyện --";
            defaultOption.value = "";
            districtSelect.add(defaultOption);

            // Lọc và thêm các quận thuộc tỉnh đã chọn
            allDistricts.forEach(option => {
                if (option.classList.contains('province-' + provinceId)) {
                    // Clone node để tránh lỗi tham chiếu
                    districtSelect.add(option.cloneNode(true));
                }
            });

            // Giữ lại giá trị cũ (Old Input) nếu có (khi validate fail)
            const oldDistrictId = "{{ old('district_id') }}";
            if (oldDistrictId) {
                districtSelect.value = oldDistrictId;
            }
        }

        // Sự kiện khi người dùng thay đổi Tỉnh
        provinceSelect.addEventListener('change', function() {
            updateDistrictOptions(this.value);
        });

        // Chạy 1 lần khi trang load (để xử lý trường hợp form reload khi có lỗi validation)
        if (provinceSelect.value) {
            updateDistrictOptions(provinceSelect.value);
        }
    });
</script>
@endpush
@endsection