@extends('app')

@section('content')
<div class="container-fluid py-4">

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h4 class="mb-0 text-primary fw-bold">Danh sách bài viết</h4>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 text-center">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Tiêu đề</th>
                            <th>Tác giả</th>
                            <th>Tag</th>
                            <th>Trạng thái</th>
                            <th>Ghi chú</th>
                            <th>Ngày tạo</th>
                            <th class="text-end">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($posts as $post)
                        <tr>
                            <td>{{ $loop->iteration }}</td>

                            <td>
                                <div class="fw-semibold">{{ $post->title }}</div>
                            </td>

                            <td>
                                <span class="badge bg-info-subtle text-info">
                                    {{ $post->author->name ?? 'N/A' }}
                                </span>
                            </td>

                            <td>
                                @foreach ($post->tags as $tag)
                                <span class="badge bg-secondary">{{ $tag->name }}</span>
                                @endforeach
                            </td>

                            <td>
                                <span class="badge {{ $post->status_label['class'] }}">
                                    {{ $post->status_label['text'] }}
                                </span>
                            </td>

                            <td>
                                @if ($post->note)
                                <span class="text-danger small">
                                    {{ str_replace('[CANCELLED]', '', $post->note) }}
                                </span>
                                @else
                                <span class="text-muted">—</span>
                                @endif
                            </td>

                            <td>
                                {{ $post->created_at->format('d/m/Y') }}
                            </td>

                            <td class="text-end">
                                <a href="{{ route('admin.posts.show', $post) }}"
                                    class="btn btn-sm btn-outline-info">
                                    <i class="ri-eye-line"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                Chưa có bài viết nào
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if ($posts->hasPages())
        <div class="card-footer bg-white">
            {{ $posts->links() }}
        </div>
        @endif
    </div>

</div>
@endsection