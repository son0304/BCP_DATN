@extends('app')

@section('content')
    {{-- Style cho màu sắc và công tắc tùy chỉnh --}}
    <style>
        :root {
            --bs-primary: #348738;
            --bs-primary-rgb: 52, 135, 56;
        }

        .btn-primary {
            --bs-btn-hover-bg: #2d6a2d;
            --bs-btn-hover-border-color: #2d6a2d;
        }

        .accordion-button:not(.collapsed) {
            background-color: #e1f3e2;
            color: #2d6a2d;
        }

        .accordion-button:focus {
            box-shadow: 0 0 0 0.25rem rgba(52, 135, 56, 0.25);
        }

        .form-switch .form-check-input {
            background-color: #dc3545;
            /* Màu đỏ khi tắt */
            border-color: #dc3545;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='-4 -4 8 8'%3e%3ccircle r='3' fill='%23fff'/%3e%3c/svg%3e");
        }

        .form-switch .form-check-input:checked {
            background-color: var(--bs-primary);
            /* Màu xanh khi bật */
            border-color: var(--bs-primary);
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='-4 -4 8 8'%3e%3ccircle r='3' fill='%23fff'/%3e%3c/svg%3e");
        }

        /* --- CSS CHO CÔNG TẮC TÙY CHỈNH --- */
        .custom-switch {
            width: 95px;
            /* Chiều rộng của công tắc */
            font-size: 0.8rem;
            font-weight: 600;
        }

        .custom-switch .form-check-input {
            width: 100%;
            height: 30px;
            position: relative;
            cursor: pointer;
            background-position: left 0.25rem center;
        }

        .custom-switch .form-check-input:checked {
            background-position: right 0.25rem center;
        }

        /* SỬA: Text cho "Bảo trì" (khi không check) */
        .custom-switch .form-check-input::before {
            content: 'Bảo trì';
            position: absolute;
            right: 8px;
            top: 50%;
            transform: translateY(-50%);
            color: white;
            transition: opacity 0.2s ease;
            opacity: 1;
        }

        /* Text cho "Mở" (khi check) */
        .custom-switch .form-check-input::after {
            content: 'Mở';
            position: absolute;
            left: 8px;
            top: 50%;
            transform: translateY(-50%);
            color: white;
            transition: opacity 0.2s ease;
            opacity: 0;
        }

        /* Ẩn/hiện text khi check */
        .custom-switch .form-check-input:checked::before {
            opacity: 0;
        }

        .custom-switch .form-check-input:checked::after {
            opacity: 1;
        }

        /* --- Style cho card thông tin bên trái --- */
        .info-card-decorated .card-body {
            border-left: 4px solid var(--bs-primary);
            background-color: #fdfdfd;
        }

        .info-card-decorated dl dt {
            font-weight: 600;
            color: #495057;
        }
    </style>

    <div class="container-fluid mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h4 mb-0">Chi tiết sân: <strong>{{ $court->name }}</strong></h1>
                <p class="text-muted mb-0">{{ $court->venue->name ?? 'N/A' }}</p>
            </div>
            <div>
                <a href="{{ route('admin.courts.edit', $court) }}" class="btn btn-warning">
                    <i class="fas fa-edit me-1"></i> Chỉnh sửa
                </a>
                <a href="{{ route('admin.courts.index') }}" class="btn btn-secondary">Quay lại</a>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="row">
            {{-- THÔNG TIN CƠ BẢN --}}
            <div class="col-lg-5 mb-4 info-card-decorated">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-white border-0">
                        <h5 class="mb-0">Thông tin cơ bản</h5>
                    </div>
                    <div class="card-body">
                        <dl class="row">
                            <dt class="col-sm-4">Tên sân</dt>
                            <dd class="col-sm-8">{{ $court->name }}</dd>
                            <dt class="col-sm-4">Địa điểm</dt>
                            <dd class="col-sm-8">{{ $court->venue->name ?? 'N/A' }}</dd>
                            <dt class="col-sm-4">Loại hình</dt>
                            <dd class="col-sm-8">{{ $court->venueType->name ?? 'N/A' }}</dd>
                            <dt class="col-sm-4">Loại sân</dt>
                            <dd class="col-sm-8">
                                @if ($court->is_indoor)
                                    <span class="badge bg-info text-dark">Trong nhà</span>
                                @else
                                    <span class="badge bg-warning text-dark">Ngoài trời</span>
                                @endif
                            </dd>
                            <dt class="col-sm-4">Bề mặt</dt>
                            <dd class="col-sm-8">{{ $court->surface ?? 'Chưa cập nhật' }}</dd>
                            <dt class="col-sm-4 text-truncate">Ngày tạo</dt>
                            <dd class="col-sm-8">{{ $court->created_at->format('d/m/Y H:i') }}</dd>
                            <dt class="col-sm-4 text-truncate">Cập nhật</dt>
                            <dd class="col-sm-8">{{ $court->updated_at->format('d/m/Y H:i') }}</dd>
                        </dl>
                    </div>
                </div>
            </div>

            {{-- LỊCH HOẠT ĐỘNG --}}
            <div class="col-lg-7 mb-4">
                <form action="{{ route('admin.courts.updateAvailabilities', $court) }}" method="POST">
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
                                <div class="accordion" id="availabilityAccordion">
                                    @foreach ($availabilities as $date => $dayAvailabilities)
                                        <div class="accordion-item">
                                            <h2 class="accordion-header" id="heading{{ Str::slug($date) }}">
                                                <button class="accordion-button {{ $loop->first ? '' : 'collapsed' }}"
                                                    type="button" data-bs-toggle="collapse"
                                                    data-bs-target="#collapse{{ Str::slug($date) }}">
                                                    <strong>Ngày:
                                                        {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}</strong>
                                                    <span
                                                        class="badge bg-secondary ms-auto me-2">{{ $dayAvailabilities->count() }}
                                                        slots</span>
                                                </button>
                                            </h2>
                                            <div id="collapse{{ Str::slug($date) }}"
                                                class="accordion-collapse collapse {{ $loop->first ? 'show' : '' }}"
                                                data-bs-parent="#availabilityAccordion">
                                                <div class="accordion-body p-0">
                                                    <ul class="list-group list-group-flush">
                                                        @foreach ($dayAvailabilities->sortBy('timeSlot.start_time') as $availability)
                                                            <li class="list-group-item d-flex align-items-center">

                                                                {{-- Tên khung giờ --}}
                                                                <div class="fw-bold me-auto">
                                                                    {{ $availability->timeSlot->label ?? 'N/A' }}
                                                                </div>

                                                                {{-- Container giá + công tắc --}}
                                                                <div class="d-flex align-items-center"
                                                                    style="min-width: 210px;">

                                                                    {{-- Giá hiển thị theo k, không thập phân --}}
                                                                    <span class="text-nowrap text-end pe-4"
                                                                        style="flex-basis: 115px;">
                                                                        {{ floor($availability->price / 1000) }}k
                                                                    </span>

                                                                    {{-- Công tắc trạng thái hoặc badge "Đã đặt" --}}
                                                                    @if ($availability->status === 'booked')
                                                                        <span class="badge bg-danger"
                                                                            style="width: 95px;">Đã đặt</span>
                                                                    @else
                                                                        <div class="form-check form-switch custom-switch">
                                                                            {{-- Gửi "maintenance" nếu công tắc tắt --}}
                                                                            <input type="hidden"
                                                                                name="statuses[{{ $availability->id }}]"
                                                                                value="maintenance">
                                                                            <input class="form-check-input" type="checkbox"
                                                                                role="switch"
                                                                                id="statusSwitch{{ $availability->id }}"
                                                                                name="statuses[{{ $availability->id }}]"
                                                                                value="open"
                                                                                {{ $availability->status === 'open' ? 'checked' : '' }}>
                                                                        </div>
                                                                    @endif

                                                                </div>
                                                            </li>
                                                        @endforeach
                                                    </ul>

                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center text-muted p-4">
                                    <i class="fas fa-calendar-times fa-3x mb-3"></i>
                                    <p>Chưa có lịch hoạt động nào được thiết lập.</p>
                                    <a href="{{ route('admin.courts.edit', $court) }}" class="btn btn-primary mt-2">Thiết
                                        lập ngay</a>
                                </div>
                            @endif
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
@endpush
