@extends('app')

@section('content')
    <style>
        .detail-label {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            font-weight: 700;
            color: #94a3b8;
        }

        .detail-value {
            font-size: 13px;
            font-weight: 600;
            color: #1e293b;
        }

        .post-content-area {
            font-size: 14px;
            line-height: 1.6;
            color: #334155;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
        }

        .badge-status-lg {
            font-size: 11px;
            font-weight: 800;
            padding: 6px 14px;
            letter-spacing: 0.02em;
        }

        .btn-action {
            font-size: 12px;
            font-weight: 700;
            padding: 8px 16px;
            text-transform: uppercase;
        }
    </style>

    <div class="container-fluid py-4">

        {{-- HEADER NAVIGATION --}}
        <div class="mb-4 d-flex align-items-center justify-content-between">
            <a href="{{ route('admin.posts.index') }}" class="text-decoration-none text-muted fw-bold"
                style="font-size: 13px;">
                <i class="ri-arrow-left-s-line"></i> QUAY LẠI DANH SÁCH
            </a>
            <div class="d-flex gap-2">
                @if ($post->status === 'pending')
                    <form action="{{ route('admin.posts.updateStatus', $post->id) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" value="active">
                        <button type="submit" class="btn btn-success btn-action shadow-sm">
                            <i class="ri-check-double-line"></i> Duyệt bài này
                        </button>
                    </form>
                @endif

                @if ($post->status !== 'rejected')
                    <button type="button"
                        class="btn btn-{{ $post->status === 'active' ? 'outline-danger' : 'danger' }} btn-action shadow-sm"
                        data-bs-toggle="modal" data-bs-target="#rejectModal">
                        <i class="ri-close-circle-line"></i>
                        {{ $post->status === 'active' ? 'Ẩn bài viết' : 'Từ chối duyệt' }}
                    </button>
                @endif
            </div>
        </div>

        <div class="row">
            {{-- CỘT TRÁI: NỘI DUNG --}}
            <div class="col-lg-8">
                <div class="card shadow-sm border-0 mb-4 rounded-3">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h6 class="mb-0 fw-bold text-dark" style="font-size: 14px;">Nội dung bài viết</h6>
                    </div>
                    <div class="card-body p-4">
                        <div class="post-content-area p-4 rounded-3 shadow-inner">
                            {!! nl2br(e($post->content)) !!}
                        </div>

                        @if ($post->phone_contact)
                            <div class="mt-3 d-inline-flex align-items-center gap-2 px-3 py-2 bg-success-subtle text-success rounded-2 border border-success-subtle"
                                style="font-size: 13px; font-weight: 700;">
                                <i class="ri-phone-fill"></i> Liên hệ: {{ $post->phone_contact }}
                            </div>
                        @endif
                    </div>
                </div>

                <div class="card shadow-sm border-0 mb-4 rounded-3">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h6 class="mb-0 fw-bold text-dark" style="font-size: 14px;">Hình ảnh đính kèm
                            ({{ $post->images->count() }})</h6>
                    </div>
                    <div class="card-body p-4">
                        @if ($post->images->count() > 0)
                            <div class="row g-2">
                                @foreach ($post->images as $image)
                                    <div class="col-md-4">
                                        <a href="{{ asset($image->url) }}" target="_blank"
                                            class="d-block overflow-hidden rounded-3 border">
                                            <img src="{{ asset($image->url) }}" class="img-fluid hover-zoom"
                                                style="height: 180px; width: 100%; object-fit: cover;">
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-5 bg-light rounded-3 text-muted" style="font-size: 13px;">
                                <i class="ri-image-line ri-2x opacity-25"></i>
                                <p class="mt-2 mb-0 italic">Bài viết này không đính kèm hình ảnh</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- CỘT PHẢI: METADATA --}}
            <div class="col-lg-4">
                <div class="card shadow-sm border-0 mb-4 rounded-3">
                    <div class="card-header bg-dark py-3">
                        <h6 class="mb-0 text-white fw-bold" style="font-size: 14px;">Thông tin hệ thống</h6>
                    </div>
                    <div class="card-body p-4">
                        <div class="text-center mb-4">
                            <div class="mb-2">
                                @if ($post->type === 'sale')
                                    <span class="badge rounded-pill bg-warning text-dark px-3 py-2"
                                        style="font-size: 10px; font-weight: 800;">⚡ FLASH SALE</span>
                                @else
                                    <span class="badge rounded-pill bg-info text-white px-3 py-2"
                                        style="font-size: 10px; font-weight: 800;">👥 USER POST</span>
                                @endif
                            </div>
                            <span
                                class="badge badge-status-lg rounded-pill border {{ $post->status === 'active' ? 'bg-success-subtle text-success border-success-subtle' : 'bg-warning-subtle text-warning border-warning-subtle' }}">
                                {{ strtoupper($post->status) }}
                            </span>
                        </div>

                        <div class="space-y-3">
                            <div className="py-2 border-bottom">
                                <label class="detail-label d-block mb-1">Người đăng bài</label>
                                <div class="detail-value">{{ $post->author->name }}</div>
                            </div>
                            <div className="py-2 border-bottom mt-3">
                                <label class="detail-label d-block mb-1">Địa điểm / Sân</label>
                                <div class="detail-value text-primary">
                                    <i class="ri-map-pin-fill opacity-50"></i> {{ $post->venue->name ?? 'Không xác định' }}
                                </div>
                            </div>
                            <div className="py-2 border-bottom mt-3">
                                <label class="detail-label d-block mb-1">Thời gian khởi tạo</label>
                                <div class="detail-value">{{ $post->created_at->format('d/m/Y - H:i:s') }}</div>
                            </div>
                        </div>

                        @if ($post->note)
                            <div class="mt-4 p-3 bg-danger-subtle border border-danger-subtle rounded-3">
                                <label class="detail-label text-danger d-block mb-1">Lý do từ chối/ẩn:</label>
                                <p class="mb-0 text-danger fw-bold" style="font-size: 12px;">{{ $post->note }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- MODAL --}}
        <div class="modal fade" id="rejectModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <form action="{{ route('admin.posts.rejectOrHide', $post->id) }}" method="POST"
                    class="modal-content border-0 shadow-lg">
                    @csrf
                    @method('PATCH')
                    <div class="modal-header bg-danger text-white border-0">
                        <h6 class="modal-title fw-bold">Xác nhận lý do</h6>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <p class="text-muted mb-3" style="font-size: 13px;">Vui lòng nhập lý do cụ thể để người dùng biết
                            tại sao bài viết bị từ chối hoặc bị ẩn.</p>
                        <textarea name="note" class="form-control border-2 shadow-sm" rows="4" style="font-size: 13px;"
                            placeholder="Ví dụ: Hình ảnh không phù hợp, nội dung vi phạm quy định..." required></textarea>
                    </div>
                    <div class="modal-footer border-0 bg-light">
                        <button type="button" class="btn btn-secondary btn-action" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-danger btn-action shadow-sm">Xác nhận thực hiện</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
