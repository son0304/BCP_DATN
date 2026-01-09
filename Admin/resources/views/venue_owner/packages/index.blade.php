@extends('app')

@section('content')
    <div class="container py-5">
        <div class="row g-4 justify-content-center">
            @foreach ($packages as $package)
                <div class="col-md-4">
                    <div
                        class="card h-100 shadow-sm border-0 rounded-4 overflow-hidden transition-hover {{ $package->is_purchased ? 'border border-2 border-success bg-success-subtle' : '' }}">

                        <div class="card-header border-0 pt-4 text-center bg-transparent">
                            @if ($package->is_purchased)
                                <span class="badge bg-success px-3 py-2 rounded-pill mb-2">
                                    <i class="fas fa-check-circle me-1"></i> Đang sử dụng
                                </span>
                            @else
                                <span class="badge bg-primary-soft text-primary px-3 py-2 rounded-pill mb-2">Gói Combo</span>
                            @endif
                            <h4 class="fw-bold text-dark">{{ $package->name }}</h4>
                        </div>

                        <div class="card-body px-4 text-center">
                            <span class="display-6 fw-bold text-primary">{{ number_format($package->price) }}đ</span>
                            <div class="text-muted small">Thời hạn: {{ $package->duration_days }} ngày</div>
                            <hr class="opacity-25">

                            {{-- List quyền lợi giữ nguyên --}}
                            <ul class="list-unstyled text-start mt-3">
                                @foreach ($package->items as $item)
                                    <li class="mb-2"><i class="fas fa-check text-primary me-2"></i> {{ $item->type }}...
                                    </li>
                                @endforeach
                            </ul>
                        </div>

                        <div class="card-footer border-0 pb-4 px-4 bg-transparent">
                            @if ($package->is_purchased)
                                {{-- TRƯỜNG HỢP 1: ĐÃ MUA -> Link đến trang Quản lý (Manage) --}}
                                <a href="{{ route('owner.packages.manage', $package->id) }}"
                                    class="btn w-100 py-2 rounded-3 fw-bold btn-success">
                                    <i class="fas fa-eye me-2"></i> Xem dịch vụ đã mua
                                </a>
                            @else
                                {{-- TRƯỜNG HỢP 2: CHƯA MUA -> Link đến trang Mua (Buy) --}}
                                <a href="{{ route('owner.packages.buy', $package->id) }}"
                                    class="btn w-100 py-2 rounded-3 fw-bold btn-outline-primary">
                                    Xem chi tiết & Mua <i class="fas fa-arrow-right ms-2"></i>
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection
