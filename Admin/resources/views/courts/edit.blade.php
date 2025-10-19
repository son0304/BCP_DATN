@extends('app')

@section('content')
    <div class="container-fluid">
        <h1>Chỉnh sửa sân: {{ $court->name }}</h1>

        <div class="card">
            <div class="card-body">
                <form action="{{ route('admin.courts.update', $court) }}" method="POST">
                    @csrf
                    @method('PUT')
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

                    {{-- THÔNG TIN CƠ BẢN CỦA SÂN --}}
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="name" class="form-label">Tên sân</label>
                                <input type="text" class="form-control" id="name" name="name"
                                    value="{{ old('name', $court->name) }}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="venue_id" class="form-label">Thương hiệu (Venue)</label>
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
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="venue_type_id" class="form-label">Loại hình</label>
                                <select class="form-select" id="venue_type_id" name="venue_type_id" required>
                                    @foreach ($venueTypes as $venueType)
                                        <option value="{{ $venueType->id }}"
                                            {{ old('venue_type_id', $court->venue_type_id) == $venueType->id ? 'selected' : '' }}>
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
                                    <option value="1"
                                        {{ old('is_indoor', $court->is_indoor) == 1 ? 'selected' : '' }}>Trong
                                        nhà</option>
                                    <option value="0"
                                        {{ old('is_indoor', $court->is_indoor) == 0 ? 'selected' : '' }}>Ngoài
                                        trời</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="surface" class="form-label">Bề mặt sân</label>
                        <input type="text" class="form-control" id="surface" name="surface"
                            value="{{ old('surface', $court->surface) }}">
                    </div>

                    {{-- PHẦN CẬP NHẬT GIÁ --}}
                    <hr>
                    <h4 class="mt-4">Cập nhật khung giờ và giá</h4>
                    <p class="text-muted">Thay đổi sẽ xóa và tạo lại lịch cho các suất chưa được đặt trong tương lai.</p>

                    <div id="time-slots-container">
                        {{-- Hiển thị các khung giờ/giá đã có --}}
                        @foreach ($currentPrices as $slotId => $price)
                            <div class="row align-items-center mb-2 time-slot-row">
                                <div class="col-md-5">
                                    <label class="form-label">Khung giờ</label>
                                    <select class="form-select" name="slot_ids[]" required>
                                        <option value="">-- Chọn khung giờ --</option>
                                        @foreach ($timeSlots as $slot)
                                            <option value="{{ $slot->id }}"
                                                {{ $slotId == $slot->id ? 'selected' : '' }}>
                                                {{ date('H:i', strtotime($slot->start_time)) }} -
                                                {{ date('H:i', strtotime($slot->end_time)) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label">Giá (VNĐ)</label>
                                    <input type="number" class="form-control" name="slot_prices[]" placeholder="Nhập giá"
                                        min="0" step="1000" value="{{ $price }}" required>
                                </div>
                                <div class="col-md-2 d-flex align-items-end">
                                    <button type="button" class="btn btn-danger remove-slot-btn">Xóa</button>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <button type="button" id="add-time-slot-btn" class="btn btn-info mt-2">Thêm khung giờ</button>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-success">Cập nhật</button>
                        <a href="{{ route('admin.courts.index') }}" class="btn btn-secondary">Hủy bỏ</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection


<script>
    // Wait for document to be ready
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Edit form JavaScript loaded');
        
        // Check if jQuery is available
        if (typeof $ === 'undefined') {
            console.error('jQuery is not loaded!');
            return;
        }
        
        console.log('jQuery version:', $.fn.jquery);
        
        try {
            const allTimeSlots = @json($timeSlots);
            console.log('Time slots data:', allTimeSlots);
            
            // Check if elements exist
            console.log('Add button exists:', $('#add-time-slot-btn').length > 0);
            console.log('Container exists:', $('#time-slots-container').length > 0);
            console.log('Current time slot rows:', $('.time-slot-row').length);

            function createTimeSlotRow() {
                const options = allTimeSlots.map(slot => {
                    const startTime = new Date('1970-01-01T' + slot.start_time).toLocaleTimeString('vi-VN', {hour: '2-digit', minute:'2-digit'});
                    const endTime = new Date('1970-01-01T' + slot.end_time).toLocaleTimeString('vi-VN', {hour: '2-digit', minute:'2-digit'});
                    return `<option value="${slot.id}">${startTime} - ${endTime}</option>`;
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

            // Add time slot functionality
            $(document).on('click', '#add-time-slot-btn', function(e) {
                e.preventDefault();
                console.log('Add time slot button clicked');
                try {
                    const newRow = createTimeSlotRow();
                    $('#time-slots-container').append(newRow);
                    console.log('New time slot row added successfully');
                } catch (error) {
                    console.error('Error adding time slot row:', error);
                }
            });

            // Remove time slot functionality
            $(document).on('click', '.remove-slot-btn', function(e) {
                e.preventDefault();
                console.log('Remove time slot button clicked');
                try {
                    $(this).closest('.time-slot-row').remove();
                    console.log('Time slot row removed successfully');
                } catch (error) {
                    console.error('Error removing time slot row:', error);
                }
            });

            // Form validation
            $('form').on('submit', function(e) {
                if ($('.time-slot-row').length === 0) {
                    e.preventDefault();
                    alert('Vui lòng thêm ít nhất một khung giờ trước khi lưu.');
                    return false;
                }
            });
            
        } catch (error) {
            console.error('Error in edit form JavaScript:', error);
        }
    });
</script>
