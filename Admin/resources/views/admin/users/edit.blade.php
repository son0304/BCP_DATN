@extends('app')

@section('content')
<div class="container-fluid">
    {{-- Hiển thị thông báo lỗi chung --}}
    @if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-triangle mr-2"></i> {{ session('error') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    @endif

    @if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <strong><i class="fas fa-exclamation-circle mr-1"></i> Vui lòng kiểm tra lại dữ liệu!</strong>
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
                        <div>
                            <h4 class="card-title mb-0 text-primary font-weight-bold">Chỉnh sửa Người dùng</h4>
                            <small class="text-muted">ID: {{ $user->id }} - {{ $user->email }}</small>
                        </div>
                        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left mr-1"></i> Quay lại
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <form method="POST" action="{{ route('admin.users.update', $user) }}" id="editUserForm">
                        @csrf
                        @method('PUT')

                        <h6 class="text-muted mb-3 border-bottom pb-2">Thông tin tài khoản</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">Họ và tên <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                                        id="name" name="name" value="{{ old('name', $user->name) }}"
                                        required>
                                    @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="email">Email đăng nhập <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror"
                                        id="email" name="email" value="{{ old('email', $user->email) }}"
                                        required>
                                    @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="password">Mật khẩu mới</label>
                                    <input type="password" class="form-control @error('password') is-invalid @enderror"
                                        id="password" name="password" autocomplete="new-password">
                                    <small class="form-text text-muted"><i class="fas fa-info-circle mr-1"></i> Chỉ nhập nếu bạn muốn thay đổi mật khẩu</small>
                                    @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="password_confirmation">Xác nhận mật khẩu mới</label>
                                    <input type="password" class="form-control"
                                        id="password_confirmation" name="password_confirmation">
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
                                        <option value="">-- Chọn vai trò --</option>
                                        @foreach($roles as $role)
                                        <option value="{{ $role->id }}"
                                            {{ old('role_id', $user->role_id) == $role->id ? 'selected' : '' }}>
                                            {{ $role->name }}
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
                                        id="phone" name="phone" value="{{ old('phone', $user->phone) }}">
                                    @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="province_id">Tỉnh/Thành phố</label>
                                    <select class="form-control @error('province_id') is-invalid @enderror" id="province_id" name="province_id">
                                        <option value="">Chọn tỉnh/thành phố</option>
                                        @foreach($provinces as $province)
                                        <option value="{{ $province->id }}" {{ old('province_id', $user->province_id) == $province->id ? 'selected' : '' }}>
                                            {{ $province->name }}
                                        </option>
                                        @endforeach
                                    </select>
                                    @error('province_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="district_id">Quận/Huyện</label>
                                    {{-- Mặc định disabled, JS sẽ check và enable --}}
                                    <select class="form-control @error('district_id') is-invalid @enderror" id="district_id" name="district_id" disabled>
                                        <option value="">-- Vui lòng chọn Tỉnh/Thành trước --</option>
                                        @foreach($districts as $district)
                                        <option value="{{ $district->id }}"
                                            class="district-option province-{{ $district->province_id }}"
                                            {{ old('district_id', $user->district_id) == $district->id ? 'selected' : '' }}>
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
                                        {{-- Logic checked: Ưu tiên old(), nếu không có old() thì lấy từ DB --}}
                                        <input type="checkbox" class="custom-control-input" id="is_active" name="is_active"
                                            value="1" {{ old('is_active', $user->is_active) ? 'checked' : '' }}>
                                        <label class="custom-control-label font-weight-bold" for="is_active">
                                            Kích hoạt tài khoản
                                        </label>
                                    </div>
                                    <small class="form-text text-muted ml-4">
                                        Trạng thái hiện tại:
                                        <span class="badge badge-{{ $user->is_active ? 'success' : 'danger' }}">
                                            {{ $user->is_active ? 'Đang hoạt động' : 'Bị vô hiệu hóa' }}
                                        </span>
                                    </small>
                                </div>
                            </div>
                        </div>

                        <div class="form-group mt-4 d-flex justify-content-end">
                            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary mr-2">
                                <i class="fas fa-times mr-1"></i> Hủy bỏ
                            </a>
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="fas fa-save mr-1"></i> Cập nhật thay đổi
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Tái sử dụng đoạn script lọc Quận/Huyện --}}
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const provinceSelect = document.getElementById('province_id');
        const districtSelect = document.getElementById('district_id');

        // Cache tất cả options quận huyện
        const allDistricts = Array.from(districtSelect.querySelectorAll('.district-option'));

        function updateDistrictOptions(provinceId) {
            districtSelect.innerHTML = '';

            if (!provinceId) {
                const defaultOption = document.createElement('option');
                defaultOption.text = "-- Vui lòng chọn Tỉnh/Thành trước --";
                defaultOption.value = "";
                districtSelect.add(defaultOption);
                districtSelect.disabled = true;
                return;
            }

            districtSelect.disabled = false;

            const defaultOption = document.createElement('option');
            defaultOption.text = "-- Chọn Quận/Huyện --";
            defaultOption.value = "";
            districtSelect.add(defaultOption);

            allDistricts.forEach(option => {
                if (option.classList.contains('province-' + provinceId)) {
                    districtSelect.add(option.cloneNode(true));
                }
            });

            // Logic chọn lại giá trị cũ (Ưu tiên Old Input > Database Value)
            // Laravel old() ở HTML đã xử lý logic ưu tiên, ở đây ta chỉ cần set value
            const targetId = "{{ old('district_id', $user->district_id) }}";
            if (targetId) {
                // Kiểm tra xem targetId có nằm trong danh sách vừa lọc không rồi mới set
                // (Tránh trường hợp User đổi tỉnh khác, quận cũ vẫn set value ẩn)
                for (let option of districtSelect.options) {
                    if (option.value == targetId) {
                        districtSelect.value = targetId;
                        break;
                    }
                }
            }
        }

        provinceSelect.addEventListener('change', function() {
            updateDistrictOptions(this.value);
            // Khi người dùng chủ động đổi tỉnh, reset quận về rỗng
            // (Trừ khi tỉnh mới chọn trùng với dữ liệu đang có - ít xảy ra)
            if (this.value != "{{ old('province_id', $user->province_id) }}") {
                districtSelect.value = "";
            }
        });

        // Chạy khi load trang để hiển thị dữ liệu hiện tại của User
        if (provinceSelect.value) {
            updateDistrictOptions(provinceSelect.value);
        }
    });
</script>
@endpush
@endsection