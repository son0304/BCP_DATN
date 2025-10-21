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
    .btn-outline-primary {
        --bs-btn-hover-color: #fff;
    }
    .form-control:focus, .form-select:focus {
        border-color: #84c887;
        box-shadow: 0 0 0 0.25rem rgba(52, 135, 56, 0.25);
    }
</style>

<div class="container-fluid mt-4">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white border-0 py-3">
            <h1 class="h4 mb-0">Chỉnh sửa sân: <strong>{{ $court->name }}</strong></h1>
        </div>
        <div class="card-body">
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

            <form action="{{ route('admin.courts.update', $court) }}" method="POST">
                @csrf
                @method('PUT')

                {{-- THÔNG TIN CƠ BẢN --}}
                <fieldset class="mb-4">
                    <legend class="h6">1. Thông tin cơ bản</legend>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Tên sân <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name"
                                value="{{ old('name', $court->name) }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="venue_id" class="form-label">Thương hiệu (Venue) <span class="text-danger">*</span></label>
                            <select class="form-select" id="venue_id" name="venue_id" required>
                                @foreach ($venues as $venue)
                                <option value="{{ $venue->id }}"
                                    {{ old('venue_id', $court->venue_id) == $venue->id ? 'selected' : '' }}>
                                    {{ $venue->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="venue_type_id" class="form-label">Loại hình <span class="text-danger">*</span></label>
                            <select class="form-select" id="venue_type_id" name="venue_type_id" required>
                                @foreach ($venueTypes as $venueType)
                                <option value="{{ $venueType->id }}"
                                    {{ old('venue_type_id', $court->venue_type_id) == $venueType->id ? 'selected' : '' }}>
                                    {{ $venueType->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="is_indoor" class="form-label">Loại sân <span class="text-danger">*</span></label>
                            <select class="form-select" id="is_indoor" name="is_indoor" required>
                                <option value="1" {{ old('is_indoor', $court->is_indoor) == 1 ? 'selected' : '' }}>Trong nhà</option>
                                <option value="0" {{ old('is_indoor', $court->is_indoor) == 0 ? 'selected' : '' }}>Ngoài trời</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="surface" class="form-label">Bề mặt sân</label>
                            <input type="text" class="form-control" id="surface" name="surface"
                                value="{{ old('surface', $court->surface) }}" placeholder="Ví dụ: Cỏ nhân tạo, Sàn gỗ">
                        </div>
                    </div>
                </fieldset>

                {{-- PHẦN KHUNG GIỜ & GIÁ --}}
                <fieldset>
                    <legend class="h6">2. Cập nhật khung giờ & Giá</legend>
                    <p class="text-muted small">
                        Thay đổi tại đây sẽ xóa và tạo lại lịch cho các suất **chưa được đặt** trong tương lai. Các suất đã đặt sẽ không bị ảnh hưởng.
                    </p>
                    <div id="time-slots-container" class="p-3 bg-light rounded border">
                        {{-- Hiển thị các khung giờ/giá đã có --}}
                        @foreach ($currentPrices as $slotId => $price)
                            <div class="row align-items-center mb-2 time-slot-row p-2 bg-white rounded border">
                                <div class="col-md-5">
                                    <select class="form-select form-select-sm" name="slot_ids[]" required>
                                        <option value="">-- Chọn khung giờ --</option>
                                        @foreach ($timeSlots as $slot)
                                            <option value="{{ $slot->id }}" {{ $slotId == $slot->id ? 'selected' : '' }}>
                                                {{ date('H:i', strtotime($slot->start_time)) }} - {{ date('H:i', strtotime($slot->end_time)) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-5">
                                    <input type="number" class="form-control form-control-sm" name="slot_prices[]" placeholder="Nhập giá (VNĐ)"
                                        min="0" step="1000" value="{{ $price }}" required>
                                </div>
                                <div class="col-md-2 d-flex align-items-center justify-content-end">
                                    <button type="button" class="btn btn-sm btn-outline-danger remove-slot-btn" title="Xóa khung giờ">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <button type="button" id="add-time-slot-btn" class="btn btn-outline-primary mt-3">
                        <i class="fas fa-plus me-1"></i> Thêm khung giờ
                    </button>
                </fieldset>

                <div class="card-footer bg-white text-end border-0 px-0 pt-4">
                    <a href="{{ route('admin.courts.index') }}" class="btn btn-secondary">Hủy bỏ</a>
                    <button type="submit" class="btn btn-primary">Cập nhật</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof $ === 'undefined') {
            console.error('jQuery is not loaded!');
            return;
        }

        const allTimeSlots = @json($timeSlots);
        const container = $('#time-slots-container');

        // Hàm tạo một hàng slot mới (để thêm)
        function createTimeSlotRow() {
            const options = allTimeSlots.map(slot => {
                const startTime = slot.start_time.substring(0, 5);
                const endTime = slot.end_time.substring(0, 5);
                return `<option value="${slot.id}">${startTime} - ${endTime}</option>`;
            }).join('');

            return `
            <div class="row align-items-center mb-2 time-slot-row p-2 bg-white rounded border">
                <div class="col-md-5">
                    <select class="form-select form-select-sm" name="slot_ids[]" required>
                        <option value="" disabled selected>-- Chọn khung giờ --</option>
                        ${options}
                    </select>
                </div>
                <div class="col-md-5">
                    <input type="number" class="form-control form-control-sm" name="slot_prices[]" placeholder="Nhập giá (VNĐ)" min="0" step="1000" required>
                </div>
                <div class="col-md-2 d-flex align-items-center justify-content-end">
                    <button type="button" class="btn btn-sm btn-outline-danger remove-slot-btn" title="Xóa khung giờ">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>`;
        }

        // Sự kiện click nút "Thêm khung giờ"
        $(document).on('click', '#add-time-slot-btn', function(e) {
            e.preventDefault();
            container.append(createTimeSlotRow());
        });

        // Sự kiện click nút "Xóa" (cho cả hàng cũ và hàng mới)
        $(document).on('click', '.remove-slot-btn', function(e) {
            e.preventDefault();
            $(this).closest('.time-slot-row').remove();
        });
    });
</script>
@endpush
