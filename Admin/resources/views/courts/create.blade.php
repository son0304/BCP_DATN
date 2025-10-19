@extends('app')

@section('content')
<div class="container-fluid">
    <h1>Thêm sân mới</h1>

    <div class="card">
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

            <form action="{{ route('admin.courts.store') }}" method="POST">
                @csrf
                {{-- THÔNG TIN CƠ BẢN --}}
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="name" class="form-label">Tên sân</label>
                            <input type="text" class="form-control" id="name" name="name"
                                value="{{ old('name') }}" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="venue_id" class="form-label">Thương hiệu (Venue)</label>
                            <select class="form-select" id="venue_id" name="venue_id" required>
                                <option value="">-- Chọn thương hiệu --</option>
                                @foreach ($venues as $venue)
                                <option value="{{ $venue->id }}"
                                    {{ old('venue_id') == $venue->id ? 'selected' : '' }}>
                                    {{ $venue->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="venue_type_id" class="form-label">Loại hình</label>
                            <select class="form-select" id="venue_type_id" name="venue_type_id" required>
                                <option value="">-- Chọn loại hình --</option>
                                @foreach ($venueTypes as $venueType)
                                <option value="{{ $venueType->id }}"
                                    {{ old('venue_type_id') == $venueType->id ? 'selected' : '' }}>
                                    {{ $venueType->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="is_indoor" class="form-label">Loại sân</label>
                            <select class="form-select" id="is_indoor" name="is_indoor" required>
                                <option value="1" {{ old('is_indoor', '1') == '1' ? 'selected' : '' }}>Trong nhà</option>
                                <option value="0" {{ old('is_indoor') == '0' ? 'selected' : '' }}>Ngoài trời</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="surface" class="form-label">Bề mặt sân</label>
                    <input type="text" class="form-control" id="surface" name="surface"
                        value="{{ old('surface') }}">
                </div>

                {{-- PHẦN KHUNG GIỜ & GIÁ --}}
                <hr>
                <h4 class="mt-4">Chọn khung giờ và thiết lập giá</h4>
                <p class="text-muted">
                    Nhấn "Thêm khung giờ" để thêm các suất bạn muốn mở bán.
                    <strong>Bạn phải thêm ít nhất một khung giờ.</strong>
                </p>

                <div id="time-slots-container"></div>

                <button type="button" id="add-time-slot-btn" class="btn btn-info mt-2">Thêm khung giờ</button>

                <div class="mt-4">
                    <button type="submit" class="btn btn-success">Lưu lại</button>
                    <a href="{{ route('admin.courts.index') }}" class="btn btn-secondary">Hủy bỏ</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Create court JS loaded');

        if (typeof $ === 'undefined') {
            console.error('jQuery chưa được tải!');
            return;
        }

        const timeSlots = @json($timeSlots);
        console.log('Available time slots:', timeSlots);

        // Hàm tạo row khung giờ
        function createTimeSlotRow() {
            const options = timeSlots.map(slot => {
                try {
                    const startTime = new Date('1970-01-01T' + slot.start_time)
                        .toLocaleTimeString('vi-VN', {
                            hour: '2-digit',
                            minute: '2-digit'
                        });
                    const endTime = new Date('1970-01-01T' + slot.end_time)
                        .toLocaleTimeString('vi-VN', {
                            hour: '2-digit',
                            minute: '2-digit'
                        });
                    return `<option value="${slot.id}">${startTime} - ${endTime}</option>`;
                } catch (e) {
                    return `<option value="${slot.id}">${slot.start_time} - ${slot.end_time}</option>`;
                }
            }).join('');

            return `
        <div class="row align-items-center mb-2 time-slot-row">
            <div class="col-md-5">
                <label class="form-label">Khung giờ</label>
                <select class="form-select" name="slot_ids[]" required>
                    <option value="">-- Chọn khung giờ --</option>
                    ${options}
                </select>
            </div>
            <div class="col-md-5">
                <label class="form-label">Giá (VNĐ)</label>
                <input type="number" class="form-control" name="slot_prices[]" placeholder="Nhập giá" min="0" step="1000" required>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="button" class="btn btn-danger remove-slot-btn">Xóa</button>
            </div>
        </div>`;
        }

        // Thêm khung giờ
        $(document).on('click', '#add-time-slot-btn', function(e) {
            e.preventDefault();
            console.log('Thêm khung giờ mới');
            $('#time-slots-container').append(createTimeSlotRow());
        });

        // Xóa khung giờ
        $(document).on('click', '.remove-slot-btn', function(e) {
            e.preventDefault();
            $(this).closest('.time-slot-row').remove();
        });

        // Kiểm tra trước khi submit
        $('form').on('submit', function(e) {
            if ($('.time-slot-row').length === 0) {
                e.preventDefault();
                alert('Vui lòng thêm ít nhất một khung giờ trước khi lưu.');
            }
        });
    });
</script>
@endpush