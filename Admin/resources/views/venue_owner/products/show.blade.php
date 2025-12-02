@extends('app')
@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="card-title">Chi tiết Sản phẩm: {{ $product->name }}</h3>
                        <div>
                            <a href="{{ route('owner.products.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Quay lại
                            </a>
                            <a href="{{ route('owner.products.edit', $product) }}" class="btn btn-primary ms-2">
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
                            <h5 class="mb-3">Thông tin Sản phẩm</h5>
                            <table class="table table-bordered">
                                <tr>
                                    <th width="40%">ID</th>
                                    <td>{{ $product->id }}</td>
                                </tr>
                                <tr>
                                    <th>Tên sản phẩm</th>
                                    <td>
                                        <strong>{{ $product->name }}</strong>
                                        @if($product->is_featured)
                                        <span class="badge bg-warning ms-2">Nổi bật</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>SKU</th>
                                    <td>
                                        @if($product->sku)
                                        <code>{{ $product->sku }}</code>
                                        @else
                                        <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Mô tả</th>
                                    <td>{{ $product->description ?: '—' }}</td>
                                </tr>
                                <tr>
                                    <th>Thương hiệu</th>
                                    <td>
                                        @if($product->venue)
                                        {{ $product->venue->name }}
                                        @else
                                        <span class="text-muted">Sản phẩm chung</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Danh mục</th>
                                    <td>
                                        @if($product->category)
                                        {{ $product->category->name }}
                                        @else
                                        <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Giá bán</th>
                                    <td>
                                        <strong class="text-primary fs-5">{{ number_format($product->price, 0) }}₫</strong>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Giá vốn</th>
                                    <td>
                                        @if($product->cost_price)
                                        {{ number_format($product->cost_price, 0) }}₫
                                        @else
                                        <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Lợi nhuận</th>
                                    <td>
                                        @if($product->cost_price)
                                        <strong class="text-success">
                                            {{ number_format($product->price - $product->cost_price, 0) }}₫
                                            ({{ number_format((($product->price - $product->cost_price) / $product->price) * 100, 1) }}%)
                                        </strong>
                                        @else
                                        <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Số lượng tồn kho</th>
                                    <td>
                                        @php
                                        $stockClass = 'success';
                                        if($product->stock_quantity == 0) {
                                            $stockClass = 'danger';
                                        } elseif($product->stock_quantity <= $product->min_stock_level) {
                                            $stockClass = 'warning';
                                        }
                                        @endphp
                                        <span class="badge bg-{{ $stockClass }} fs-6">
                                            {{ $product->stock_quantity }}
                                            @if($product->unit) {{ $product->unit }} @endif
                                        </span>
                                        @if($product->stock_quantity <= $product->min_stock_level && $product->stock_quantity > 0)
                                        <span class="text-warning ms-2"><i class="fas fa-exclamation-triangle"></i> Sắp hết</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Mức tồn kho tối thiểu</th>
                                    <td>
                                        {{ $product->min_stock_level }}
                                        @if($product->unit) {{ $product->unit }} @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Đơn vị</th>
                                    <td>{{ $product->unit ?: '—' }}</td>
                                </tr>
                                <tr>
                                    <th>Thứ tự sắp xếp</th>
                                    <td>{{ $product->sort_order }}</td>
                                </tr>
                                <tr>
                                    <th>Trạng thái</th>
                                    <td>
                                        @if($product->is_active)
                                        <span class="badge bg-success">Hoạt động</span>
                                        @else
                                        <span class="badge bg-danger">Không hoạt động</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Hình ảnh</th>
                                    <td>
                                        @if($product->image_url)
                                        <img src="{{ $product->image_url }}" alt="{{ $product->name }}" 
                                            class="img-thumbnail" style="max-width: 200px; max-height: 200px; object-fit: cover;">
                                        @else
                                        <span class="text-muted">Không có hình ảnh</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Ngày tạo</th>
                                    <td>{{ $product->created_at->format('d/m/Y H:i') }}</td>
                                </tr>
                                <tr>
                                    <th>Ngày cập nhật</th>
                                    <td>{{ $product->updated_at->format('d/m/Y H:i') }}</td>
                                </tr>
                            </table>
                        </div>

                        <!-- Lịch sử sử dụng -->
                        <div class="col-md-6">
                            <h5 class="mb-3">Lịch sử sử dụng</h5>
                            @if($product->items && $product->items->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm">
                                    <thead>
                                        <tr>
                                            <th>Ticket ID</th>
                                            <th>Số lượng</th>
                                            <th>Giá</th>
                                            <th>Ngày</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($product->items->take(10) as $item)
                                        <tr>
                                            <td>
                                                <a href="{{ route('owner.bookings.show', $item->ticket_id) }}" target="_blank">
                                                    #{{ $item->ticket_id }}
                                                </a>
                                            </td>
                                            <td>{{ $item->quantity }}</td>
                                            <td>{{ number_format($item->product_price ?? $item->unit_price, 0) }}₫</td>
                                            <td>{{ $item->created_at->format('d/m/Y') }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                                @if($product->items->count() > 10)
                                <p class="text-muted small">Hiển thị 10 mục đầu tiên trong tổng số {{ $product->items->count() }} mục</p>
                                @endif
                            </div>
                            @else
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                Sản phẩm này chưa được sử dụng trong bất kỳ đơn hàng nào.
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

