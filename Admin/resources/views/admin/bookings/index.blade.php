@extends('app')

@section('content')

    <div class="container-fluid py-4">

        {{-- PHẦN 1: HEADER & BỘ LỌC (ADMIN) --}}
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
            <div>
                <h1 class="h4 fw-bold text-dark mb-1">Quản lý đặt sân (Admin)</h1>
                <p class="text-muted small mb-0">Quản lý toàn bộ vé và doanh thu trên hệ thống.</p>
            </div>

            {{-- Form Tìm kiếm & Chọn Sân --}}
            <div class="mt-3 mt-md-0">
                <form action="{{ route('admin.bookings.index') }}" method="GET" class="d-flex gap-2">
                    {{-- Giữ lại trạng thái hiện tại nếu đang search --}}
                    @if (request('status'))
                        <input type="hidden" name="status" value="{{ request('status') }}">
                    @endif

                    {{-- Chọn Sân (Admin nhìn thấy toàn bộ sân) --}}
                    <select name="venue" class="form-select shadow-sm" style="width: 200px;"
                        onchange="this.form.submit()">
                        <option value="">-- Tất cả sân --</option>
                        @foreach ($venues as $v)
                            <option value="{{ $v->id }}" {{ request('venue') == $v->id ? 'selected' : '' }}>
                                {{ $v->name }}
                            </option>
                        @endforeach
                    </select>

                    {{-- Input tìm kiếm --}}
                    <div class="input-group shadow-sm" style="width: 250px;">
                        <input type="text" name="search" class="form-control" placeholder="Tên khách, SĐT..."
                            value="{{ request('search') }}">
                        <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i></button>
                    </div>
                </form>
            </div>
        </div>

        {{-- PHẦN 2: TAB TRẠNG THÁI --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body py-2 px-3">
                <ul class="nav nav-pills nav-fill gap-2 p-1 bg-light rounded">
                    @php
                        function adminUrlWithStatus($status)
                        {
                            return request()->fullUrlWithQuery(['status' => $status, 'page' => 1]);
                        }
                    @endphp

                    <li class="nav-item">
                        <a class="nav-link {{ !request('status') ? 'active bg-white text-primary shadow-sm' : 'text-muted' }}"
                            href="{{ adminUrlWithStatus(null) }}">
                            <i class="fas fa-list me-1"></i> Tất cả
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request('status') == 'pending' ? 'active bg-warning text-dark shadow-sm' : 'text-muted' }}"
                            href="{{ adminUrlWithStatus('pending') }}">
                            <i class="fas fa-clock me-1"></i> Chờ thanh toán
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request('status') == 'completed' ? 'active bg-success text-white shadow-sm' : 'text-muted' }}"
                            href="{{ adminUrlWithStatus('completed') }}">
                            <i class="fas fa-check-circle me-1"></i> Hoàn thành
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request('status') == 'canceled' ? 'active bg-danger text-white shadow-sm' : 'text-muted' }}"
                            href="{{ adminUrlWithStatus('canceled') }}">
                            <i class="fas fa-times-circle me-1"></i> Đã hủy
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        {{-- PHẦN 3: DANH SÁCH BOOKING --}}
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr class="text-secondary small fw-bold text-uppercase text-center">
                                <th style="width: 80px;">ID Vé</th>
                                <th class="text-start">Khách hàng</th>
                                <th class="text-start">Sân / Thời gian</th>
                                <th class="text-end" style="width: 150px;">Tổng tiền</th>
                                <th style="width: 140px;">Trạng thái</th>
                                <th style="width: 100px;">Chi tiết</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($tickets as $ticket)
                                {{-- LOGIC ADMIN: Không cần lọc owner, lấy toàn bộ item --}}
                                @php
                                    $allItems = $ticket->items;
                                    $firstItem = $allItems->first();
                                    $itemCount = $allItems->count();
                                @endphp

                                <tr>
                                    {{-- Cột ID --}}
                                    <td class="text-center text-muted fw-bold">{{ $ticket->id }}</td>

                                    {{-- Cột Khách hàng --}}
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex justify-content-center align-items-center me-3"
                                                style="width: 40px; height: 40px;">
                                                <i class="fas fa-user"></i>
                                            </div>
                                            <div>
                                                <span
                                                    class="d-block fw-bold text-dark">{{ $ticket->user->name ?? 'Khách vãng lai' }}</span>
                                                <small class="text-muted">{{ $ticket->user->phone ?? '---' }}</small>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Cột Thông tin sân --}}
                                    <td>
                                        @if ($firstItem)
                                            <div class="fw-bold text-dark mb-1">
                                                {{ $firstItem->booking->court->venue->name }}
                                                <span class="fw-normal text-muted">-
                                                    {{ $firstItem->booking->court->name }}</span>
                                            </div>
                                            <div class="small text-muted">
                                                <i class="far fa-calendar-alt me-1"></i>
                                                {{ \Carbon\Carbon::parse($firstItem->booking->booking_date)->format('d/m/Y') }}
                                                <span class="mx-1">|</span>
                                                <i class="far fa-clock me-1"></i>
                                                {{ \Carbon\Carbon::parse($firstItem->booking->timeSlot->start_time)->format('H:i') }}
                                                -
                                                {{ \Carbon\Carbon::parse($firstItem->booking->timeSlot->end_time)->format('H:i') }}
                                            </div>
                                            @if ($itemCount > 1)
                                                <span class="badge bg-secondary-subtle text-secondary rounded-pill mt-1">
                                                    +{{ $itemCount - 1 }} sân khác
                                                </span>
                                            @endif
                                        @else
                                            <span class="text-muted">Không có dữ liệu sân</span>
                                        @endif
                                    </td>

                                    {{-- Cột Tổng tiền (Admin lấy thẳng từ ticket) --}}
                                    <td class="text-end">
                                        <span class="fw-bold text-success fs-6">
                                            {{ number_format($ticket->total_amount, 0, ',', '.') }} đ
                                        </span>
                                    </td>

                                    {{-- Cột Trạng thái --}}
                                    <td class="text-center">
                                        @php
                                            $statusMap = [
                                                'pending' => [
                                                    'bg' => 'bg-warning-subtle',
                                                    'text' => 'text-warning-emphasis',
                                                    'label' => 'Chờ xử lý',
                                                ],
                                                'completed' => [
                                                    'bg' => 'bg-success-subtle',
                                                    'text' => 'text-success-emphasis',
                                                    'label' => 'Hoàn thành',
                                                ],
                                                'canceled' => [
                                                    'bg' => 'bg-danger-subtle',
                                                    'text' => 'text-danger-emphasis',
                                                    'label' => 'Đã hủy',
                                                ],
                                                'confirmed' => [
                                                    'bg' => 'bg-primary-subtle',
                                                    'text' => 'text-primary-emphasis',
                                                    'label' => 'Đã xác nhận',
                                                ],
                                            ];
                                            // Fallback nếu status không khớp
                                            $s = $statusMap[$ticket->status] ?? [
                                                'bg' => 'bg-secondary-subtle',
                                                'text' => 'text-secondary-emphasis',
                                                'label' => ucfirst($ticket->status),
                                            ];
                                        @endphp
                                        <span
                                            class="badge {{ $s['bg'] }} {{ $s['text'] }} border border-opacity-10 rounded-pill px-3 py-2">
                                            {{ $s['label'] }}
                                        </span>
                                    </td>

                                    {{-- Cột Hành động --}}
                                    <td class="text-center">
                                        {{-- Admin chỉ có nút Xem, không sửa xóa --}}
                                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal"
                                            data-bs-target="#ticketModal{{ $ticket->id }}">
                                            Xem
                                        </button>
                                    </td>
                                </tr>

                                {{-- MODAL CHI TIẾT (ADMIN VIEW) --}}
                                <div class="modal fade" id="ticketModal{{ $ticket->id }}" tabindex="-1"
                                    aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title fw-bold">Chi tiết vé #{{ $ticket->id }}</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="d-flex justify-content-between mb-3 border-bottom pb-2">
                                                    <span>Khách hàng:
                                                        <strong>{{ $ticket->user->name ?? 'N/A' }}</strong></span>
                                                    <span>{{ $ticket->created_at->format('H:i d/m/Y') }}</span>
                                                </div>

                                                <h6 class="text-uppercase text-muted small fw-bold mb-3">Danh sách sân đặt
                                                </h6>

                                                <div class="list-group list-group-flush">
                                                    @foreach ($allItems as $item)
                                                        <div
                                                            class="list-group-item px-0 d-flex justify-content-between align-items-center">
                                                            <div>
                                                                <div class="fw-bold">
                                                                    {{ $item->booking->court->venue->name }}
                                                                </div>
                                                                <small class="text-muted d-block">
                                                                    {{ $item->booking->court->name }} -
                                                                    {{ \Carbon\Carbon::parse($item->booking->booking_date)->format('d/m') }}
                                                                    ({{ \Carbon\Carbon::parse($item->booking->timeSlot->start_time)->format('H:i') }}
                                                                    -
                                                                    {{ \Carbon\Carbon::parse($item->booking->timeSlot->end_time)->format('H:i') }})
                                                                </small>
                                                                {{-- Admin thấy thêm thông tin Chủ sân --}}
                                                                <small class="text-primary fst-italic"
                                                                    style="font-size: 0.75rem;">
                                                                    Owner:
                                                                    {{ $item->booking->court->venue->owner->name ?? 'N/A' }}
                                                                </small>
                                                            </div>
                                                            <span class="fw-bold text-primary">
                                                                {{ number_format($item->price, 0, ',', '.') }} đ
                                                            </span>
                                                        </div>
                                                    @endforeach
                                                </div>

                                                <div
                                                    class="mt-3 pt-2 border-top d-flex justify-content-between align-items-center">
                                                    <span class="h6 mb-0">Tổng giá trị:</span>
                                                    <span class="h5 mb-0 text-success fw-bold">
                                                        {{ number_format($ticket->total_amount, 0, ',', '.') }} đ
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary"
                                                    data-bs-dismiss="modal">Đóng</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5">
                                        <div class="text-muted opacity-50">
                                            <i class="fas fa-clipboard-list fa-3x mb-3"></i>
                                            <h5>Không có dữ liệu đặt sân</h5>
                                            <p class="small">Hệ thống chưa ghi nhận đơn nào.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Phân trang --}}
            @if ($tickets->hasPages())
                <div class="card-footer bg-white border-0 py-3">
                    <div class="d-flex justify-content-center">
                        {{ $tickets->appends(request()->query())->links() }}
                    </div>
                </div>
            @endif
        </div>

    </div>
@endsection
