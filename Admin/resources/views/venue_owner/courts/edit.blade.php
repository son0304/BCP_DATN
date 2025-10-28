@extends('app')

@section('content')
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

        .form-control:focus,
        .form-select:focus {
            border-color: #84c887;
            box-shadow: 0 0 0 0.25rem rgba(52, 135, 56, 0.25);
        }
    </style>

    <div class="container-fluid mt-4">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white border-0 py-3">
                <h1 class="h4 mb-0">Chỉnh sửa sân: <strong>{{ $court->name }}</strong> - <strong>Thương hiệu: {{ $venue->name }}</strong></h1>
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

                <form action="{{ route('venue.courts.update', ['venue' => $court->venue, 'court' => $court]) }}"
                    method="POST"> @csrf
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
                          
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="venue_type_id" class="form-label">Loại hình <span
                                        class="text-danger">*</span></label>
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
                                <label for="is_indoor" class="form-label">Loại sân <span
                                        class="text-danger">*</span></label>
                                <select class="form-select" id="is_indoor" name="is_indoor" required>
                                    <option value="1"
                                        {{ old('is_indoor', $court->is_indoor) == 1 ? 'selected' : '' }}>Trong nhà</option>
                                    <option value="0"
                                        {{ old('is_indoor', $court->is_indoor) == 0 ? 'selected' : '' }}>Ngoài trời
                                    </option>
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
                            Thay đổi tại đây sẽ xóa và tạo lại lịch cho các suất <strong>chưa được đặt</strong> trong tương
                            lai.
                        </p>

                        <div id="time-slots-container" class="p-3 bg-light rounded border">
                            @foreach ($currentPricesDetailed as $i => $item)
                                <div class="row align-items-center mb-2 time-slot-row p-2 bg-white rounded border">
                                    <div class="col-md-3">
                                        <input type="time" class="form-control form-control-sm"
                                            name="time_slots[{{ $i }}][start_time]"
                                            value="{{ \Carbon\Carbon::parse($item['start_time'])->format('H:i') }}"
                                            required>
                                    </div>
                                    <div class="col-md-3">
                                        <input type="time" class="form-control form-control-sm"
                                            name="time_slots[{{ $i }}][end_time]"
                                            value="{{ \Carbon\Carbon::parse($item['end_time'])->format('H:i') }}" required>
                                    </div>
                                    <div class="col-md-4">
                                        <input type="number" class="form-control form-control-sm"
                                            name="time_slots[{{ $i }}][price]" value="{{ $item['price'] }}"
                                            min="0" step="1000" required>
                                    </div>
                                    <div class="col-md-2 d-flex align-items-center justify-content-end">
                                        <button type="button" class="btn btn-sm btn-outline-danger remove-slot-btn">
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
                        <a href="{{ route('courts.index') }}" class="btn btn-secondary">Hủy bỏ</a>
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
            const container = document.getElementById('time-slots-container');

            function splitTimeIntoHourlySlots(startTime, endTime, price) {
                const slots = [];
                const start = new Date(`2000-01-01T${startTime}`);
                const end = new Date(`2000-01-01T${endTime}`);

                let current = new Date(start);
                while (current < end) {
                    const next = new Date(current);
                    next.setHours(next.getHours() + 1);
                    if (next > end) break;

                    slots.push({
                        start_time: current.toTimeString().slice(0, 5),
                        end_time: next.toTimeString().slice(0, 5),
                        price: price
                    });

                    current = next;
                }
                return slots;
            }

            function regenerateIndex() {
                container.querySelectorAll('.time-slot-row').forEach((row, i) => {
                    row.querySelectorAll('input').forEach(input => {
                        input.name = input.name.replace(/time_slots\[\d+]/, `time_slots[${i}]`);
                    });
                });
            }

            function addRow(start = "", end = "", price = "") {
                const index = container.querySelectorAll('.time-slot-row').length;

                const row = `
        <div class="row align-items-center mb-2 time-slot-row p-2 bg-white rounded border">
            <div class="col-md-3">
                <input type="time" class="form-control form-control-sm start" name="time_slots[${index}][start_time]" value="${start}" required>
            </div>
            <div class="col-md-3">
                <input type="time" class="form-control form-control-sm end" name="time_slots[${index}][end_time]" value="${end}" required>
            </div>
            <div class="col-md-4">
                <input type="number" class="form-control form-control-sm price" name="time_slots[${index}][price]" value="${price}" min="0" step="1000" required>
            </div>
            <div class="col-md-2 text-end">
                <button type="button" class="btn btn-sm btn-outline-danger remove-slot-btn">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>`;

                container.insertAdjacentHTML('beforeend', row);
            }

            document.getElementById('add-time-slot-btn').addEventListener('click', function() {
                addRow();
            });

            document.addEventListener('click', function(e) {
                if (e.target.closest('.remove-slot-btn')) {
                    e.target.closest('.time-slot-row').remove();
                    regenerateIndex();
                }
            });

            document.addEventListener('change', function(e) {
                const row = e.target.closest('.time-slot-row');
                if (!row) return;

                const start = row.querySelector('.start').value;
                const end = row.querySelector('.end').value;
                const price = row.querySelector('.price').value;

                if (start && end && price) {
                    const slots = splitTimeIntoHourlySlots(start, end, price);

                    if (slots.length > 1) {
                        row.remove();
                        slots.forEach(s => addRow(s.start_time, s.end_time, s.price));
                        regenerateIndex();
                    }
                }
            });

        });
    </script>
@endpush
