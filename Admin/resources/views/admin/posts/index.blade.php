@extends('app')

@section('content')
    <style>
        /* CSS tinh chỉnh để đồng bộ kích thước */
        .table thead th {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            font-weight: 700;
            color: #6c757d;
            border-top: none;
        }

        .table tbody td {
            font-size: 13px;
            color: #495057;
            vertical-align: middle;
        }

        .badge-custom {
            font-size: 10px;
            font-weight: 700;
            padding: 4px 8px;
            border-radius: 4px;
            text-transform: uppercase;
        }

        .text-preview {
            max-width: 280px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            font-size: 13px;
        }

        .btn-detail {
            font-size: 11px;
            font-weight: 700;
            padding: 4px 10px;
        }

        .stats-badge {
            font-size: 11px;
            padding: 5px 12px;
            font-weight: 600;
        }
    </style>

    <div class="container-fluid py-4">
        <div class="card shadow-sm border-0 rounded-3">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0 fw-bold text-dark" style="font-size: 16px;">Quản lý bài viết hệ thống</h5>
                    <p class="text-muted mb-0" style="font-size: 12px;">Theo dõi và duyệt các nội dung từ người dùng và sân
                        đấu</p>
                </div>
                <div class="d-flex gap-2">
                    <span class="badge stats-badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill">
                        Sale: {{ $posts->where('type', 'sale')->count() }}
                    </span>
                    <span class="badge stats-badge bg-info-subtle text-info border border-info-subtle rounded-pill">
                        Cộng đồng: {{ $posts->where('type', 'user_post')->count() }}
                    </span>
                </div>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4 text-center" style="width: 50px;">#</th>
                                <th style="width: 120px;">Loại bài</th>
                                <th style="min-width: 250px;">Nội dung bài viết</th>
                                <th>Tác giả</th>
                                <th>Sân (Venue)</th>
                                <th class="text-center">Trạng thái</th>
                                <th class="text-center">Ngày tạo</th>
                                <th class="text-end pe-4">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($posts as $post)
                                <tr>
                                    <td class="text-center ps-4 text-muted">{{ $loop->iteration }}</td>
                                    <td>
                                        @if ($post->type === 'sale')
                                            <span class="badge-custom"
                                                style="background-color: #fffbeb; color: #9a3412; border: 1px solid #fed7aa;">
                                                <i class="ri-flashlight-fill"></i> Sale
                                            </span>
                                        @else
                                            <span class="badge-custom"
                                                style="background-color: #f0fdf4; color: #166534; border: 1px solid #bbf7d0;">
                                                <i class="ri-team-line"></i> User
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="text-preview text-dark fw-medium">
                                            {{ $post->content }}
                                        </div>
                                    </td>
                                    <td>
                                        <span class="fw-bold"
                                            style="color: #334155;">{{ $post->author->name ?? 'N/A' }}</span>
                                    </td>
                                    <td>
                                        @if ($post->venue)
                                            <div class="d-flex align-items-center gap-1 text-primary fw-semibold"
                                                style="font-size: 12px;">
                                                <i class="ri-map-pin-2-fill opacity-50"></i> {{ $post->venue->name }}
                                            </div>
                                        @else
                                            <span class="text-muted" style="font-size: 11px;">Không xác định</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @php
                                            $statusStyle = [
                                                'pending' => 'bg-warning-subtle text-warning border-warning-subtle',
                                                'active' => 'bg-success-subtle text-success border-success-subtle',
                                                'rejected' => 'bg-danger-subtle text-danger border-danger-subtle',
                                            ];
                                        @endphp
                                        <span
                                            class="badge rounded-pill border {{ $statusStyle[$post->status] ?? 'bg-secondary' }}"
                                            style="font-size: 10px; padding: 4px 10px;">
                                            {{ strtoupper($post->status) }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="text-muted"
                                            style="font-size: 12px;">{{ $post->created_at->format('d/m/Y') }}</span><br>
                                        <small class="text-muted opacity-50"
                                            style="font-size: 10px;">{{ $post->created_at->format('H:i') }}</small>
                                    </td>
                                    <td class="text-end pe-4">
                                        <a href="{{ route('admin.posts.show', $post) }}"
                                            class="btn btn-light border btn-detail hover-shadow">
                                            <i class="ri-eye-line me-1"></i> CHI TIẾT
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-5 text-muted small">Chưa có dữ liệu bài viết
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if ($posts->hasPages())
                <div class="card-footer bg-white border-top-0 py-3">
                    {{ $posts->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
