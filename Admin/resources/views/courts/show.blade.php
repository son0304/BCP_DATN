@extends('app')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Chi tiết sân: {{ $court->name }}</h1>
            <div>
                <a href="{{ route('admin.courts.edit', $court) }}" class="btn btn-warning">Chỉnh sửa</a>
                <a href="{{ route('admin.courts.index') }}" class="btn btn-secondary">Quay lại</a>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>Thông tin cơ bản</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>Tên sân:</strong></td>
                                <td>{{ $court->name }}</td>
                            </tr>
                            <tr>
                                <td><strong>Địa điểm:</strong></td>
                                <td>{{ $court->venue->name ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Loại hình:</strong></td>
                                <td>{{ $court->venueType->name ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Loại sân:</strong></td>
                                <td>
                                    <span class="badge {{ $court->is_indoor ? 'bg-primary' : 'bg-success' }}">
                                        {{ $court->is_indoor ? 'Trong nhà' : 'Ngoài trời' }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Bề mặt sân:</strong></td>
                                <td>{{ $court->surface ?? 'Chưa cập nhật' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Ngày tạo:</strong></td>
                                <td>{{ $court->created_at->format('d/m/Y H:i') }}</td>
                            </tr>
                            <tr>
                                <td><strong>Cập nhật lần cuối:</strong></td>
                                <td>{{ $court->updated_at->format('d/m/Y H:i') }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>Lịch hoạt động (30 ngày tới)</h5>
                    </div>
                    <div class="card-body">
                        @if($availabilities->count() > 0)
                            <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                                <table class="table table-sm">
                                    <thead class="sticky-top bg-light">
                                        <tr>
                                            <th>Ngày</th>
                                            <th>Khung giờ</th>
                                            <th>Giá (VNĐ)</th>
                                            <th>Trạng thái</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($availabilities as $date => $dayAvailabilities)
                                            @foreach($dayAvailabilities as $availability)
                                                <tr>
                                                    <td>{{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}</td>
                                                    <td>
                                                        {{ date('H:i', strtotime($availability->timeSlot->start_time)) }} - 
                                                        {{ date('H:i', strtotime($availability->timeSlot->end_time)) }}
                                                    </td>
                                                    <td>{{ number_format($availability->price) }}</td>
                                                    <td>
                                                        <span class="badge {{ $availability->status == 'open' ? 'bg-success' : 'bg-danger' }}">
                                                            {{ $availability->status == 'open' ? 'Mở' : 'Đóng' }}
                                                        </span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="text-muted">Chưa có lịch hoạt động nào được thiết lập.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

