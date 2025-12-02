@extends('app')
@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0 text-primary font-weight-bold">Thêm Sản phẩm Mới</h4>
                        <a href="{{ route('owner.products.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Quay lại
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <form method="POST" action="{{ route('owner.products.store') }}">
                        @csrf

                        <div class="row">
                            {{-- Tên sản phẩm --}}
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="name" class="form-label fw-bold">Tên sản phẩm <span class="text-danger">*</span></label>
                                    <input type="text"
                                        class="form-control @error('name') is-invalid @enderror"
                                        id="name"
                                        name="name"
                                        value="{{ old('name') }}"
                                        placeholder="VD: Nước uống thể thao">
                                    @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            {{-- SKU --}}
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="sku" class="form-label fw-bold">Mã SKU</label>
                                    <input type="text"
                                        class="form-control @error('sku') is-invalid @enderror"
                                        id="sku"
                                        name="sku"
                                        value="{{ old('sku') }}"
                                        placeholder="VD: SP001"
                                        style="text-transform: uppercase">
                                    @error('sku')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">Mã SKU sẽ được tự động chuyển thành chữ in hoa</small>
                                </div>
                            </div>
                        </div>

                        {{-- Mô tả --}}
                        <div class="form-group mb-3">
                            <label for="description" class="form-label fw-bold">Mô tả</label>
                            <textarea class="form-control @error('description') is-invalid @enderror"
                                id="description"
                                name="description"
                                rows="3"
                                placeholder="Mô tả chi tiết về sản phẩm...">{{ old('description') }}</textarea>
                            @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            {{-- Thương hiệu --}}
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="venue_id" class="form-label fw-bold">Thương hiệu</label>
                                    <select class="form-select @error('venue_id') is-invalid @enderror"
                                        id="venue_id"
                                        name="venue_id">
                                        <option value="">Chọn thương hiệu (để trống nếu là sản phẩm chung)</option>
                                        @foreach($venues as $venue)
                                        <option value="{{ $venue->id }}" {{ old('venue_id') == $venue->id ? 'selected' : '' }}>
                                            {{ $venue->name }}
                                        </option>
                                        @endforeach
                                    </select>
                                    @error('venue_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">Để trống nếu sản phẩm này dùng chung cho tất cả thương hiệu</small>
                                </div>
                            </div>

                            {{-- Danh mục --}}
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="category_id" class="form-label fw-bold">Danh mục</label>
                                    <select class="form-select @error('category_id') is-invalid @enderror"
                                        id="category_id"
                                        name="category_id">
                                        <option value="">Chọn danh mục</option>
                                        @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                        @endforeach
                                    </select>
                                    @error('category_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            {{-- Giá bán --}}
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label for="price" class="form-label fw-bold">Giá bán (₫) <span class="text-danger">*</span></label>
                                    <input type="text"
                                        class="form-control @error('price') is-invalid @enderror"
                                        id="price"
                                        name="price"
                                        value="{{ old('price') }}"
                                        placeholder="VD: 50000">
                                    @error('price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            {{-- Giá vốn --}}
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label for="cost_price" class="form-label fw-bold">Giá vốn (₫)</label>
                                    <input type="text"
                                        class="form-control @error('cost_price') is-invalid @enderror"
                                        id="cost_price"
                                        name="cost_price"
                                        value="{{ old('cost_price') }}"
                                        placeholder="VD: 30000">
                                    @error('cost_price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            {{-- Đơn vị --}}
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label for="unit" class="form-label fw-bold">Đơn vị</label>
                                    <input type="text"
                                        class="form-control @error('unit') is-invalid @enderror"
                                        id="unit"
                                        name="unit"
                                        value="{{ old('unit') }}"
                                        placeholder="VD: chai, lon, gói">
                                    @error('unit')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            {{-- Số lượng tồn kho --}}
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label for="stock_quantity" class="form-label fw-bold">Số lượng tồn kho <span class="text-danger">*</span></label>
                                    <input type="text"
                                        class="form-control @error('stock_quantity') is-invalid @enderror"
                                        id="stock_quantity"
                                        name="stock_quantity"
                                        value="{{ old('stock_quantity', 0) }}"
                                        placeholder="VD: 100">
                                    @error('stock_quantity')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            {{-- Mức tồn kho tối thiểu --}}
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label for="min_stock_level" class="form-label fw-bold">Mức tồn kho tối thiểu</label>
                                    <input type="text"
                                        class="form-control @error('min_stock_level') is-invalid @enderror"
                                        id="min_stock_level"
                                        name="min_stock_level"
                                        value="{{ old('min_stock_level', 0) }}"
                                        placeholder="VD: 10">
                                    @error('min_stock_level')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">Cảnh báo khi tồn kho ≤ mức này</small>
                                </div>
                            </div>

                            {{-- Thứ tự sắp xếp --}}
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label for="sort_order" class="form-label fw-bold">Thứ tự sắp xếp</label>
                                    <input type="text"
                                        class="form-control @error('sort_order') is-invalid @enderror"
                                        id="sort_order"
                                        name="sort_order"
                                        value="{{ old('sort_order', 0) }}"
                                        placeholder="VD: 1">
                                    @error('sort_order')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            {{-- URL hình ảnh --}}
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="image_url" class="form-label fw-bold">URL hình ảnh</label>
                                    <input type="text"
                                        class="form-control @error('image_url') is-invalid @enderror"
                                        id="image_url"
                                        name="image_url"
                                        value="{{ old('image_url') }}"
                                        placeholder="https://example.com/image.jpg">
                                    @error('image_url')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            {{-- Trạng thái --}}
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_active">
                                            <strong>Hoạt động</strong>
                                        </label>
                                    </div>
                                    <small class="form-text text-muted">Sản phẩm sẽ hiển thị và có thể bán khi được bật</small>
                                </div>
                            </div>

                            {{-- Nổi bật --}}
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="is_featured" name="is_featured" value="1" {{ old('is_featured') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_featured">
                                            <strong>Sản phẩm nổi bật</strong>
                                        </label>
                                    </div>
                                    <small class="form-text text-muted">Sản phẩm sẽ được đánh dấu là nổi bật</small>
                                </div>
                            </div>
                        </div>

                        <div class="form-group mt-4 d-flex justify-content-end gap-2">
                            <a href="{{ route('owner.products.index') }}" class="btn btn-secondary px-4">
                                <i class="fas fa-times me-1"></i> Hủy bỏ
                            </a>
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="fas fa-save me-1"></i> Tạo sản phẩm
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const skuInput = document.getElementById('sku');
        if (skuInput) {
            skuInput.addEventListener('input', function() {
                this.value = this.value.toUpperCase();
            });
        }
    });
</script>
@endpush
@endsection

