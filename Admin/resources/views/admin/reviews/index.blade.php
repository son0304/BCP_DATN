@extends('app')

@section('content')
    <div class="container-fluid">
        <div class="row mb-3 mt-3">
            <div class="col-12">
                <h4 class="page-title">Quản lý Đánh giá & Nhận xét</h4>
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        @if ($reviews->isEmpty())
                            <div class="alert alert-warning text-center">Chưa có đánh giá nào trên hệ thống.</div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-centered table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 50px;">#</th>
                                            <th>Khách hàng</th>
                                            <th>Sân</th>
                                            <th style="width: 130px;">Điểm số</th>
                                            <th>Nội dung & Ảnh</th>
                                            <th style="width: 120px;">Thời gian</th>
                                            <th style="width: 80px;" class="text-center">Hành động</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($reviews as $key => $review)
                                            <tr>
                                                <td>{{ $key + 1 }}</td>
                                                <td>
                                                    <h5 class="font-14 m-0">{{ $review->user->name ?? 'N/A' }}</h5>
                                                    <small class="text-muted">{{ $review->user->email ?? '' }}</small>
                                                </td>
                                                <td>
                                                    <span
                                                        class="badge bg-soft-info text-info border border-info">{{ $review->venue->name ?? 'Đã xóa' }}</span>
                                                </td>
                                                <td>
                                                    <div class="text-warning">
                                                        @for ($i = 1; $i <= 5; $i++)
                                                            <i
                                                                class="{{ $i <= $review->rating ? 'fas' : 'far' }} fa-star"></i>
                                                        @endfor
                                                    </div>
                                                </td>
                                                <td>
                                                    <!-- Nội dung -->
                                                    <p class="mb-1 text-dark">
                                                        {{ Str::limit($review->comment, 70) }}
                                                        @if (strlen($review->comment) > 70 || $review->images->count() > 0)
                                                            <a href="javascript:void(0);" class="fw-bold"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#modalReview{{ $review->id }}">Chi
                                                                tiết</a>
                                                        @endif
                                                    </p>

                                                    <!-- Hiển thị ảnh Thumbnail từ cột 'url' -->
                                                    <div class="d-flex flex-wrap gap-1">
                                                        @foreach ($review->images->take(4) as $img)
                                                            <img src="{{ $img->url }}" class="rounded border shadow-sm"
                                                                style="width: 42px; height: 42px; object-fit: cover; cursor: pointer;"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#modalReview{{ $review->id }}">
                                                        @endforeach
                                                        @if ($review->images->count() > 4)
                                                            <div class="rounded bg-light border d-flex align-items-center justify-content-center text-muted"
                                                                style="width: 42px; height: 42px; font-size: 11px;">
                                                                +{{ $review->images->count() - 4 }}
                                                            </div>
                                                        @endif
                                                    </div>

                                                    <!-- Modal Chi tiết đánh giá & Ảnh lớn -->
                                                    <div class="modal fade" id="modalReview{{ $review->id }}"
                                                        tabindex="-1" aria-hidden="true">
                                                        <div class="modal-dialog modal-lg modal-dialog-centered">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title">Đánh giá của
                                                                        {{ $review->user->name }}</h5>
                                                                    <button type="button" class="btn-close"
                                                                        data-bs-dismiss="modal"></button>
                                                                </div>
                                                                <div class="modal-body">
                                                                    <p><strong>Nhận xét:</strong>
                                                                        {{ $review->comment ?: 'Không có nội dung.' }}</p>

                                                                    @if ($review->images->count() > 0)
                                                                        <hr>
                                                                        <div class="row g-2">
                                                                            @foreach ($review->images as $img)
                                                                                <div class="col-md-4 col-6">
                                                                                    <a href="{{ $img->url }}"
                                                                                        target="_blank">
                                                                                        <img src="{{ $img->url }}"
                                                                                            class="rounded w-100 shadow-sm"
                                                                                            style="height: 180px; object-fit: cover;">
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
                                                    <small>{{ $review->created_at->format('d/m/Y') }}</small><br>
                                                    <small
                                                        class="text-muted">{{ $review->created_at->format('H:i') }}</small>
                                                </td>
                                                <td class="text-center">
                                                    <form action="{{ route('admin.reviews.destroy', $review->id) }}"
                                                        method="POST">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit"
                                                            class="btn btn-sm btn-light text-danger border"
                                                            onclick="return confirm('Xóa đánh giá này?')">
                                                            <i class="ri-delete-bin-line"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
