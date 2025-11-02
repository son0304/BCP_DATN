@extends('app')
@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="card-title">Chi tiết Voucher: {{ $promotion->code }}</h3>
                        <div>
                            <a href="{{ route('admin.promotions.edit', $promotion) }}" class="btn btn-warning">
                                <i class="fas fa-edit"></i> Chỉnh sửa
                            </a>
                            <a href="{{ route('admin.promotions.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Quay lại
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="card-body">
                    <!-- Alerts -->
                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <div class="row">
                        <!-- Thông tin cơ bản -->
                        <div class="col-md-6">
                            <h5 class="mb-3">Thông tin Voucher</h5>
                            <table class="table table-bordered">
                                <tr>
                                    <th width="40%">ID</th>
                                    <td>{{ $promotion->id }}</td>
                                </tr>
                                <tr>
                                    <th>Mã voucher</th>
                                    <td><span class="badge bg-primary fs-6">{{ $promotion->code }}</span></td>
                                </tr>
                                <tr>
                                    <th>Giá trị</th>
                                    <td>
                                        <strong class="text-primary fs-5">
                                            @if($promotion->type == '%')
                                                {{ number_format($promotion->value, 0) }}%
                                            @else
                                                {{ number_format($promotion->value, 0) }}₫
                                            @endif
                                        </strong>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Loại</th>
                                    <td>
                                        @if($promotion->type == '%')
                                            <span class="badge bg-info">Phần trăm (%)</span>
                                        @else
                                            <span class="badge bg-warning">Tiền mặt (VND)</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Ngày bắt đầu</th>
                                    <td>{{ \Carbon\Carbon::parse($promotion->start_at, 'UTC')->setTimezone('Asia/Ho_Chi_Minh')->format('d/m/Y H:i') }}</td>
                                </tr>
                                <tr>
                                    <th>Ngày kết thúc</th>
                                    <td>{{ \Carbon\Carbon::parse($promotion->end_at, 'UTC')->setTimezone('Asia/Ho_Chi_Minh')->format('d/m/Y H:i') }}</td>
                                </tr>
                                <tr>
                                    <th>Giới hạn sử dụng</th>
                                    <td>
                                        @if($promotion->usage_limit == 0)
                                            <span class="text-muted">Không giới hạn</span>
                                        @else
                                            {{ $promotion->usage_limit }} lần
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Số lần đã sử dụng</th>
                                    <td><strong>{{ $promotion->used_count }}</strong> lần</td>
                                </tr>
                                <tr>
                                    <th>Người tạo</th>
                                    <td>
                                        @if($promotion->creator)
                                            <strong>{{ $promotion->creator->name }}</strong>
                                            <br>
                                            <small class="text-muted">{{ $promotion->creator->email }}</small>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Trạng thái</th>
                                    <td>
                                        @php
                                            $now = now();
                                            $startAt = \Carbon\Carbon::parse($promotion->start_at);
                                            $endAt = \Carbon\Carbon::parse($promotion->end_at);
                                            $isNotStarted = $startAt->gt($now);
                                            $isExpired = $endAt->lt($now);
                                        @endphp
                                        @if($isNotStarted)
                                            <span class="badge bg-info">Chưa hoạt động</span>
                                        @elseif($isExpired)
                                            <span class="badge bg-danger">Hết hạn</span>
                                        @else
                                            <span class="badge bg-success">Đang hoạt động</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Ngày tạo</th>
                                    <td>{{ $promotion->created_at->format('d/m/Y H:i:s') }}</td>
                                </tr>
                                <tr>
                                    <th>Ngày cập nhật</th>
                                    <td>{{ $promotion->updated_at->format('d/m/Y H:i:s') }}</td>
                                </tr>
                            </table>
                        </div>

                        <!-- Danh sách tickets sử dụng voucher -->
                        <div class="col-md-6">
                            <h5 class="mb-3">Lịch sử sử dụng</h5>
                            @if($promotion->tickets && $promotion->tickets->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-bordered table-sm">
                                        <thead>
                                            <tr>
                                                <th>ID Ticket</th>
                                                <th>Người dùng</th>
                                                <th>Ngày sử dụng</th>
                                                <th>Tổng tiền</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($promotion->tickets as $ticket)
                                                <tr>
                                                    <td>#{{ $ticket->id }}</td>
                                                    <td>{{ $ticket->user->name ?? 'N/A' }}</td>
                                                    <td>{{ $ticket->created_at->format('d/m/Y H:i') }}</td>
                                                    <td>{{ number_format($ticket->total_amount, 0) }}₫</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>Voucher này chưa được sử dụng.
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="mt-4">
                        <a href="{{ route('admin.promotions.edit', $promotion) }}" class="btn btn-warning">
                            <i class="fas fa-edit"></i> Chỉnh sửa voucher
                        </a>
                        <form method="POST" action="{{ route('admin.promotions.destroy', $promotion) }}" 
                              class="d-inline"
                              onsubmit="return confirm('Bạn có chắc chắn muốn xóa voucher này?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-trash"></i> Xóa voucher
                            </button>
                        </form>
                        <a href="{{ route('admin.promotions.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Quay lại danh sách
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

