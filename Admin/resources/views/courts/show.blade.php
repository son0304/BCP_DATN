@extends('app')

@section('content')
{{-- Style cho màu sắc, công tắc, và bảng tùy chỉnh --}}
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
        border-color: #dc3545;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='-4 -4 8 8'%3e%3ccircle r='3' fill='%23fff'/%3e%3c/svg%3e");
    }

    .form-switch .form-check-input:checked {
        background-color: var(--bs-primary);
        border-color: var(--bs-primary);
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='-4 -4 8 8'%3e%3ccircle r='3' fill='%23fff'/%3e%3c/svg%3e");
    }

    .custom-switch {
        width: 70px;
        font-size: 0.7rem;
        font-weight: 600;
    }

    .form-switch .form-check-input {
        width: 100%;
        height: 22px;
        position: relative;
        cursor: pointer;
        background-position: left 0.15rem center;
    }

    .form-switch .form-check-input:checked {
        background-position: right 0.15rem center;
    }

    .custom-switch .form-check-input::before {
        content: 'Bảo trì';
        position: absolute;
        right: 5px;
        top: 50%;
        transform: translateY(-50%);
        color: white;
        transition: opacity 0.2s ease;
        opacity: 1;
        font-size: 0.65rem;
    }

    .custom-switch .form-check-input::after {
        content: 'Mở';
        position: absolute;
        left: 5px;
        top: 50%;
        transform: translateY(-50%);
        color: white;
        transition: opacity 0.2s ease;
        opacity: 0;
        font-size: 0.65rem;
    }

    .custom-switch .form-check-input:checked::before {
        opacity: 0;
    }

    .custom-switch .form-check-input:checked::after {
        opacity: 1;
    }

    .info-card-decorated .card-body {
        border-left: 4px solid var(--bs-primary);
        background-color: #fdfdfd;
    }

    /* Style cho bảng mới */
    .info-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0 8px;
        /* Khoảng cách giữa các hàng */
    }

    .info-table th,
    .info-table td {
        padding: 10px 15px;
        text-align: left;
        background-color: #fff;
        border: 1px solid #e9ecef;
        border-radius: 8px;
        /* Bo góc cho từng ô */
    }

    .info-table th {
        width: 35%;
        font-weight: 600;
        color: #495057;
        background-color: #f8f9fa;
        /* Màu nền nhạt cho tiêu đề */
    }

    .info-table td {
        color: #212529;
        word-break: break-word;
        /* Ngắt từ nếu nội dung dài */
    }

    .info-table .badge {
        padding: 4px 8px;
        font-size: 0.8rem;
    }

    /* Responsive */
    @media (max-width: 768px) {

        .info-table th,
        .info-table td {
            padding: 8px 10px;
            font-size: 0.9rem;
        }

        .info-table th {
            width: 40%;
        }
    }

    /* Style cho phân trang */
    .pagination {
        margin: 0;
    }

    .page-item.active .page-link {
        background-color: var(--bs-primary);
        border-color: var(--bs-primary);
        color: #fff;
    }

    .page-item.disabled .page-link {
        color: #6c757d;
        background-color: #e9ecef;
        cursor: not-allowed;
    }

    .page-link {
        font-size: 14px;
    }

    .list-group-flush {
        width: 290px;
    }

    /* Style cho container giá + công tắc */
    .d-flex.align-items-center {
        justify-content: flex-end;
        /* Dịch toàn bộ container sang phải */
    }

    .list-group-item .d-flex.align-items-center {
        min-width: 150px;
        /* Tăng chiều rộng để tạo không gian */
    }

    .list-group-item .text-nowrap {
        flex-basis: 50px;
        /* Giảm không gian cho giá để công tắc dịch sang phải */
        text-align: right;
        font-size: 0.9rem;
        padding-right: 2rem !important;
        padding-top: 7px;
        color: #c11404ff;
        font-weight: bold;
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
        <div class="col-lg-8 mb-2 info-card-decorated">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0">Thông tin cơ bản</h5>
                </div>
                <div class="card-body">
                    <table class="info-table">
                        <tr>
                            <th>Tên sân</th>
                            <td>{{ $court->name }}</td>
                        </tr>
                        <tr>
                            <th>Địa điểm</th>
                            <td>{{ $court->venue->name ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Loại hình</th>
                            <td>{{ $court->venueType->name ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Loại sân</th>
                            <td>
                                @if ($court->is_indoor)
                                <span class="badge bg-info text-dark">Trong nhà</span>
                                @else
                                <span class="badge bg-warning text-dark">Ngoài trời</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Bề mặt</th>
                            <td>{{ $court->surface ?? 'Chưa cập nhật' }}</td>
                        </tr>
                        <tr>
                            <th>Ngày tạo</th>
                            <td>{{ $court->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                        <tr>
                            <th>Cập nhật</th>
                            <td>{{ $court->updated_at->format('d/m/Y H:i') }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        {{-- LỊCH HOẠT ĐỘNG --}}
        <div class="col-lg-4 mb-2 info-card-decorated">
            <form action="{{ route('admin.courts.updateAvailabilities', $court) }}" method="POST">
                @csrf
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Lịch hoạt động (30 ngày tới)</h5>
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="fas fa-save me-1"></i> Lưu thay đổi
                        </button>
                    </div>
                    <div class="card-body">
                        @if ($availabilities->isNotEmpty())
                        <?php
                        $perPage = 7; // Số ngày hiển thị trên mỗi trang
                        $currentPage = request()->get('page', 1); // Lấy trang hiện tại từ URL, mặc định là 1
                        $totalDays = count($availabilities);
                        $totalPages = ceil($totalDays / $perPage);
                        $offset = ($currentPage - 1) * $perPage;
                        $pagedAvailabilities = array_slice($availabilities->all(), $offset, $perPage, true);
                        ?>
                        <div class="accordion" id="availabilityAccordion">
                            @foreach ($pagedAvailabilities as $date => $dayAvailabilities)
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
                                                    @if ($availability->timeSlot && $availability->timeSlot->start_time && $availability->timeSlot->end_time)
                                                    {{ \Carbon\Carbon::parse($availability->timeSlot->start_time)->format('H:00') }} - {{ \Carbon\Carbon::parse($availability->timeSlot->end_time)->format('H:00') }}
                                                    @else
                                                    N/A
                                                    @endif
                                                </div>

                                                {{-- Container giá + công tắc --}}
                                                <div class="d-flex align-items-center" style="min-width: 140px; position: relative; top: -10px;">
                                                    <span class="text-nowrap text-end pe-4" style="flex-basis: 60px;">
                                                        {{ floor($availability->price / 1000) }}k
                                                    </span>
                                                    @if ($availability->status === 'booked')
                                                    <span class="badge bg-danger" style="width: 75px;">Đã đặt</span>
                                                    @else
                                                    <div class="form-check form-switch custom-switch">
                                                        <input type="hidden" name="statuses[{{ $availability->id }}]" value="maintenance">
                                                        <input class="form-check-input" type="checkbox" role="switch" id="statusSwitch{{ $availability->id }}" name="statuses[{{ $availability->id }}]" value="open" {{ $availability->status === 'open' ? 'checked' : '' }}>
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
                        <!-- Phân trang -->
                        <div class="d-flex justify-content-center mt-3">
                            <nav aria-label="Page navigation">
                                <ul class="pagination">
                                    <li class="page-item {{ $currentPage <= 1 ? 'disabled' : '' }}">
                                        <a class="page-link" href="{{ request()->fullUrlWithQuery(['page' => $currentPage - 1]) }}" aria-label="Previous">
                                            <span aria-hidden="true">Previous</span>
                                        </a>
                                    </li>
                                    @for ($i = 1; $i <= $totalPages; $i++)
                                        <li class="page-item {{ $i == $currentPage ? 'active' : '' }}">
                                        <a class="page-link" href="{{ request()->fullUrlWithQuery(['page' => $i]) }}">{{ $i }}</a>
                                        </li>
                                        @endfor
                                        <li class="page-item {{ $currentPage >= $totalPages ? 'disabled' : '' }}">
                                            <a class="page-link" href="{{ request()->fullUrlWithQuery(['page' => $currentPage + 1]) }}" aria-label="Next">
                                                <span aria-hidden="true">Next</span>
                                            </a>
                                        </li>
                                </ul>
                            </nav>
                        </div>
                        @else
                        <div class="text-center text-muted p-4">
                            <i class="fas fa-calendar-times fa-3x mb-3"></i>
                            <p>Chưa có lịch hoạt động nào được thiết lập.</p>
                            <a href="{{ route('admin.courts.edit', $court) }}" class="btn btn-primary mt-2">Thiết lập ngay</a>
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
</xaiArtifact>