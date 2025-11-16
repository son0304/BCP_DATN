@extends('app')

@section('content')


    <div class="container-fluid mt-4">

        <div class="card shadow-sm border-0">
            <div class="card-header bg-white border-0 py-3">
                {{-- Thêm thông báo sân này thuộc Venue nào (nếu có) --}}
                @if (request('venue_id') && ($venue = \App\Models\Venue::find(request('venue_id'))))
                    <small class="text-muted">
                        — cho thương hiệu: <span class="text-success fw-bold">{{ $venue->name }}</span>
                    </small>
                @endif
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

                <form action="{{ route('owner.venues.courts.store', ['venue' => $venue->id]) }}" method="POST"> @csrf
                    <input type="hidden" name="venue_id" value="{{ $venue->id }}">

                    <fieldset class="mb-4">
                        <legend class="h6 text-primary">1. Thông tin cơ bản</legend>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">Tên sân <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="name" name="name"
                                    value="{{ old('name') }}" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="venue_type_id" class="form-label">Loại hình <span
                                        class="text-danger">*</span></label>
                                <select class="form-select" id="venue_type_id" name="venue_type_id" required>
                                    <option value="" disabled selected>-- Chọn loại hình --</option>
                                    @foreach ($venueTypes as $venueType)
                                        <option value="{{ $venueType->id }}"
                                            {{ old('venue_type_id') == $venueType->id ? 'selected' : '' }}>
                                            {{ $venueType->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="is_indoor" class="form-label">Loại sân <span
                                        class="text-danger">*</span></label>
                                <select class="form-select" id="is_indoor" name="is_indoor" required>
                                    <option value="1" {{ old('is_indoor', '1') == '1' ? 'selected' : '' }}>Trong nhà
                                    </option>
                                    <option value="0" {{ old('is_indoor', '0') == '0' ? 'selected' : '' }}>Ngoài trời
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="surface" class="form-label">Bề mặt sân</label>
                                <input type="text" class="form-control" id="surface" name="surface"
                                    value="{{ old('surface') }}" placeholder="Ví dụ: Cỏ nhân tạo, Sàn gỗ">
                            </div>
                        </div>
                    </fieldset>

                    {{-- Khung giờ & giá giống như trước --}}
                    <fieldset>
                        <legend class="h6 text-primary">2. Khung giờ & Giá</legend>
                        <p class="text-muted small">
                            Thiết lập các khung giờ hoạt động và giá tiền tương ứng. Lịch hoạt động cho 30 ngày tới sẽ được
                            tự động tạo. <strong>Bạn phải thêm ít nhất một khung giờ.</strong>
                        </p>

                        <div class="row gx-2 mb-2 d-none d-md-flex small text-muted fw-bold">
                            <div class="col-md-3"><label>Giờ bắt đầu</label></div>
                            <div class="col-md-3"><label>Giờ kết thúc</label></div>
                            <div class="col-md-4"><label>Giá (VNĐ)</label></div>
                            <div class="col-md-2 text-end"><label>Xóa</label></div>
                        </div>

                        <div id="time-slots-container" class="p-3 bg-light rounded border"></div>
                        <button type="button" id="add-time-slot-btn" class="btn btn-outline-primary mt-3">
                            <i class="fas fa-plus me-1"></i> Thêm khung giờ
                        </button>
                    </fieldset>

                    <div class="card-footer bg-white text-end border-0 px-0 pt-4">
                        <a href="/venue/{{ $venue->id }}" class="btn btn-secondary">Hủy bỏ</a>
                        <button type="submit" class="btn btn-primary">Lưu lại</button>
                    </div>
                </form>

                <option value="{{ $venueType->id }}" {{ old('venue_type_id') == $venueType->id ? 'selected' : '' }}>
                    {{ $venueType->name }}
                </option>
                @endforeach
                </select>
            </div>
            <div class="col-md-4 mb-3">
                <label for="is_indoor" class="form-label">Loại sân <span class="text-danger">*</span></label>
                <select class="form-select" id="is_indoor" name="is_indoor" required>
                    <option value="1" {{ old('is_indoor', '1') == '1' ? 'selected' : '' }}>Trong nhà
                    </option>
                    <option value="0" {{ old('is_indoor', '0') == '0' ? 'selected' : '' }}>Ngoài trời
                    </option>
                </select>
            </div>
            <div class="col-md-4 mb-3">
                <label for="surface" class="form-label">Bề mặt sân</label>
                <input type="text" class="form-control" id="surface" name="surface" value="{{ old('surface') }}"
                    placeholder="Ví dụ: Cỏ nhân tạo, Sàn gỗ">
            </div>
        </div>
        </fieldset>

        {{-- Khung giờ & giá giống như trước --}}
        <fieldset>
            <legend class="h6 text-primary">2. Khung giờ & Giá</legend>
            <p class="text-muted small">
                Thiết lập các khung giờ hoạt động và giá tiền tương ứng. Lịch hoạt động cho 30 ngày tới sẽ được
                tự động tạo. <strong>Bạn phải thêm ít nhất một khung giờ.</strong>
            </p>

            <div id="time-slots-container" class="p-3 bg-light rounded border">
                <div class="row gx-2 mb-2 d-none d-md-flex small text-muted fw-bold">
                    <div class="col-md-3"><label>Giờ bắt đầu</label></div>
                    <div class="col-md-3"><label>Giờ kết thúc</label></div>
                    <div class="col-md-4"><label>Giá (VNĐ)</label></div>
                </div>
            </div>

            <button type="button" id="add-time-slot-btn" class="btn btn-outline-primary mt-3">
                <i class="fas fa-plus me-1"></i> Thêm khung giờ
            </button>
        </fieldset>

        <div class="card-footer bg-white text-end border-0 px-0 pt-4">
            <a href="/owner/venues/{{ $venue->id }}" class="btn btn-secondary">Hủy bỏ</a>
            <button type="submit" class="btn btn-primary">Lưu lại</button>
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

            const container = $('#time-slots-container');

            // --- ĐÃ SỬA LẠI HTML TRONG HÀM NÀY CHO ĐẸP HƠN ---
            function createTimeSlotRow() {
                return `
                <div class="row gx-2 align-items-center mb-2 time-slot-row p-2 bg-white rounded border">
                    <div class="col-md-3">
                        <label class="form-label d-md-none small">Giờ bắt đầu</label>
                        <input type="time" class="form-control form-control-sm time-start" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label d-md-none small">Giờ kết thúc</label>
                        <input type="time" class="form-control form-control-sm time-end" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label d-md-none small">Giá (VNĐ)</label>
                        <input type="number" class="form-control form-control-sm time-price" placeholder="Nhập giá (VNĐ)" min="0" step="1000" required>
                    </div>
                    <div class="col-md-2 d-flex align-items-center justify-content-end">
                        <button type="button" class="btn btn-sm btn-outline-danger remove-slot-btn" title="Xóa khung giờ">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>`;
            }

            // --- TOÀN BỘ LOGIC CÒN LẠI GIỮ NGUYÊN ---

            function splitTimeIntoHourlySlots(startTime, endTime, price) {
                const slots = [];
                const start = new Date('2000-01-01 ' + startTime);
                const end = new Date('2000-01-01 ' + endTime);

                if (end <= start) {
                    end.setDate(end.getDate() + 1);
                }

                let current = new Date(start);

                while (current < end) {
                    const nextHour = new Date(current);
                    nextHour.setHours(nextHour.getHours() + 1);

                    if (nextHour > end) {
                        break;
                    }

                    const slotStart = current.toTimeString().substring(0, 5);
                    const slotEnd = nextHour.toTimeString().substring(0, 5);

                    slots.push({
                        start_time: slotStart,
                        end_time: slotEnd,
                        price: price
                    });

                    current = nextHour;
                }
                return slots;
            }

            function updateInputNames() {
                let slotIndex = 0;
                container.find('.time-slot-row').each(function() {
                    const $row = $(this);
                    // Đổi lại name cho đúng format Laravel mong muốn
                    $row.find('.time-start').attr('name', `time_slots[${slotIndex}][start_time]`);
                    $row.find('.time-end').attr('name', `time_slots[${slotIndex}][end_time]`);
                    $row.find('.time-price').attr('name', `time_slots[${slotIndex}][price]`);
                    slotIndex++;
                });
            }


            $(document).on('click', '#add-time-slot-btn', function(e) {
                e.preventDefault();
                container.append(createTimeSlotRow());
                updateInputNames();
            });

            $(document).on('click', '.remove-slot-btn', function(e) {
                e.preventDefault();
                $(this).closest('.time-slot-row').remove();
                updateInputNames();
            });

            $(document).on('change', '.time-start, .time-end, .time-price', function() {
                const $row = $(this).closest('.time-slot-row');
                const startTime = $row.find('.time-start').val();
                const endTime = $row.find('.time-end').val();
                const price = $row.find('.time-price').val();

                if (startTime && endTime && price) {
                    const slots = splitTimeIntoHourlySlots(startTime, endTime, price);

                    if (slots.length > 1) {
                        $row.remove(); // Xóa hàng hiện tại

                        // Thêm các slot 1 giờ
                        slots.forEach(slot => {
                            const newRow = $(createTimeSlotRow());
                            newRow.find('.time-start').val(slot.start_time);
                            newRow.find('.time-end').val(slot.end_time);
                            newRow.find('.time-price').val(slot.price);
                            container.append(newRow);
                        });

                        updateInputNames();
                    }
                }
            });

            $('form').on('submit', function() {
                updateInputNames(); // Cập nhật tên input lần cuối trước khi gửi
            });

            // Tự động thêm một hàng khi trang tải
            if (container.children().length === 0) {
                $('#add-time-slot-btn').click();
            }
        });
    </script>
@endpush
