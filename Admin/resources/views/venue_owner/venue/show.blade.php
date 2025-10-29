@extends('app')

@section('content')
    {{-- Thêm <style> để định nghĩa màu xanh lá/cam --}}
    <style>
        :root {
            --bs-primary: #348738;
            --bs-primary-rgb: 52, 135, 56;
            --bs-primary-dark: #2d6a2d;
            --bs-primary-bg-subtle: #e1f3e2;
            --bs-primary-border-subtle: #d1e7dd;
        }

        .btn-primary {
            --bs-btn-hover-bg: #2d6a2d;
            --bs-btn-hover-border-color: #2d6a2d;
        }

        .text-primary {
            color: var(--bs-primary) !important;
        }

        .btn-accent {
            --bs-btn-bg: #f97316;
            /* Tailwind orange-500 */
            --bs-btn-border-color: #f97316;
            --bs-btn-hover-bg: #ea580c;
            /* Tailwind orange-600 */
            --bs-btn-hover-border-color: #ea580c;
            --bs-btn-color: #fff;
        }

        .card-header-green {
            background-color: var(--bs-primary-bg-subtle);
            border-bottom: 1px solid var(--bs-primary-border-subtle);
        }

        .badge-primary {
            background-color: var(--bs-primary);
        }
    </style>

    <div class="container-fluid py-4">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h4 mb-0">Chi tiết thương hiệu</h1>
                <p class="text-muted mb-0">Quản lý thông tin cho: <strong>{{ $venue->name }}</strong></p>
            </div>
            <div>

                <a href="{{ route('owner.venues.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Quay lại
                </a>
            </div>
        </div>

        <div class="row">
            {{-- CỘT CHÍNH (BÊN TRÁI) --}}
            <div class="col-lg-8">
                {{-- Thông tin cơ bản --}}
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header card-header-green py-3">
                        <h5 class="mb-0 text-primary">Thông tin cơ bản</h5>
                    </div>
                    <div class="card-body">
                        <dl class="row">
                            <dt class="col-sm-3">Tên thương hiệu:</dt>
                            <dd class="col-sm-9">{{ $venue->name }}</dd>

                            <dt class="col-sm-3">Chủ sở hữu:</dt>
                            <dd class="col-sm-9">{{ $venue->owner->name ?? 'N/A' }} ({{ $venue->owner->email ?? 'N/A' }})
                            </dd>

                            <dt class="col-sm-3">Địa chỉ:</dt>
                            <dd class="col-sm-9">{{ $venue->address_detail }}, {{ $venue->district->name ?? '' }},
                                {{ $venue->province->name ?? '' }}</dd>

                            <dt class="col-sm-3">Giờ hoạt động:</dt>
                            <dd class="col-sm-9">{{ \Carbon\Carbon::parse($venue->start_time)->format('H:i') }} -
                                {{ \Carbon\Carbon::parse($venue->end_time)->format('H:i') }}</p>

                            <dt class="col-sm-3">Số điện thoại:</dt>
                            <dd class="col-sm-9">{{ $venue->phone ?? 'Chưa cập nhật' }}</dd>

                            <dt class="col-sm-3">Trạng thái:</dt>
                            <dd class="col-sm-9">
                                @if ($venue->is_active)
                                    <span class="badge bg-success">Đang hoạt động</span>
                                @else
                                    <span class="badge bg-secondary">Tạm dừng</span>
                                @endif
                            </dd>
                        </dl>
                    </div>
                </div>

                {{-- Danh sách sân con --}}
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header card-header-green py-3 d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 text-primary">Danh sách sân con ({{ $venue->courts->count() }})</h5>
                        <a href="{{ route('owner.venues.courts.create', $venue->id) }}" class="btn btn-accent me-2">
                            <i class="fas fa-plus me-1"></i> Thêm
                        </a>
                    </div>
                    <div class="card-body p-0">
                        @if ($venue->courts->count())
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Tên sân</th>
                                            <th>Loại hình</th>
                                            <th>Giá (đ/giờ)</th>
                                            <th class="text-center">Loại sân</th>
                                            <th class="text-end">Lịch</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($venue->courts as $court)
                                            <tr>
                                                <td><strong>{{ $court->name }}</strong></td>
                                                <td>{{ $court->venueType->name ?? 'N/A' }}</td>
                                                <td>{{ number_format($court->price_per_hour, 0, ',', '.') }}</td>
                                                <td class="text-center">
                                                    @if ($court->is_indoor)
                                                        <span class="badge bg-info text-dark">Trong nhà</span>
                                                    @else
                                                        <span class="badge bg-warning text-dark">Ngoài trời</span>
                                                    @endif
                                                </td>
                                                <td class="text-end">
                                                    <a href="{{ route('owner.venues.courts.show', ['venue' => $venue->id, 'court' => $court->id]) }}"
                                                        class="btn btn-sm btn-outline-primary">
                                                        Xem lịch
                                                    </a>

                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="p-3 text-center text-muted">Chưa có sân con nào được tạo.</div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- CỘT BÊN PHẢI --}}
            <div class="col-lg-4">
                {{-- Hành động --}}
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header card-header-green py-3">
                        <h5 class="mb-0 text-primary">Hành động</h5>
                    </div>
                    <div class="card-body d-grid gap-2">
                        <a href="{{ route('owner.venues.edit', $venue) }}" class="btn btn-warning">
                            <i class="fas fa-edit me-1"></i> Chỉnh sửa thông tin Venue
                        </a>
                        <a href="{{ route('owner.venues.courts.create', $venue->id) }}" class="btn btn-accent me-2">
                            <i class="fas fa-plus me-1"></i> Thêm Sân Con Mới
                        </a>
                    </div>
                </div>

                {{-- Hình ảnh --}}
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header card-header-green py-3">
                        <h5 class="mb-0 text-primary">Hình ảnh</h5>
                    </div>
                    <div class="card-body">
                        @if ($venue->images->count())
                            @php
                                $primaryImage = $venue->images->firstWhere('is_primary', 1);
                            @endphp
                            @if ($primaryImage)
                                <div class="mb-3 position-relative">
                                    <img src="{{ $primaryImage->url }}" class="img-fluid rounded shadow-sm"
                                        alt="Ảnh chính">
                                    <span class="badge bg-primary position-absolute top-0 start-0 m-2">Ảnh chính</span>
                                </div>
                            @endif
                            <div class="row g-2">
                                @foreach ($venue->images->where('is_primary', 0) as $image)
                                    <div class="col-4">
                                        <img src="{{ $image->url }}" class="img-fluid rounded" alt="Image">
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-muted">Chưa có hình ảnh.</p>
                        @endif
                    </div>
                </div>

                {{-- Dịch vụ --}}
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header card-header-green py-3">
                        <h5 class="mb-0 text-primary">Dịch vụ</h5>
                    </div>
                    <div class="card-body p-0">
                        @if ($venue->services->count())
                            <ul class="list-group list-group-flush">
                                @foreach ($venue->services as $service)
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        {{ $service->name }}
                                        <span class="text-muted">{{ number_format($service->price) }}đ</span>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <div class="p-3 text-center text-muted">Chưa có dịch vụ nào.</div>
                        @endif
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection
