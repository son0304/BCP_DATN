@extends('app')

@section('content')
    <div class="container-fluid py-4">
        {{-- Header --}}
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

        {{-- Form --}}
        <form action="{{ route('brand.store') }}" method="POST">
            @csrf
            <div class="row">
                {{-- Cột trái --}}
                <div class="col-lg-8">
                    {{-- Thông tin cơ bản --}}
                    <div class="card shadow-sm mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Thông tin cơ bản</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Tên thương hiệu (sân)</label>
                                <input type="text" name="name" class="form-control" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Chủ sở hữu</label>
                                <select name="owner_id" class="form-select" required>
                                    <option value="">-- Chọn chủ sở hữu --</option>
                                    @foreach ($owners as $owner)
                                        <option value="{{ $owner->id }}">{{ $owner->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <hr class="my-4">
                            <h6 class="fw-bold">Thông tin địa chỉ</h6>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Tỉnh/Thành</label>
                                    <select name="province_id" id="province_id" class="form-select" required>
                                        <option value="">-- Chọn Tỉnh/Thành --</option>
                                        @foreach ($provinces as $province)
                                            <option value="{{ $province->id }}">{{ $province->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Quận/Huyện</label>
                                    <select name="district_id" id="district_id" class="form-select" required>
                                        <option value="">-- Chọn Quận/Huyện --</option>
                                        @foreach ($districts as $district)
                                            <option value="{{ $district->id }}">{{ $district->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Địa chỉ chi tiết</label>
                                <input type="text" name="address_detail" class="form-control" required>
                            </div>
                        </div>
                    </div>

                    {{-- 💡 DANH SÁCH SÂN --}}
                    <div class="card shadow-sm mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">Danh sách sân</h5>
                            <button type="button" id="add-court-btn" class="btn btn-sm btn-success">
                                <i class="fas fa-plus"></i> Thêm sân
                            </button>
                        </div>
                        <div class="card-body" id="court-list">
                            {{-- Các sân sẽ được thêm động tại đây --}}
                        </div>
                    </div>
                </div>

                {{-- Cột phải --}}
                <div class="col-lg-4">
                    <div class="card shadow-sm mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Thông tin bổ sung</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Số điện thoại</label>
                                <input type="tel" name="phone" class="form-control" placeholder="09xxxxxxxx">
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Giờ mở cửa</label>
                                    <input type="time" name="start_time" class="form-control" value="06:00">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Giờ đóng cửa</label>
                                    <input type="time" name="end_time" class="form-control" value="22:00">
                                </div>
                            </div>

                            <label class="form-label fw-bold d-block">Loại hình sân</label>
                            @foreach ($venue_types as $type)
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input venue-type-checkbox" type="checkbox" name="venue_types[]"
                                        value="{{ $type->id }}">
                                    <label class="form-check-label">{{ $type->name }}</label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-center mt-4">
                <input type="hidden" name="is_active" value="0">
                <button type="submit" class="btn btn-primary px-4 py-2">
                    <i class="fas fa-save me-2"></i> Lưu và tạo mới
                </button>
            </div>
        </form>
    </div>

    {{-- ✅ JS: Thêm sân + khung giờ + tự động cập nhật loại sân --}}
    {{-- ✅ JS: SỬ DỤNG SELECT BOX ĐỂ CHỌN GIỜ (KHẮC PHỤC LỖI SA/CH) --}}
    {{-- ✅ JS: SỬ DỤNG SELECT BOX ĐỂ CHỌN GIỜ (KHẮC PHỤC LỖI SA/CH) --}}
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            let courtIndex = 0;
            const courtList = document.getElementById('court-list');
            const addCourtBtn = document.getElementById('add-court-btn');

            // ✅ 1. HÀM TẠO DANH SÁCH GIỜ (05:00 đến 24:00) CHO SELECT BOX
            function generateTimeOptions(selectedValue = "") {
                let options = '<option value="">--:--</option>';
                // Tạo giờ từ 05:00 đến 24:00
                for (let i = 5; i <= 24; i++) {
                    let hour = i < 10 ? '0' + i : i;
                    let timeVal = `${hour}:00`;
                    let isSelected = selectedValue === timeVal ? 'selected' : '';
                    options += `<option value="${timeVal}" ${isSelected}>${timeVal}</option>`;
                }
                return options;
            }

            // 👉 Lấy danh sách loại hình sân
            function getSelectedVenueTypes() {
                const checkedBoxes = document.querySelectorAll('.venue-type-checkbox:checked');
                return Array.from(checkedBoxes).map(cb => ({
                    id: cb.value,
                    name: cb.nextElementSibling.textContent.trim()
                }));
            }

            function renderVenueTypeOptions(selectedTypes) {
                if (selectedTypes.length === 0) return `<option value="">-- Chưa chọn loại hình --</option>`;
                return selectedTypes.map(type => `<option value="${type.id}">${type.name}</option>`).join('');
            }

            function updateTimeSlotNames() {
                document.querySelectorAll('.court-item').forEach((courtItem, courtIdx) => {
                    const tbody = courtItem.querySelector('tbody');
                    const rows = tbody.querySelectorAll('tr');

                    rows.forEach((row, slotIdx) => {
                        const startInput = row.querySelector('.time-start');
                        const endInput = row.querySelector('.time-end');
                        const priceInput = row.querySelector('.time-price');

                        if (startInput) startInput.name =
                            `courts[${courtIdx}][time_slots][${slotIdx}][start_time]`;
                        if (endInput) endInput.name =
                            `courts[${courtIdx}][time_slots][${slotIdx}][end_time]`;
                        if (priceInput) priceInput.name =
                            `courts[${courtIdx}][time_slots][${slotIdx}][price]`;
                    });
                });
            }

            // ✅ 2. BẤM NÚT "THÊM SÂN" -> SINH RA GIAO DIỆN CÓ SELECT BOX
            addCourtBtn.addEventListener('click', () => {
                const options = renderVenueTypeOptions(getSelectedVenueTypes());
                const timeOptions = generateTimeOptions(); // Sinh HTML các option giờ

                const newCourt = `
            <div class="border rounded p-3 mb-3 court-item bg-white shadow-sm">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="mb-0 fw-bold text-primary">Sân #${courtIndex + 1}</h6>
                    <button type="button" class="btn btn-sm btn-outline-danger remove-court"><i class="fas fa-times"></i> Xóa sân</button>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label small fw-bold">Tên sân</label>
                        <input type="text" name="courts[${courtIndex}][name]" class="form-control" required placeholder="Ví dụ: Sân 1">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label small fw-bold">Loại sân</label>
                        <select name="courts[${courtIndex}][venue_type_id]" class="form-select court-type-select" required>${options}</select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label small fw-bold">Mặt sân</label>
                        <input type="text" name="courts[${courtIndex}][surface]" class="form-control">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label small fw-bold">Trong nhà</label>
                        <select name="courts[${courtIndex}][is_indoor]" class="form-select">
                            <option value="0">Ngoài trời</option>
                            <option value="1">Trong nhà</option>
                        </select>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-3 mb-2 border-bottom pb-2">
                    <span class="fw-bold small text-uppercase text-muted">Bảng giá</span>
                    <button type="button" class="btn btn-sm btn-success add-time-slot"><i class="fas fa-plus-circle"></i> Thêm giờ</button>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-sm align-middle mb-0">
                        <thead class="bg-light text-center">
                            <tr>
                                <th style="width: 35%">Từ giờ</th>
                                <th style="width: 35%">Đến giờ</th>
                                <th>Giá (VNĐ)</th>
                                <th style="width: 50px"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <select class="form-select form-select-sm time-start" required>
                                        ${timeOptions}
                                    </select>
                                </td>
                                <td>
                                    <select class="form-select form-select-sm time-end" required>
                                        ${timeOptions}
                                    </select>
                                </td>
                                <td><input type="number" class="form-control form-control-sm time-price" required placeholder="Nhập giá"></td>
                                <td class="text-center"><button type="button" class="btn btn-sm btn-light text-danger remove-slot"><i class="fas fa-trash"></i></button></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>`;

                courtList.insertAdjacentHTML('beforeend', newCourt);
                courtIndex++;
                updateTimeSlotNames();
            });

            // 👉 Tự động cập nhật dropdown loại sân
            document.querySelectorAll('.venue-type-checkbox').forEach(cb => {
                cb.addEventListener('change', () => {
                    const selectedTypes = getSelectedVenueTypes();
                    const options = renderVenueTypeOptions(selectedTypes);
                    document.querySelectorAll('.court-type-select').forEach(select => {
                        const val = select.value;
                        select.innerHTML = options;
                        if (selectedTypes.some(t => t.id === val)) select.value = val;
                    });
                });
            });

            // 👉 Xử lý sự kiện CLICK (Thêm dòng / Xóa dòng)
            document.addEventListener('click', e => {
                // Nút thêm giờ thủ công
                if (e.target.closest('.add-time-slot')) {
                    const timeOptions = generateTimeOptions();
                    e.target.closest('.court-item').querySelector('tbody').insertAdjacentHTML('beforeend', `
                    <tr>
                        <td>
                            <select class="form-select form-select-sm time-start" required>${timeOptions}</select>
                        </td>
                        <td>
                            <select class="form-select form-select-sm time-end" required>${timeOptions}</select>
                        </td>
                        <td><input type="number" class="form-control form-control-sm time-price" required></td>
                        <td class="text-center"><button type="button" class="btn btn-sm btn-light text-danger remove-slot"><i class="fas fa-trash"></i></button></td>
                    </tr>
                `);
                    updateTimeSlotNames();
                }
                if (e.target.closest('.remove-slot')) {
                    e.target.closest('tr').remove();
                    updateTimeSlotNames();
                }
                if (e.target.closest('.remove-court')) {
                    if (confirm('Xóa sân này?')) e.target.closest('.court-item').remove();
                }
            });

            // ✅ 3. LOGIC TỰ ĐỘNG CHIA GIỜ (Dựa trên Select Box)
            document.addEventListener('change', e => {
                if (e.target.classList.contains('time-start') || e.target.classList.contains('time-end') ||
                    e.target.classList.contains('time-price')) {
                    const row = e.target.closest('tr');

                    const startVal = row.querySelector('.time-start').value;
                    const endVal = row.querySelector('.time-end').value;
                    const priceVal = row.querySelector('.time-price').value;

                    // Chỉ tính khi chọn đủ 3 ô
                    if (startVal && endVal && priceVal) {
                        let startHour = parseInt(startVal.split(':')[0]);
                        let endHour = parseInt(endVal.split(':')[0]);

                        // Xử lý qua đêm: 23:00 -> 01:00 (tức là 25h)
                        if (endHour <= startHour) {
                            endHour += 24;
                        }

                        const diff = endHour - startHour;

                        // Nếu khoảng cách > 1 tiếng thì tách dòng
                        if (diff > 1) {
                            const tbody = row.closest('tbody');
                            row.remove(); // Xóa dòng hiện tại

                            for (let i = 0; i < diff; i++) {
                                let s = startHour + i;
                                let e = startHour + i + 1;

                                // Chuyển lại về dạng 24h (nếu > 24 thì trừ 24)
                                let displayS = s >= 24 ? s - 24 : s;
                                let displayE = e >= 24 ? e - 24 : e;

                                let strS = (displayS < 10 ? '0' + displayS : displayS) + ':00';
                                let strE = (displayE < 10 ? '0' + displayE : displayE) + ':00';

                                let optionsS = generateTimeOptions(strS);
                                let optionsE = generateTimeOptions(strE);

                                tbody.insertAdjacentHTML('beforeend', `
                                <tr>
                                    <td>
                                        <select class="form-select form-select-sm time-start" required>${optionsS}</select>
                                    </td>
                                    <td>
                                        <select class="form-select form-select-sm time-end" required>${optionsE}</select>
                                    </td>
                                    <td><input type="number" class="form-control form-control-sm time-price" value="${priceVal}" required></td>
                                    <td class="text-center"><button type="button" class="btn btn-sm btn-light text-danger remove-slot"><i class="fas fa-trash"></i></button></td>
                                </tr>
                            `);
                            }
                            updateTimeSlotNames();
                        }
                    }
                }
            });

            document.querySelector('form').addEventListener('submit', () => updateTimeSlotNames());
        });
    </script>
@endsection
