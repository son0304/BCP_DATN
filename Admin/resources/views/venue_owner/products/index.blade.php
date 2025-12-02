@extends('app')
@section('content')

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">Quản lý Sản phẩm</h3>
                    <a href="{{ route('owner.products.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i> Thêm sản phẩm mới
                    </a>
                </div>

                <!-- Search & Filter -->
                <div class="card-body">
                    <!-- Filter container -->
                    <div class="p-3 mb-4 rounded-3" style="background-color: #f8f9fa;">
                        <form method="GET" action="{{ route('owner.products.index') }}">
                            <div class="row g-3">
                                <!-- Search -->
                                <div class="col-md-3">
                                    <label for="search" class="form-label fw-semibold">Tìm kiếm</label>
                                    <input type="text" id="search" name="search" value="{{ request('search') }}"
                                        class="form-control" placeholder="Tên, SKU, mô tả...">
                                </div>

                                <!-- Venue Filter -->
                                <div class="col-md-2">
                                    <label for="venue_id" class="form-label fw-semibold">Thương hiệu</label>
                                    <select class="form-select" id="venue_id" name="venue_id">
                                        <option value="">Tất cả</option>
                                        @foreach($venues as $venue)
                                        <option value="{{ $venue->id }}" {{ request('venue_id') == $venue->id ? 'selected' : '' }}>
                                            {{ $venue->name }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Category Filter -->
                                <div class="col-md-2">
                                    <label for="category_id" class="form-label fw-semibold">Danh mục</label>
                                    <select class="form-select" id="category_id" name="category_id">
                                        <option value="">Tất cả</option>
                                        @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Status Filter -->
                                <div class="col-md-2">
                                    <label for="is_active" class="form-label fw-semibold">Trạng thái</label>
                                    <select class="form-select" id="is_active" name="is_active">
                                        <option value="">Tất cả</option>
                                        <option value="1" {{ request('is_active') == '1' ? 'selected' : '' }}>Hoạt động</option>
                                        <option value="0" {{ request('is_active') == '0' ? 'selected' : '' }}>Không hoạt động</option>
                                    </select>
                                </div>

                                <!-- Stock Status -->
                                <div class="col-md-2">
                                    <label for="stock_status" class="form-label fw-semibold">Tồn kho</label>
                                    <select class="form-select" id="stock_status" name="stock_status">
                                        <option value="">Tất cả</option>
                                        <option value="in_stock" {{ request('stock_status') == 'in_stock' ? 'selected' : '' }}>Còn hàng</option>
                                        <option value="low" {{ request('stock_status') == 'low' ? 'selected' : '' }}>Sắp hết</option>
                                        <option value="out" {{ request('stock_status') == 'out' ? 'selected' : '' }}>Hết hàng</option>
                                    </select>
                                </div>

                                <!-- Buttons -->
                                <div class="col-md-1 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="fas fa-search"></i>
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

                    <!-- Products Table -->
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered align-middle mb-0">
                            <thead class="table-light">
                                <tr class="text-center">
                                    <th>ID</th>
                                    <th>Hình ảnh</th>
                                    <th>Tên sản phẩm</th>
                                    <th>SKU</th>
                                    <th>Thương hiệu</th>
                                    <th>Danh mục</th>
                                    <th>Giá</th>
                                    <th>Tồn kho</th>
                                    <th>Trạng thái</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($products as $product)
                                <tr>
                                    <td class="text-center">{{ $product->id }}</td>
                                    <td class="text-center">
                                        @if($product->image_url)
                                        <img src="{{ $product->image_url }}" alt="{{ $product->name }}" 
                                            class="img-thumbnail" style="width: 50px; height: 50px; object-fit: cover;">
                                        @else
                                        <span class="text-muted"><i class="fas fa-image"></i></span>
                                        @endif
                                    </td>
                                    <td>
                                        <strong>{{ $product->name }}</strong>
                                        @if($product->is_featured)
                                        <span class="badge bg-warning ms-2">Nổi bật</span>
                                        @endif
                                        @if($product->description)
                                        <br><small class="text-muted">{{ Str::limit($product->description, 50) }}</small>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($product->sku)
                                        <code>{{ $product->sku }}</code>
                                        @else
                                        <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($product->venue)
                                        {{ $product->venue->name }}
                                        @else
                                        <span class="text-muted">Chung</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($product->category)
                                        {{ $product->category->name }}
                                        @else
                                        <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <strong>{{ number_format($product->price, 0) }}₫</strong>
                                        @if($product->cost_price)
                                        <br><small class="text-muted">Giá vốn: {{ number_format($product->cost_price, 0) }}₫</small>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @php
                                        $stockClass = 'success';
                                        if($product->stock_quantity == 0) {
                                            $stockClass = 'danger';
                                        } elseif($product->stock_quantity <= $product->min_stock_level) {
                                            $stockClass = 'warning';
                                        }
                                        @endphp
                                        <span class="badge bg-{{ $stockClass }}">
                                            {{ $product->stock_quantity }}
                                            @if($product->unit) {{ $product->unit }} @endif
                                        </span>
                                        @if($product->stock_quantity <= $product->min_stock_level && $product->stock_quantity > 0)
                                        <br><small class="text-warning">Sắp hết</small>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($product->is_active)
                                        <span class="badge bg-success">Hoạt động</span>
                                        @else
                                        <span class="badge bg-danger">Không hoạt động</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('owner.products.show', $product) }}"
                                                class="btn btn-outline-primary btn-sm me-2" title="Xem chi tiết">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('owner.products.edit', $product) }}"
                                                class="btn btn-outline-warning btn-sm me-2" title="Sửa">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form method="POST" action="{{ route('owner.products.destroy', $product) }}"
                                                class="d-inline"
                                                onsubmit="return confirm('Bạn có chắc chắn muốn xóa sản phẩm này?')">
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
                                        <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                                        <h5 class="mb-1">Không tìm thấy sản phẩm nào</h5>
                                        <p class="text-muted">Hãy bắt đầu bằng cách thêm một sản phẩm mới.</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center mt-3">
                        {{ $products->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection


