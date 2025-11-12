@extends('app')

@section('content')
<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h4 mb-0">Chi tiết sân: <strong>{{ $court->name }}</strong></h1>
            <p class="text-muted mb-0">{{ $court->venue->name ?? 'N/A' }}</p>
        </div>
        <div>
            <a href="{{ route('courts.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> Quay lại
            </a>
            <a href="{{ route('owner.venues.courts.edit', ['venue' => $venue->id, 'court' => $court->id]) }}"
                class="btn btn-warning me-1">
                <i class="fas fa-edit me-1"></i> Chỉnh sửa
            </a>
        </div>
    </div>

    <div class="row g-4">

        {{-- THÔNG TIN CƠ BẢN --}}
        <div class="col-lg-6">
            <div class="card shadow-sm border-0 h-100 info-card">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0">Thông tin cơ bản</h5>
                </div>
                <div class="card-body">

                    <style>
                        .info-card .label {
                            font-size: 0.85rem;
                            color: #6c757d;
                        }

                        .info-card .value {
                            font-size: 1rem;
                            font-weight: 600;
                        }

                        .info-card .icon-badge {
                            width: 36px;
                            height: 36px;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            font-size: 1rem;
                            border-radius: 50%;
                        }

                        .info-card .info-row {
                            margin-bottom: 12px;
                            display: flex;
                            align-items: center;
                        }

                        .info-card .info-row .text-container {
                            flex: 1;
                        }
                    </style>

                    <!-- Tên sân -->
                    <div class="info-row">
                        <span class="badge bg-success text-white icon-badge me-3">
                            <i class="fas fa-futbol"></i>
                        </span>
                        <div class="text-container">
                            <div class="label">Tên sân</div>
                            <div class="value">{{ $court->name ?? 'N/A' }}</div>
                        </div>
                    </div>

                    <!-- Địa điểm -->
                    <div class="info-row">
                        <span class="badge bg-primary text-white icon-badge me-3">
                            <i class="fas fa-map-marker-alt"></i>
                        </span>
                        <div class="text-container">
                            <div class="label">Địa điểm</div>
                            <div class="value">{{ $court->venue->name ?? 'N/A' }}</div>
                        </div>
                    </div>

                    <!-- Loại hình -->
                    <div class="info-row">
                        <span class="badge bg-info text-white icon-badge me-3">
                            <i class="fas fa-layer-group"></i>
                        </span>
                        <div class="text-container">
                            <div class="label">Loại hình</div>
                            <div class="value">{{ $court->venueType->name ?? 'N/A' }}</div>
                        </div>
                    </div>

                    <!-- Loại sân -->
                    <div class="info-row">
                        <span class="badge bg-warning text-white icon-badge me-3">
                            <i class="fas fa-building"></i>
                        </span>
                        <div class="text-container">
                            <div class="label">Loại sân</div>
                            <div class="value">
                                <span class="badge {{ $court->is_indoor ? 'bg-info' : 'bg-warning' }}">
                                    {{ $court->is_indoor ? 'Trong nhà' : 'Ngoài trời' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Bề mặt -->
                    <div class="info-row">
                        <span class="badge bg-secondary text-white icon-badge me-3">
                            <i class="fas fa-th"></i> <!-- icon dạng lưới -->
                        </span>
                        <div class="text-container">
                            <div class="label">Bề mặt</div>
                            <div class="value">{{ $court->surface ?? 'Chưa cập nhật' }}</div>
                        </div>
                    </div>

                    <hr>

                    <!-- Thời gian tạo/cập nhật -->
                    <div class="small text-muted">
                        Tạo lúc: {{ $court->created_at?->format('d/m/Y H:i') ?? 'N/A' }} <br>
                        Cập nhật: {{ $court->updated_at?->format('d/m/Y H:i') ?? 'N/A' }}
                    </div>

                </div>
            </div>
        </div>

        {{-- LỊCH HOẠT ĐỘNG --}}
        <div class="col-lg-6">
            <form action="{{ route('owner.courts.updateAvailabilities', $court) }}" method="POST">
                @csrf

                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Lịch hoạt động (30 ngày tới)</h5>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Lưu thay đổi
                        </button>
                    </div>

                    <div class="card-body">

                        @if ($availabilities->isNotEmpty())
                        {{-- LỊCH 30 NGÀY DẠNG GRID --}}
                        <div class="calendar-grid row row-cols-7 g-2 text-center mb-4">
                            @php
                            $localTimezone = 'Asia/Ho_Chi_Minh';
                            @endphp
                            @foreach ($availabilities as $date => $dayAvailabilities)
                            @php
                            $carbonDate = \Carbon\Carbon::parse($date, $localTimezone);
                            if ($carbonDate->isPast() && !$carbonDate->isToday()) {
                            continue;
                            }
                            @endphp

                            <div class="col">
                                <div class="calendar-day p-2 border rounded shadow-sm calendar-select-day"
                                    data-date="{{ $date }}" style="cursor:pointer; transition:0.2s;">
                                    <strong>{{ $carbonDate->format('d') }}</strong>
                                    <div class="small text-muted">{{ $carbonDate->isoFormat('ddd') }}</div>
                                </div>
                            </div>
                            @endforeach
                        </div>

                        {{-- KHU VỰC LOAD KHUNG GIỜ --}}
                        <div id="scheduleDetail" class="mt-4">
                            <p class="text-muted text-center">Hãy chọn ngày để xem chi tiết khung giờ.</p>
                        </div>

                        {{-- TEMPLATES HIDDEN --}}
                        @foreach ($availabilities as $date => $dayAvailabilities)
                        @php
                        $carbonDate = \Carbon\Carbon::parse($date, $localTimezone);
                        if ($carbonDate->isPast() && !$carbonDate->isToday()) {
                        continue;
                        }
                        @endphp

                        <template id="tpl-{{ $date }}">
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="mb-0">Khung giờ ngày
                                        <strong>{{ $carbonDate->format('d/m/Y') }}</strong>
                                    </h6>
                                    <div class="form-check form-switch mb-0" style="min-width: 120px;">
                                        <input class="form-check-input day-toggle-all" type="checkbox"
                                            data-date="{{ $date }}">
                                        <label class="form-check-label small">Đóng/Mở</label>
                                    </div>
                                </div>

                                {{-- Container scroll --}}
                                <div class="time-slot-list border rounded p-2"
                                    style="max-height: 300px; overflow-y: auto;">
                                    <ul class="list-group">
                                        @foreach ($dayAvailabilities->sortBy('timeSlot.start_time') as $availability)
                                        <li class="list-group-item py-2">
                                            <div class="d-flex align-items-center">
                                                <span
                                                    class="fw-semibold">{{ $availability->timeSlot->label }}</span>
                                                <div class="d-flex align-items-center ms-auto"
                                                    style="gap: 40px;">
                                                    <span class="fw-semibold text-success text-end"
                                                        style="min-width: 95px;">
                                                        {{ number_format($availability->price, 0, ',', '.') }}₫
                                                    </span>

                                                    @if ($availability->status === 'booked')
                                                    <span class="badge bg-danger"
                                                        style="min-width: 90px;">Đã đặt</span>
                                                    @else
                                                    <div class="form-check form-switch ms-1"
                                                        style="min-width: 90px;">
                                                        <input type="hidden"
                                                            name="statuses[{{ $availability->id }}]"
                                                            value="maintenance">
                                                        <input class="form-check-input" type="checkbox"
                                                            name="statuses[{{ $availability->id }}]"
                                                            value="open" @checked($availability->status === 'open')>
                                                    </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </template>
                        @endforeach
                        @else
                        <p class="text-center text-muted py-4">Chưa có lịch hoạt động.</p>
                        @endif

                    </div>
                </div>
            </form>
        </div>

        {{-- SCRIPT --}}
        <script>
            let activeDate = null;

            document.querySelectorAll(".calendar-select-day").forEach(day => {
                day.addEventListener("click", () => {
                    let date = day.dataset.date;
                    let tpl = document.querySelector(`#tpl-${date}`);
                    const container = document.getElementById("scheduleDetail");

                    if (activeDate === date) {
                        container.innerHTML =
                            `<p class="text-muted text-center">Hãy chọn ngày để xem chi tiết khung giờ.</p>`;
                        activeDate = null;
                        document.querySelectorAll(".calendar-select-day").forEach(d =>
                            d.classList.remove("bg-primary", "text-white")
                        );
                        return;
                    }

                    activeDate = date;
                    document.querySelectorAll(".calendar-select-day").forEach(d =>
                        d.classList.remove("bg-primary", "text-white")
                    );
                    day.classList.add("bg-primary", "text-white");

                    container.innerHTML = tpl ? tpl.innerHTML :
                        "<p class='text-muted text-center'>Không có dữ liệu.</p>";
                    updateMasterToggle();
                });
            });


            // Hàm cập nhật trạng thái master toggle dựa trên các toggle con
            function updateMasterToggle() {
                const masterToggle = document.querySelector("#scheduleDetail .day-toggle-all");
                if (!masterToggle) return;

                const checkboxes = document.querySelectorAll(
                    `#scheduleDetail input.form-check-input[type="checkbox"]:not(.day-toggle-all)`);
                if (checkboxes.length === 0) {
                    masterToggle.checked = false;
                    masterToggle.disabled = true;
                    return;
                }

                const allChecked = Array.from(checkboxes).every(cb => cb.checked);
                masterToggle.checked = allChecked;
                masterToggle.disabled = false;
            }

            // Khi click bật/tắt tổng → cập nhật tất cả khung giờ
            document.addEventListener("change", function(e) {
                if (e.target.classList.contains("day-toggle-all")) {
                    let parentChecked = e.target.checked;
                    document.querySelectorAll(
                            `#scheduleDetail input.form-check-input[type="checkbox"]:not(.day-toggle-all)`)
                        .forEach(cb => cb.checked = parentChecked);
                }

                // Khi bật/tắt từng khung giờ → làm mới trạng thái tổng
                if (e.target.type === "checkbox" && !e.target.classList.contains("day-toggle-all")) {
                    updateMasterToggle();
                }
            });
        </script>



    </div>
</div>

{{-- SCRIPT MỚI ĐỂ ĐIỀU KHIỂN SWITCH --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {

        /**
         * Cập nhật trạng thái của Master Switch dựa trên các switch con
         */
        function updateMasterSwitchStatus(dayIndex) {
            const childSwitches = document.querySelectorAll(
                `.form-check-input[data-day-index="${dayIndex}"]:not(.day-master-switch)`);
            const masterSwitch = document.querySelector(`.day-master-switch[data-day-index="${dayIndex}"]`);

            if (!masterSwitch) return;

            if (childSwitches.length > 0) {
                // Kiểm tra xem TẤT CẢ switch con có đang được check hay không
                const allChecked = Array.from(childSwitches).every(sw => sw.checked);
                masterSwitch.checked = allChecked;
                masterSwitch.disabled = false;
            } else {
                // Nếu không có switch con nào (đã lọc hết), tắt và vô hiệu hóa master
                masterSwitch.checked = false;
                masterSwitch.disabled = true;
            }
        }

        // 1. Lắng nghe sự kiện 'change' trên CÁC MASTER SWITCH
        const masterSwitches = document.querySelectorAll('.day-master-switch');
        masterSwitches.forEach(masterSwitch => {
            masterSwitch.addEventListener('change', function() {
                const dayIndex = this.dataset.dayIndex;
                const isChecked = this.checked;
                // Tìm tất cả switch con CÙNG NGÀY
                const childSwitches = document.querySelectorAll(
                    `.form-check-input[data-day-index="${dayIndex}"]:not(.day-master-switch)`
                );

                // Bật/tắt tất cả switch con
                childSwitches.forEach(childSwitch => {
                    childSwitch.checked = isChecked;
                });
            });
        });

        // 2. Lắng nghe sự kiện 'change' trên CÁC SWITCH CON (để cập nhật lại master)
        const allChildSwitches = document.querySelectorAll(
            '.form-check-input:not(.day-master-switch)[data-day-index]');
        allChildSwitches.forEach(childSwitch => {
            childSwitch.addEventListener('change', function() {
                const dayIndex = this.dataset.dayIndex;
                // Khi 1 switch con thay đổi, kiểm tra lại master
                updateMasterSwitchStatus(dayIndex);
            });
        });

        // 3. Khởi tạo trạng thái Master switches khi tải trang
        masterSwitches.forEach(masterSwitch => {
            updateMasterSwitchStatus(masterSwitch.dataset.dayIndex);
        });

    });
</script>

@endsection