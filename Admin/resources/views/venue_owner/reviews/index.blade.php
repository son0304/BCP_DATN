@extends('app')

@section('content')
    <div class="container-fluid">

        <!-- Tiêu đề trang -->
        <div class="row mb-3 mt-3">
            <div class="col-12">
                <h4 class="page-title">Đánh giá từ khách hàng</h4>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">

                        <!-- Thông báo nếu chưa có đánh giá -->
                        @if ($reviews->isEmpty())
                            <div class="alert alert-info text-center" role="alert">
                                <i class="ri-information-line me-1"></i>
                                Sân của bạn chưa có đánh giá nào.
                            </div>
                        @else
                            <!-- Bảng dữ liệu -->
                            <div class="table-responsive">
                                <table class="table table-centered table-nowrap mb-0 table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 5%;">#</th>
                                            <th style="width: 25%;">Khách hàng</th>
                                            <th style="width: 15%;">Điểm đánh giá</th>
                                            <th style="width: 40%;">Nội dung nhận xét</th>
                                            <th style="width: 15%;">Thời gian</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($reviews as $key => $review)
                                            <tr>
                                                <!-- STT -->
                                                <td>{{ $key + 1 }}</td>

                                                <!-- Thông tin khách -->
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <!-- Avatar chữ cái -->
                                                        <div class="avatar-sm me-2">
                                                            <span
                                                                class="avatar-title bg-primary-lighten text-primary rounded-circle">
                                                                {{ substr($review->user->name ?? 'K', 0, 1) }}
                                                            </span>
                                                        </div>
                                                        <div>
                                                            <h5 class="font-14 my-1 fw-normal">
                                                                {{ $review->user->name ?? 'Khách ẩn danh' }}</h5>
                                                            <small
                                                                class="text-muted">{{ $review->user->email ?? '' }}</small>
                                                        </div>
                                                    </div>
                                                </td>

                                                <!-- Số sao (Rating) -->
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

                                                <!-- Nội dung comment -->
                                                <td>
                                                    @if ($review->comment)
                                                        <span class="text-dark"
                                                            style="white-space: pre-wrap;">{{ $review->comment }}</span>
                                                    @else
                                                        <em class="text-muted">Khách hàng không để lại lời nhắn.</em>
                                                    @endif
                                                </td>

                                                <!-- Thời gian -->
                                                <td>
                                                    {{ $review->created_at->format('H:i - d/m/Y') }}
                                                    <br>
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
