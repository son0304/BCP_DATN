@extends('app')
@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="card-title">Chi tiết Danh mục: {{ $productCategory->name }}</h3>
                        <div>
                            <a href="{{ route('owner.product_categories.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Quay lại
                            </a>
                            <a href="{{ route('owner.product_categories.edit', $productCategory) }}" class="btn btn-primary ms-2">
                                <i class="fas fa-edit"></i> Chỉnh sửa
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="card-body">
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

                    <div class="row">
                        <!-- Thông tin cơ bản -->
                        <div class="col-md-6">
                            <h5 class="mb-3">Thông tin Danh mục</h5>
                            <table class="table table-bordered">
                                <tr>
                                    <th width="40%">ID</th>
                                    <td>{{ $productCategory->id }}</td>
                                </tr>
                                <tr>
                                    <th>Tên danh mục</th>
                                    <td><strong>{{ $productCategory->name }}</strong></td>
                                </tr>
                                <tr>
                                    <th>Slug</th>
                                    <td><code>{{ $productCategory->slug }}</code></td>
                                </tr>
                                <tr>
                                    <th>Mô tả</th>
                                    <td>{{ $productCategory->description ?: '—' }}</td>
                                </tr>
                                <tr>
                                    <th>Số sản phẩm</th>
                                    <td>
                                        <span class="badge bg-success">{{ $productCategory->products->count() }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Thứ tự sắp xếp</th>
                                    <td>{{ $productCategory->sort_order }}</td>
                                </tr>
                                <tr>
                                    <th>Trạng thái</th>
                                    <td>
                                        @if($productCategory->is_active)
                                        <span class="badge bg-success">Hoạt động</span>
                                        @else
                                        <span class="badge bg-danger">Không hoạt động</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Hình ảnh</th>
                                    <td>
                                        @if($productCategory->image_url)
                                        <img src="{{ $productCategory->image_url }}" alt="{{ $productCategory->name }}" 
                                            class="img-thumbnail" style="max-width: 200px; max-height: 200px; object-fit: cover;">
                                        @else
                                        <span class="text-muted">Không có hình ảnh</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Ngày tạo</th>
                                    <td>{{ $productCategory->created_at->format('d/m/Y H:i') }}</td>
                                </tr>
                                <tr>
                                    <th>Ngày cập nhật</th>
                                    <td>{{ $productCategory->updated_at->format('d/m/Y H:i') }}</td>
                                </tr>
                            </table>
                        </div>

                        <!-- Sản phẩm -->
                        <div class="col-md-6">
                            <h5 class="mb-3">Sản phẩm trong danh mục</h5>
                            @if($productCategory->products->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm">
                                    <thead>
                                        <tr>
                                            <th>Tên</th>
                                            <th>SKU</th>
                                            <th>Giá</th>
                                            <th>Tồn kho</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($productCategory->products->take(10) as $product)
                                        <tr>
                                            <td>{{ $product->name }}</td>
                                            <td><code>{{ $product->sku ?: '—' }}</code></td>
                                            <td>{{ number_format($product->price, 0) }}₫</td>
                                            <td>{{ $product->stock_quantity }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                                @if($productCategory->products->count() > 10)
                                <p class="text-muted small">Hiển thị 10 sản phẩm đầu tiên trong tổng số {{ $productCategory->products->count() }} sản phẩm</p>
                                @endif
                            </div>
                            @else
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                Danh mục này chưa có sản phẩm nào.
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

