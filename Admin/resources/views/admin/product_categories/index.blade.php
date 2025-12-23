@extends('app')
@section('content')

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">Quản lý Danh mục Sản phẩm</h3>
                    <a href="{{ route('admin.product_categories.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i> Thêm danh mục mới
                    </a>
                </div>

                <!-- Search & Filter -->
                <div class="card-body">
                    <!-- Filter container -->
                    <div class="p-3 mb-4 rounded-3" style="background-color: #f8f9fa;">
                        <form method="GET" action="{{ route('admin.product_categories.index') }}">
                            <div class="row g-3">
                                <!-- Search -->
                                <div class="col-md-4">
                                    <label for="search" class="form-label fw-semibold">Tìm kiếm</label>
                                    <input type="text" id="search" name="search" value="{{ request('search') }}"
                                        class="form-control" placeholder="Tên, slug, mô tả...">
                                </div>

                                <!-- Parent Only Filter -->
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold d-block mb-1">Loại</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="parent_only" id="parent_only" value="1"
                                            {{ request('parent_only') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="parent_only">Chỉ danh mục cha</label>
                                    </div>
                                </div>

                                <!-- Status Filter -->
                                <div class="col-md-3">
                                    <label for="is_active" class="form-label fw-semibold">Trạng thái</label>
                                    <select class="form-select" id="is_active" name="is_active">
                                        <option value="">Tất cả</option>
                                        <option value="1" {{ request('is_active') == '1' ? 'selected' : '' }}>Hoạt động</option>
                                        <option value="0" {{ request('is_active') == '0' ? 'selected' : '' }}>Không hoạt động</option>
                                    </select>
                                </div>

                                <!-- Buttons -->
                                <div class="col-md-2 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="fas fa-search me-1"></i> Tìm kiếm
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

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

                    <!-- Categories Table -->
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered align-middle mb-0">
                            <thead class="table-light">
                                <tr class="text-center">
                                    <th>ID</th>
                                    <th>Hình ảnh</th>
                                    <th>Tên danh mục</th>
                                    <th>Slug</th>
                                    <th>Danh mục cha</th>
                                    <th>Số danh mục con</th>
                                    <th>Số sản phẩm</th>
                                    <th>Thứ tự</th>
                                    <th>Trạng thái</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($categories as $category)
                                <tr>
                                    <td class="text-center">{{ $category->id }}</td>
                                    <td class="text-center">
                                        @if($category->image_url)
                                        <img src="{{ $category->image_url }}" alt="{{ $category->name }}" 
                                            class="img-thumbnail" style="width: 50px; height: 50px; object-fit: cover;">
                                        @else
                                        <span class="text-muted"><i class="fas fa-image"></i></span>
                                        @endif
                                    </td>
                                    <td>
                                        <strong>{{ $category->name }}</strong>
                                        @if($category->description)
                                        <br><small class="text-muted">{{ Str::limit($category->description, 50) }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <code>{{ $category->slug }}</code>
                                    </td>
                                    <td>
                                        @if($category->parent)
                                        <span class="badge bg-info">{{ $category->parent->name }}</span>
                                        @else
                                        <span class="badge bg-secondary">Danh mục gốc</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-primary">{{ $category->children->count() }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-success">{{ $category->products->count() }}</span>
                                    </td>
                                    <td class="text-center">{{ $category->sort_order }}</td>
                                    <td class="text-center">
                                        @if($category->is_active)
                                        <span class="badge bg-success">Hoạt động</span>
                                        @else
                                        <span class="badge bg-danger">Không hoạt động</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('admin.product_categories.show', $category) }}"
                                                class="btn btn-outline-primary btn-sm me-2" title="Xem chi tiết">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.product_categories.edit', $category) }}"
                                                class="btn btn-outline-warning btn-sm me-2" title="Sửa">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form method="POST" action="{{ route('admin.product_categories.destroy', $category) }}"
                                                class="d-inline"
                                                onsubmit="return confirm('Bạn có chắc chắn muốn xóa danh mục này?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger btn-sm" title="Xóa">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="10" class="text-center py-5">
                                        <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
                                        <h5 class="mb-1">Không tìm thấy danh mục nào</h5>
                                        <p class="text-muted">Hãy bắt đầu bằng cách thêm một danh mục mới.</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center mt-3">
                        {{ $categories->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection


