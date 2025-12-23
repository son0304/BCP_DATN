@extends('app')
@section('content')

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">

            <div class="card">
                <!-- Header -->
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">Quản lý Tags</h3>
                    <a href="{{ route('admin.tags.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i> Tạo mới
                    </a>
                </div>

                <div class="card-body">

                    <!-- Alerts -->
                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show">
                            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible fade show">
                            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <!-- Table -->
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered align-middle mb-0">
                            <thead class="table-light">
                                <tr class="text-center">
                                    <th width="80">ID</th>
                                    <th>Tên tag</th>
                                    <th width="180">Ngày tạo</th>
                                    <th width="180">Cập nhật</th>
                                    <th width="160">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($tags as $tag)
                                    <tr class="text-center">
                                        <td>{{ $tag->id }}</td>
                                        <td>
                                            <span class="badge bg-secondary fs-6">
                                                {{ $tag->name }}
                                            </span>
                                        </td>
                                        <td>{{ $tag->created_at->format('d/m/Y H:i') }}</td>
                                        <td>{{ $tag->updated_at->format('d/m/Y H:i') }}</td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('admin.tags.edit', $tag) }}"
                                                   class="btn btn-outline-warning btn-sm me-2"
                                                   title="Sửa">
                                                    <i class="fas fa-edit"></i>
                                                </a>

                                                <form method="POST"
                                                      action="{{ route('admin.tags.destroy', $tag) }}"
                                                      class="d-inline"
                                                      onsubmit="return confirm('Bạn có chắc chắn muốn xóa tag này?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                            class="btn btn-outline-danger btn-sm"
                                                            title="Xóa">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-3">
                                            Không có tag nào
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center mt-3">
                        {{ $tags->appends(request()->query())->links() }}
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>

@endsection
