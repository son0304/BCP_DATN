@extends('app')
@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="card-title">Chi tiết Người dùng: {{ $user->name }}</h3>
                        <div>
                            <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-warning">
                                <i class="fas fa-edit"></i> Chỉnh sửa
                            </a>
                            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Quay lại
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row">
                        <!-- Thông tin cơ bản -->
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">Thông tin cơ bản</h5>
                                </div>
                                <div class="card-body">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td><strong>ID:</strong></td>
                                            <td>{{ $user->id }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Tên:</strong></td>
                                            <td>{{ $user->name }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Email:</strong></td>
                                            <td>{{ $user->email }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Số điện thoại:</strong></td>
                                            <td>{{ $user->phone ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Vai trò:</strong></td>
                                            <td>
                                                @if($user->role->name === 'Admin')
                                                <span class="badge badge-danger">
                                                    <i class="fas fa-crown"></i> {{ $user->role->name }}
                                                </span>
                                                @elseif($user->role->name === 'Manager')
                                                <span class="badge badge-warning">
                                                    <i class="fas fa-user-tie"></i> {{ $user->role->name }}
                                                </span>
                                                @elseif($user->role->name === 'Owner')
                                                <span class="badge badge-success">
                                                    <i class="fas fa-key"></i> {{ $user->role->name }}
                                                </span>
                                                @else
                                                <span class="badge badge-info">
                                                    <i class="fas fa-user"></i> {{ $user->role->name }}
                                                </span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Trạng thái:</strong></td>
                                            <td>
                                                <span class="badge {{ $user->is_active ? 'badge-success' : 'badge-danger' }}">
                                                    {{ $user->is_active ? 'Hoạt động' : 'Không hoạt động' }}
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Ngày tạo:</strong></td>
                                            <td>{{ $user->created_at->format('d/m/Y H:i:s') }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Cập nhật lần cuối:</strong></td>
                                            <td>{{ $user->updated_at->format('d/m/Y H:i:s') }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Thông tin địa chỉ -->
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">Thông tin địa chỉ</h5>
                                </div>
                                <div class="card-body">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td><strong>Tỉnh/Thành phố:</strong></td>
                                            <td>{{ $user->province->name ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Quận/Huyện:</strong></td>
                                            <td>{{ $user->district->name ?? 'N/A' }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Danh sách đặt sân gần đây -->
                    @if($tickets->count() > 0)
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">Đơn đặt sân gần đây ({{ $tickets->total() }} đơn)</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <thead class="text-center">
                                                <tr>
                                                    <th>Mã đơn (Ticket ID)</th>
                                                    <th>Thương hiệu</th>
                                                    <th>Chi tiết đặt (Sân - Khung giờ)</th>
                                                    <th>Tổng tiền</th>
                                                    <th>Ngày tạo đơn</th>
                                                    <th>Trạng thái</th>
                                                </tr>
                                            </thead>
                                            <tbody class="text-center">
                                                @foreach($tickets as $ticket)
                                                <tr>
                                                    <td>#{{ $ticket->id }}</td>

                                                    <td>
                                                        @if($ticket->items->isNotEmpty())
                                                        {{ $ticket->items->first()->booking->court->venue->name ?? 'N/A' }}
                                                        @else
                                                        <span class="text-muted">N/A</span>
                                                        @endif
                                                    </td>

                                                    <td>
                                                        @foreach($ticket->items as $item)
                                                        <div class="small mb-1">
                                                            <strong>{{ $item->booking->court->name ?? 'Sân?' }}</strong> -
                                                            {{ \Carbon\Carbon::parse($item->booking->date)->format('d/m') }}
                                                            <br>
                                                            @if($item->booking->timeSlot)
                                                            ({{ \Carbon\Carbon::parse($item->booking->timeSlot->start_time)->format('H:i') }} -
                                                            {{ \Carbon\Carbon::parse($item->booking->timeSlot->end_time)->format('H:i') }})
                                                            @endif
                                                        </div>
                                                        @endforeach
                                                    </td>

                                                    <td>{{ number_format($ticket->total_amount, 0, ',', '.') }} ₫</td>

                                                    <td>{{ $ticket->created_at->format('d/m/Y H:i') }}</td>

                                                    <td>
                                                        @php
                                                        $status = trim(strtolower($ticket->status));
                                                        $statusLabel = $status;
                                                        $statusClass = 'badge-secondary';

                                                        switch ($status) {
                                                        case 'pending':
                                                        $statusLabel = 'Chờ xác nhận';
                                                        $statusClass = 'badge-warning';
                                                        break;
                                                        case 'confirmed':
                                                        $statusLabel = 'Đã xác nhận';
                                                        $statusClass = 'badge-success';
                                                        break;
                                                        case 'completed':
                                                        $statusLabel = 'Hoàn thành';
                                                        $statusClass = 'badge-primary';
                                                        break;
                                                        case 'cancelled':
                                                        $statusLabel = 'Đã hủy';
                                                        $statusClass = 'badge-danger';
                                                        break;
                                                        }
                                                        @endphp
                                                        <span class="badge {{ $statusClass }}">{{ $statusLabel }}</span>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="d-flex justify-content-center mt-3">
                                        {{ $tickets->links() }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Danh sách địa điểm sở hữu -->
                    @if($venues->count() > 0)
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">Thương hiệu sở hữu ({{ $venues->total() }})</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <thead class="text-center">
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Tên thương hiệu</th>
                                                    <th>Địa chỉ</th>
                                                    <th>Số sân</th>
                                                    <th>Trạng thái</th>
                                                </tr>
                                            </thead>
                                            <tbody class="text-center">
                                                @foreach($venues as $venue)
                                                <tr>
                                                    <td>{{ $venue->id }}</td>
                                                    <td>{{ $venue->name }}</td>
                                                    <td>
                                                        {{ $venue->address_detail }},
                                                        {{ $venue->district->name ?? '' }},
                                                        {{ $venue->province->name ?? '' }}
                                                    </td>

                                                    <td>{{ $venue->courts->count() }}</td>
                                                    <td>
                                                        <span class="badge {{ $venue->is_active ? 'badge-success' : 'badge-danger' }}">
                                                            {{ $venue->is_active ? 'Hoạt động' : 'Không hoạt động' }}
                                                        </span>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>

                                    <!-- Pagination for venues -->
                                    <div class="d-flex justify-content-center mt-3">
                                        {{ $venues->links() }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection