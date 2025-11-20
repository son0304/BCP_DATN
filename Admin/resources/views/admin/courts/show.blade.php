@extends('app')

@section('content')

<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h4 mb-0">Chi tiết sân: <strong>{{ $court->name }}</strong></h1>
            <p class="text-muted mb-0">{{ $court->venue->name ?? 'N/A' }}</p>
        </div>
        <div>
            <a href="{{ route('admin.venues.show', $venue->id) }}" class="btn btn-secondary">Quay lại</a>
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
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0">Lịch hoạt động (30 ngày tới)</h5>
                </div>

                <div class="card-body">
                    @if ($availabilities->isNotEmpty())
                    <div class="calendar-grid row row-cols-7 g-2 text-center mb-4">
                        @php $localTimezone = 'Asia/Ho_Chi_Minh'; @endphp
                        @foreach ($availabilities as $date => $dayAvailabilities)
                        @php
                        $carbonDate = \Carbon\Carbon::parse($date, $localTimezone);
                        if ($carbonDate->isPast() && !$carbonDate->isToday()) {
                        continue;
                        }
                        @endphp
                        <div class="col">
                            <div class="calendar-day p-2 border rounded shadow-sm"
                                data-date="{{ $date }}">
                                <strong>{{ $carbonDate->format('d') }}</strong>
                                <div class="small text-muted">{{ $carbonDate->isoFormat('ddd') }}</div>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    {{-- Chi tiết khung giờ --}}
                    <div id="scheduleDetail" class="mt-4">
                        <p class="text-muted text-center">Hãy chọn ngày để xem chi tiết khung giờ.</p>
                    </div>

                    @foreach ($availabilities as $date => $dayAvailabilities)
                    @php
                    $carbonDate = \Carbon\Carbon::parse($date, $localTimezone);
                    if ($carbonDate->isPast() && !$carbonDate->isToday()) {
                    continue;
                    }
                    @endphp
                    <template id="tpl-{{ $date }}">
                        <div class="mb-3">
                            <h6>Khung giờ ngày <strong>{{ $carbonDate->format('d/m/Y') }}</strong></h6>
                            <ul class="list-group">
                                @foreach ($dayAvailabilities->sortBy('timeSlot.start_time') as $availability)
                                <li
                                    class="list-group-item d-flex justify-content-between align-items-center">
                                    <span>{{ $availability->timeSlot->label }}</span>
                                    <span>
                                        {{ number_format($availability->price, 0, ',', '.') }}₫
                                        @if ($availability->status === 'booked')
                                        <span class="badge bg-danger ms-2">Đã đặt</span>
                                        @elseif ($availability->status === 'maintenance')
                                        <span class="badge bg-secondary ms-2">Bảo trì</span>
                                        @else
                                        <span class="badge bg-success ms-2">Mở</span>
                                        @endif
                                    </span>
                                </li>
                                @endforeach
                            </ul>
                        </div>
                    </template>
                    @endforeach
                    @else
                    <p class="text-center text-muted py-4">Chưa có lịch hoạt động.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    let activeDate = null;

    document.querySelectorAll(".calendar-day").forEach(day => {
        day.addEventListener("click", () => {
            let date = day.dataset.date;
            let tpl = document.querySelector(`#tpl-${date}`);
            const container = document.getElementById("scheduleDetail");

            if (activeDate === date) {
                container.innerHTML =
                    `<p class="text-muted text-center">Hãy chọn ngày để xem chi tiết khung giờ.</p>`;
                activeDate = null;
                document.querySelectorAll(".calendar-day").forEach(d =>
                    d.classList.remove("bg-primary", "text-white")
                );
                return;
            }

            activeDate = date;
            document.querySelectorAll(".calendar-day").forEach(d =>
                d.classList.remove("bg-primary", "text-white")
            );
            day.classList.add("bg-primary", "text-white");

            container.innerHTML = tpl ? tpl.innerHTML :
                "<p class='text-muted text-center'>Không có dữ liệu.</p>";
        });
    });
</script>
@endsection