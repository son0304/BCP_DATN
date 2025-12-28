@extends('app')

@section('content')
    <div class="container-fluid">

        <!-- Tiêu đề & Thông báo -->
        <div class="row mb-3 mt-3">
            <div class="col-12">
                <h4 class="page-title">Quản lý Đánh giá & Nhận xét (Toàn hệ thống)</h4>

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
                            <div class="alert alert-warning text-center">
                                Hiện tại chưa có đánh giá nào trên hệ thống.
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-centered table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 50px;">#</th>
                                            <th>Khách hàng</th>
                                            <th>Sân được đánh giá</th> <!-- Admin cần cột này -->
                                            <th style="width: 150px;">Điểm số</th>
                                            <th>Nội dung</th>
                                            <th>Thời gian</th>
                                            <th style="width: 100px;" class="text-center">Hành động</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($reviews as $key => $review)
                                            <tr>
                                                <!-- STT -->
                                                <td>{{ $key + 1 }}</td>

                                                <!-- Người dùng -->
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar-sm me-2">
                                                            <span
                                                                class="avatar-title bg-soft-primary text-primary rounded-circle">
                                                                {{ substr($review->user->name ?? 'U', 0, 1) }}
                                                            </span>
                                                        </div>
                                                        <div>
                                                            <h5 class="font-14 m-0">
                                                                {{ $review->user->name ?? 'User đã xóa' }}</h5>
                                                            <small
                                                                class="text-muted">{{ $review->user->email ?? '' }}</small>
                                                        </div>
                                                    </div>
                                                </td>

                                                <!-- Tên Sân (Quan trọng với Admin) -->
                                                <td>
                                                    @if ($review->venue)
                                                        <span class="badge bg-info p-1">{{ $review->venue->name }}</span>
                                                    @else
                                                        <span class="badge bg-secondary">Sân đã bị xóa</span>
                                                    @endif
                                                </td>

                                                <!-- Điểm sao -->
                                                <td>
                                                    <div class="text-warning font-16">
                                                        @for ($i = 1; $i <= 5; $i++)
                                                            @if ($i <= $review->rating)
                                                                <!-- Sao đầy (FontAwesome) -->
                                                                <i class="fas fa-star"></i>
                                                            @else
                                                                <!-- Sao rỗng (FontAwesome) -->
                                                                <i class="far fa-star"></i>
                                                            @endif
                                                        @endfor
                                                    </div>
                                                    <span class="font-13 fw-bold mt-1 d-block text-muted">
                                                        {{ $review->rating }}/5 điểm
                                                    </span>
                                                </td>

                                                <!-- Nội dung -->
                                                <td>
                                                    <span class="text-break" style="max-width: 300px; display: block;">
                                                        {{ Str::limit($review->comment ?? 'Không có nội dung', 80) }}
                                                    </span>
                                                    @if (strlen($review->comment) > 80)
                                                        <small class="text-primary" style="cursor: pointer;"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#modalReview{{ $review->id }}">Xem
                                                            thêm</small>

                                                        <!-- Modal xem chi tiết -->
                                                        <div class="modal fade" id="modalReview{{ $review->id }}"
                                                            tabindex="-1" aria-hidden="true">
                                                            <div class="modal-dialog">
                                                                <div class="modal-content">
                                                                    <div class="modal-header">
                                                                        <h5 class="modal-title">Nội dung đánh giá</h5>
                                                                        <button type="button" class="btn-close"
                                                                            data-bs-dismiss="modal"></button>
                                                                    </div>
                                                                    <div class="modal-body">
                                                                        {{ $review->comment }}
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endif
                                                </td>

                                                <!-- Thời gian -->
                                                <td>
                                                    {{ $review->created_at->format('d/m/Y') }} <br>
                                                    <small
                                                        class="text-muted">{{ $review->created_at->format('H:i') }}</small>
                                                </td>

                                                <!-- Nút Xóa -->
                                                <td class="text-center">
                                                    <form action="{{ route('admin.reviews.destroy', $review->id) }}"
                                                        method="POST" class="d-inline-block">
                                                        @csrf
                                                        @method('DELETE')

                                                        <button type="submit" class="btn btn-outline-danger btn-sm"
                                                            onclick="return confirm('Cảnh báo: Bạn có chắc chắn muốn xóa đánh giá này không? Hành động này không thể hoàn tác.');"
                                                            data-bs-toggle="tooltip" title="Xóa đánh giá">
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

                    </div> <!-- end card-body-->
                </div> <!-- end card-->
            </div>
        </div>
    </div>
@endsection
