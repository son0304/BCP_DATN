@extends('app')

@section('content')
<div class="container-fluid py-4">

    {{-- HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h4 mb-0 text-primary">Chi tiết bài viết</h1>
            <p class="text-muted mb-0">
                Tiêu đề: <strong>{{ $post->title }}</strong>
            </p>
        </div>

        <div class="d-flex gap-2">

            {{-- PENDING: Duyệt --}}
            @if (!$post->is_active && !$post->note)
            <form action="{{ route('admin.posts.updateStatus', $post->id) }}" method="POST">
                @csrf
                @method('PATCH')
                <input type="hidden" name="is_active" value="1">
                <button type="submit" class="btn btn-success">
                    Duyệt
                </button>
            </form>
            @endif

            {{-- PENDING hoặc ĐÃ DUYỆT: Từ chối / Ẩn --}}
            @if (!$post->note)
            <button type="button"
                class="btn btn-{{ $post->is_active ? 'warning' : 'danger' }}"
                data-bs-toggle="modal"
                data-bs-target="#rejectModal">
                {{ $post->is_active ? 'Ẩn' : 'Từ chối' }}
            </button>
            @endif

            <a href="{{ route('admin.posts.index') }}" class="btn btn-outline-secondary">
                Quay lại
            </a>
        </div>


    </div>

    <div class="row">

        {{-- CỘT CHÍNH --}}
        <div class="col-lg-8">

            {{-- Thông tin bài viết --}}
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header py-3">
                    <h5 class="mb-0 text-primary">Thông tin bài viết</h5>
                </div>
                <div class="card-body">

                    <dl class="row mb-0">
                        <dt class="col-sm-3">Tiêu đề:</dt>
                        <dd class="col-sm-9">{{ $post->title }}</dd>

                        <dt class="col-sm-3">Tác giả:</dt>
                        <dd class="col-sm-9">
                            {{ $post->author->name ?? 'N/A' }}
                            ({{ $post->author->email ?? '' }})
                        </dd>

                        <dt class="col-sm-3">Tags:</dt>
                        <dd class="col-sm-9">
                            @if($post->tag)
                            <span class="badge bg-info text-white">
                                <i class="bi bi-tag-fill me-1"></i> {{ $post->tag->name }}
                            </span>
                            @else
                            <span class="text-muted italic">Không có tag</span>
                            @endif
                        </dd>

                        <dt class="col-sm-3">Trạng thái:</dt>
                        <dd class="col-sm-9">
                            <span class="badge {{ $post->status_label['class'] }}">
                                {{ $post->status_label['text'] }}
                            </span>
                        </dd>

                        <dt class="col-sm-3">Ngày tạo:</dt>
                        <dd class="col-sm-9">
                            {{ $post->created_at->format('d/m/Y H:i') }}
                        </dd>
                    </dl>

                </div>
            </div>

            {{-- Nội dung --}}
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header py-3">
                    <h5 class="mb-0 text-primary">Nội dung</h5>
                </div>
                <div class="card-body">
                    <div class="post-content">
                        {!! nl2br(e($post->content)) !!}
                    </div>
                </div>
            </div>

        </div>

        {{-- CỘT PHẢI --}}
        <div class="col-lg-4">

            {{-- Thumbnail --}}
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header py-3">
                    <h5 class="mb-0 text-primary">Hình ảnh</h5>
                </div>
                <div class="card-body">

                    @if ($post->images && $post->images->count() > 0)
                    <div id="postCarousel" class="carousel slide" data-bs-ride="carousel">
                        <div class="carousel-inner rounded shadow-sm">
                            @foreach ($post->images as $key => $image)
                            <div class="carousel-item {{ $key == 0 ? 'active' : '' }}">
                                <img src="{{ asset($image->url) }}"
                                    class="d-block w-100"
                                    style="height: 350px; object-fit: cover;"
                                    alt="Post image">
                            </div>
                            @endforeach
                        </div>

                        @if ($post->images->count() > 1)
                        <button class="carousel-control-prev" type="button" data-bs-target="#postCarousel" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#postCarousel" data-bs-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        </button>
                        @endif
                    </div>
                    @else
                    <div class="text-center py-4 border rounded bg-light">
                        <p class="text-muted mb-0">Chưa có ảnh nào</p>
                    </div>
                    @endif

                </div>
            </div>

        </div>

    </div>

    <div class="modal fade" id="rejectModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <form action="{{ route('admin.posts.rejectOrHide', $post->id) }}"
                method="POST" class="modal-content">
                @csrf
                @method('PATCH')

                <div class="modal-header">
                    <h5 class="modal-title text-danger">
                        {{ $post->is_active ? 'Ẩn bài viết' : 'Từ chối bài viết' }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">
                            Lý do <span class="text-danger">*</span>
                        </label>
                        <textarea name="note"
                            class="form-control"
                            rows="4"
                            placeholder="Nhập lý do..."
                            required></textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Hủy
                    </button>
                    <button type="submit" class="btn btn-danger">
                        Xác nhận
                    </button>
                </div>
            </form>
        </div>
    </div>



</div>
@endsection