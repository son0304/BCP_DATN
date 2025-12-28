@extends('app')

@section('content')
    <div class="container-fluid py-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="h4 mb-0 fw-bold">Quản lý thương hiệu sân</h1>
                        <p class="text-muted mb-0 small">Danh sách tất cả các sân trong hệ thống.</p>
                    </div>
                </div>
            </div>

            <div class="card-body p-0">


                <div class="table-responsive">
                    <table class="table table-hover table-bordered align-middle mb-0">
                        <thead class="table-light text-center">
                            <tr>
                                <th style="width: 100px;">ID</th>
                                <th>Tên sân</th>
                                <th>Chủ sở hữu</th>
                                <th>Địa điểm</th>
                                <th class="text-center" style="width: 130px;">Giờ mở cửa</th>
                                <th class="text-center" style="width: 130px;">Giờ đóng cửa</th>
                                <th class="text-center" style="width: 140px;">Trạng thái</th>
                                <th style="width: 140px;">Hành động</th>
                            </tr>
                        </thead>
                        <tbody class="text-center">
                            @forelse($venues as $venue)
                                <tr id="venue-row-{{ $venue->id }}">

                                    <td class="fw-semibold">{{ $venue->id }}</td>
                                    <td>
                                        <strong class="text-dark d-block">{{ $venue->name }}</strong>
                                        <small class="text-muted">{{ $venue->phone ?? 'Chưa có SĐT' }}</small>
                                    </td>
                                    <td>{{ $venue->owner->name ?? 'N/A' }}</td>
                                    <td>{{ $venue->province->name ?? 'N/A' }}</td>

                                    <td>
                                        @if ($venue->start_time)
                                            <span
                                                class="badge bg-primary-subtle border border-primary-subtle text-primary-emphasis rounded-pill px-3 py-2">
                                                {{ \Carbon\Carbon::parse($venue->start_time)->format('H:i') }}
                                            </span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>

                                    <td>
                                        @if ($venue->end_time)
                                            <span
                                                class="badge bg-warning-subtle border border-warning-subtle text-warning-emphasis rounded-pill px-3 py-2">
                                                {{ \Carbon\Carbon::parse($venue->end_time)->format('H:i') }}
                                            </span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($venue->is_active == 1)
                                            <span
                                                class="badge bg-success-subtle border border-success-subtle text-success-emphasis rounded-pill px-3 py-2">
                                                Hoạt động
                                            </span>
                                        @else
                                            <span
                                                class="badge bg-danger-subtle border border-danger-subtle text-danger-emphasis rounded-pill px-3 py-2">
                                                Đã khóa
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.venues.show', $venue->id) }}"
                                            class="btn btn-outline-primary btn-sm me-2">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-5">
                                        <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
                                        <h5 class="mb-1">Không tìm thấy thương hiệu sân nào</h5>
                                        <p class="text-muted">Hãy bắt đầu bằng cách thêm một sân mới.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Phân trang --}}
            @if ($venues->hasPages())
                <div class="card-footer bg-white border-0 py-3">
                    <div class="d-flex justify-content-center">
                        {{ $venues->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // --- HÀM HỖ TRỢ: Tạo nội dung bên trong dòng TR ---
        function getVenueRowContent(venue) {
            // Dùng Optional Chaining (?.) hoặc kiểm tra để tránh lỗi JS nếu owner/province null
            const ownerName = (venue.owner && venue.owner.name) ? venue.owner.name : 'N/A';
            const provinceName = (venue.province && venue.province.name) ? venue.province.name : 'N/A';
            const phone = venue.phone ? venue.phone : 'Chưa có SĐT';

            // Format giờ (lấy 5 ký tự đầu HH:mm)
            const start = venue.start_time ? venue.start_time.substring(0, 5) : null;
            const end = venue.end_time ? venue.end_time.substring(0, 5) : null;

            const startTimeDisplay = start ?
                `<span class="badge bg-primary-subtle border border-primary-subtle text-primary-emphasis rounded-pill px-3 py-2">${start}</span>` :
                '<span class="text-muted">—</span>';

            const endTimeDisplay = end ?
                `<span class="badge bg-warning-subtle border border-warning-subtle text-warning-emphasis rounded-pill px-3 py-2">${end}</span>` :
                '<span class="text-muted">—</span>';

            const statusBadge = venue.is_active == 1 ?
                `<span class="badge bg-success-subtle border border-success-subtle text-success-emphasis rounded-pill px-3 py-2">Hoạt động</span>` :
                `<span class="badge bg-danger-subtle border border-danger-subtle text-danger-emphasis rounded-pill px-3 py-2">Đã khóa</span>`;

            return `
            <td class="fw-semibold">${venue.id}</td>
            <td>
                <strong class="text-dark d-block">${venue.name}</strong>
                <small class="text-muted">${phone}</small>
            </td>
            <td>${ownerName}</td>
            <td>${provinceName}</td>
            <td>${startTimeDisplay}</td>
            <td>${endTimeDisplay}</td>
            <td>${statusBadge}</td>
            <td>
                <a href="/admin/venues/${venue.id}" class="btn btn-outline-primary btn-sm me-2">
                    <i class="fas fa-eye"></i>
                </a>
            </td>
        `;
        }

        const venueChannel = Echo.channel('venues');

        // 1. LẮNG NGHE TẠO MỚI (CREATED)
        venueChannel.listen('.venue.created', (e) => {
            const venue = e.data;
            const tbody = document.querySelector('table tbody');

            const emptyRow = tbody.querySelector('td[colspan]');
            if (emptyRow) emptyRow.parentElement.remove();

            const newRowHtml = `
            <tr id="venue-row-${venue.id}" class="animate__animated animate__fadeIn">
                ${getVenueRowContent(venue)}
            </tr>`;

            tbody.insertAdjacentHTML('afterbegin', newRowHtml);

            const row = document.getElementById(`venue-row-${venue.id}`);
            row.style.backgroundColor = '#e8f5e9';
            setTimeout(() => row.style.backgroundColor = '', 2000);
        });

        // 2. LẮNG NGHE CẬP NHẬT (UPDATED) - ĐÃ SỬA LẠI CHUẨN
        venueChannel.listen('.venue.updated', (e) => {
            console.log("Dữ liệu nhận được:", e);
            const venue = e.data;

            // Tìm dòng TR dựa vào ID mà ta đã thêm ở Blade
            const row = document.getElementById(`venue-row-${venue.id}`);

            if (row) {
                // Thay thế nội dung bên trong dòng TR bằng dữ liệu mới
                row.innerHTML = getVenueRowContent(venue);

                // Hiệu ứng highlight màu vàng nhạt
                row.style.transition = "background-color 0.5s ease";
                row.style.backgroundColor = '#fff9c4';
                setTimeout(() => row.style.backgroundColor = '', 2000);

                console.log("Đã cập nhật giao diện dòng số: " + venue.id);
            } else {
                console.warn("Không tìm thấy dòng HTML để cập nhật: venue-row-" + venue.id);
            }
        });

        // 3. LẮNG NGHE XÓA (DELETED)
        venueChannel.listen('.venue.deleted', (e) => {
            const venueId = e.id;
            const row = document.getElementById(`venue-row-${venueId}`);

            if (row) {
                row.classList.add('animate__animated', 'animate__fadeOutRight');
                setTimeout(() => {
                    row.remove();
                    const tbody = document.querySelector('table tbody');
                    if (tbody.children.length === 0) {
                        tbody.innerHTML = '<tr><td colspan="8" class="text-center py-5">Trống.</td></tr>';
                    }
                }, 800);
            }
        });
    </script>
@endpush
