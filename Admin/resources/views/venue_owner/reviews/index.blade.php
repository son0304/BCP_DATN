@extends('app')

@section('content')
    <div class="container-fluid">
        <!-- Tiêu đề trang & Thông báo -->
        <div class="row mb-3 mt-3">
            <div class="col-12">
                <h4 class="page-title">Đánh giá từ khách hàng</h4>
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="ri-check-line me-1"></i> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm border-0">
                    <div class="card-body">

                        @if ($reviews->isEmpty())
                            <div class="alert alert-info text-center" role="alert">
                                <i class="ri-information-line me-1"></i>
                                Sân của bạn hiện chưa có đánh giá nào.
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-centered table-hover mb-0">
                                    <thead class="table-light text-muted">
                                        <tr>
                                            <th style="width: 50px;">#</th>
                                            <th>Khách hàng</th>
                                            <th>Sân áp dụng</th>
                                            <th style="width: 130px;">Điểm số</th>
                                            <th>Nội dung & Hình ảnh</th>
                                            <th style="width: 150px;">Thời gian</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($reviews as $key => $review)
                                            <tr>
                                                <td>{{ $key + 1 }}</td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar-sm me-2">
                                                            <span
                                                                class="avatar-title bg-primary-lighten text-primary rounded-circle"
                                                                style="padding: 10px; background: #eef2ff;">
                                                                {{ substr($review->user->name ?? 'K', 0, 1) }}
                                                            </span>
                                                        </div>
                                                        <div>
                                                            <h5 class="font-14 m-0 fw-bold text-dark">
                                                                {{ $review->user->name ?? 'Khách ẩn danh' }}</h5>
                                                            <small
                                                                class="text-muted">{{ $review->user->email ?? '' }}</small>
                                                                <br>
                                                            <small
                                                                class="text-muted">{{ $review->user->phone ?? '' }}</small>

                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge bg-soft-info text-info border border-info px-2 py-1">
                                                        {{ $review->venue->name ?? 'N/A' }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="text-warning">
                                                        @for ($i = 1; $i <= 5; $i++)
                                                            <i
                                                                class="{{ $i <= $review->rating ? 'fas' : 'far' }} fa-star"></i>
                                                        @endfor
                                                    </div>
                                                    <small class="text-muted fw-bold">{{ $review->rating }}/5 điểm</small>
                                                </td>
                                                <td>
                                                    <!-- Nội dung nhận xét -->
                                                    <p class="mb-2 text-dark">
                                                        {{ Str::limit($review->comment, 80) }}
                                                        @if (strlen($review->comment) > 80 || $review->images->count() > 0)
                                                            <a href="javascript:void(0);" class="text-primary fw-bold small"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#modalReviewOwner{{ $review->id }}">Xem
                                                                chi tiết</a>
                                                        @endif
                                                    </p>

                                                    <!-- Thumbnail Hình ảnh (Load giống admin) -->
                                                    <div class="d-flex flex-wrap gap-1">
                                                        @foreach ($review->images->take(4) as $img)
                                                            <img src="{{ $img->url }}" class="rounded border shadow-sm"
                                                                style="width: 45px; height: 45px; object-fit: cover; cursor: pointer;"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#modalReviewOwner{{ $review->id }}">
                                                        @endforeach

                                                        @if ($review->images->count() > 4)
                                                            <div class="rounded bg-light border d-flex align-items-center justify-content-center text-muted fw-bold"
                                                                style="width: 45px; height: 45px; font-size: 12px; cursor: pointer;"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#modalReviewOwner{{ $review->id }}">
                                                                +{{ $review->images->count() - 4 }}
                                                            </div>
                                                        @endif
                                                    </div>

                                                    <!-- Modal Chi tiết đánh giá (Load giống admin) -->
                                                    <div class="modal fade" id="modalReviewOwner{{ $review->id }}"
                                                        tabindex="-1" aria-hidden="true">
                                                        <div class="modal-dialog modal-lg modal-dialog-centered">
                                                            <div class="modal-content border-0">
                                                                <div class="modal-header bg-light">
                                                                    <h5 class="modal-title">Chi tiết đánh giá -
                                                                        {{ $review->user->name }}</h5>
                                                                    <button type="button" class="btn-close"
                                                                        data-bs-dismiss="modal"></button>
                                                                </div>
                                                                <div class="modal-body">
                                                                    <div class="mb-3">
                                                                        <label class="fw-bold text-muted mb-1">Nội dung nhận
                                                                            xét:</label>
                                                                        <p class="text-dark fs-5"
                                                                            style="white-space: pre-wrap;">
                                                                            {{ $review->comment ?: 'Khách hàng không để lại lời nhắn.' }}
                                                                        </p>
                                                                    </div>

                                                                    @if ($review->images->count() > 0)
                                                                        <label class="fw-bold text-muted mb-2">Hình ảnh đính
                                                                            kèm ({{ $review->images->count() }}):</label>
                                                                        <div class="row g-2">
                                                                            @foreach ($review->images as $img)
                                                                                <div class="col-md-4 col-6">
                                                                                    <a href="{{ $img->url }}"
                                                                                        target="_blank">
                                                                                        <img src="{{ $img->url }}"
                                                                                            class="rounded w-100 shadow-sm border"
                                                                                            style="height: 200px; object-fit: cover;">
                                                                                    </a>
                                                                                </div>
                                                                            @endforeach
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="small fw-bold">{{ $review->created_at->format('d/m/Y') }}
                                                    </div>
                                                    <small
                                                        class="text-muted">{{ $review->created_at->diffForHumans() }}</small>
                                                </td>

                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif

                    </div> <!-- end card-body-->
                </div> <!-- end card-->
            </div> <!-- end col -->
        </div>
    </div>
@endsection
