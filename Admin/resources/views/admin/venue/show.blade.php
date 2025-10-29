@extends('app')

@section('content')

    <style>
        :root {
            --bs-primary: #348738;
            /* xanh lá chủ đạo */
            --bs-primary-rgb: 52, 135, 56;
            --bs-primary-bg-subtle: #e1f3e2;
            --bs-primary-border-subtle: #d1e7dd;

            --bs-accent: #f97316;
            /* orange-500 */
            --bs-accent-bg-subtle: #fff4e6;
            --bs-accent-border-subtle: #fed7aa;
        }

        .text-primary {
            color: var(--bs-primary) !important;
        }

        .text-accent {
            color: var(--bs-accent) !important;
        }

        .card-header-green {
            background-color: var(--bs-primary-bg-subtle);
            border-bottom: 1px solid var(--bs-primary-border-subtle);
        }

        .card-header-accent {
            background-color: var(--bs-accent-bg-subtle);
            border-bottom: 1px solid var(--bs-accent-border-subtle);
        }

        .badge-primary {
            background-color: var(--bs-primary);
        }

        .badge-accent {
            background-color: var(--bs-accent);
            color: #fff;
        }

        .btn-primary {
            background-color: var(--bs-primary);
            border-color: var(--bs-primary);
        }

        .btn-accent {
            background-color: var(--bs-accent);
            border-color: var(--bs-accent);
        }
    </style>

    <div class="container-fluid py-4">

        {{-- HEADER --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h4 mb-0 text-primary">Chi tiết thương hiệu</h1>
                <p class="text-muted mb-0">Quản lý thông tin cho: <strong>{{ $venue->name }}</strong></p>
            </div>
            <div class="d-flex gap-2">
                <form action="{{ route('admin.venues.update-status', $venue->id) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="is_active" value="{{ $venue->is_active ? 0 : 1 }}">
                    <button type="submit" class="btn btn-{{ $venue->is_active ? 'warning' : 'success' }}">
                        {{ $venue->is_active ? 'Tạm dừng hoạt động' : 'Kích hoạt' }}
                    </button>
                </form>
                <a href="{{ route('admin.venues.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Quay lại
                </a>
            </div>
        </div>

        <div class="row">

            {{-- CỘT CHÍNH --}}
            <div class="col-lg-8">

                {{-- Thông tin cơ bản --}}
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header card-header-green py-3">
                        <h5 class="mb-0 text-primary">Thông tin cơ bản</h5>
                    </div>
                    <div class="card-body">
                        <dl class="row mb-0">
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
                                {{ \Carbon\Carbon::parse($venue->end_time)->format('H:i') }}</dd>

                            <dt class="col-sm-3">Số điện thoại:</dt>
                            <dd class="col-sm-9">{{ $venue->phone ?? 'Chưa cập nhật' }}</dd>

                            <dt class="col-sm-3">Trạng thái:</dt>
                            <dd class="col-sm-9">
                                @if ($venue->is_active)
                                    <span class="badge badge-primary">Đang hoạt động</span>
                                @else
                                    <span class="badge badge-secondary">Tạm dừng</span>
                                @endif
                            </dd>
                        </dl>
                    </div>
                </div>

                {{-- Danh sách sân con --}}
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header card-header-accent py-3">
                        <h5 class="mb-0 text-accent">Danh sách sân con ({{ $venue->courts->count() }})</h5>
                    </div>
                    <div class="card-body p-0">
                        @if ($venue->courts->count())
                            <div class="table-responsive">
                                <table class="table table-hover table-striped mb-0 align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Tên sân</th>
                                            <th>Loại hình</th>
                                            <th>Giá (đ/giờ)</th>
                                            <th class="text-center">Loại sân</th>
                                            <th class="text-end">Chi tiết</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($venue->courts as $court)
                                            <tr class="align-middle">
                                                <td><strong>{{ $court->name }}</strong></td>
                                                <td>{{ $court->venueType->name ?? 'N/A' }}</td>
                                                <td>{{ number_format($court->price_per_hour, 0, ',', '.') }}</td>
                                                <td class="text-center">
                                                    @if ($court->is_indoor)
                                                        <span class="badge badge-primary py-1 px-2">Trong nhà</span>
                                                    @else
                                                        <span class="badge badge-accent py-1 px-2">Ngoài trời</span>
                                                    @endif
                                                </td>
                                                <td class="text-end">
                                                    <a href="{{ route('admin.venues.courts.show', ['venue' => $venue->id, 'court' => $court->id]) }}"
                                                        class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-eye me-1"></i> Xem
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="p-4 text-center text-muted">
                                <i class="fas fa-futbol fa-2x mb-2"></i>
                                <div>Chưa có sân con nào được tạo.</div>
                            </div>
                        @endif
                    </div>
                </div>

            </div>

            {{-- CỘT BÊN PHẢI --}}
            <div class="col-lg-4">

                {{-- Hình ảnh --}}
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header card-header-green py-3">
                        <h5 class="mb-0 text-primary">Hình ảnh</h5>
                    </div>
                    <div class="card-body">
                        @if ($venue->images->count())
                            @php
                                $primaryImage = $venue->images->firstWhere('is_primary', 1) ?? $venue->images->first();
                                $otherImages = $venue->images->where('id', '!=', $primaryImage->id);
                            @endphp

                            {{-- Ảnh chính --}}
                            <div class="mb-3 position-relative">
                                <img src="{{ $primaryImage->url }}" class="img-fluid rounded shadow-sm w-100"
                                    style="max-height:300px; object-fit:contain; background-color:#f0f0f0;" alt="Ảnh chính">
                                <span class="badge bg-primary position-absolute top-0 start-0 m-2">Ảnh chính</span>
                            </div>

                            {{-- Ảnh phụ - 2 cột --}}
                            @if ($otherImages->count())
                                <div class="row g-2">
                                    @foreach ($otherImages as $image)
                                        <div class="col-6">
                                            <div class="position-relative overflow-hidden rounded shadow-sm"
                                                style="height:150px;">
                                                <img src="{{ $image->url }}" class="img-fluid w-100 h-100"
                                                    style="object-fit:cover; transition: transform 0.3s;" alt="Image">
                                                <div class="position-absolute top-0 start-0 w-100 h-100 overlay"
                                                    style="background-color: rgba(0,0,0,0.1); opacity:0; transition:0.3s;">
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        @else
                            <p class="text-muted">Chưa có hình ảnh.</p>
                        @endif
                    </div>
                </div>


                {{-- Dịch vụ --}}
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header card-header-accent py-3">
                        <h5 class="mb-0 text-accent">Dịch vụ</h5>
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
