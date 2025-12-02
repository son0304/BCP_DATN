@extends('app')
@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0 text-primary font-weight-bold">Thêm Danh mục Mới</h4>
                        <a href="{{ route('admin.product_categories.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Quay lại
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <form method="POST" action="{{ route('admin.product_categories.store') }}">
                        @csrf

                        <div class="row">
                            {{-- Tên danh mục --}}
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="name" class="form-label fw-bold">Tên danh mục <span class="text-danger">*</span></label>
                                    <input type="text"
                                        class="form-control @error('name') is-invalid @enderror"
                                        id="name"
                                        name="name"
                                        value="{{ old('name') }}"
                                        placeholder="VD: Đồ uống">
                                    @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            {{-- Slug --}}
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="slug" class="form-label fw-bold">Slug</label>
                                    <input type="text"
                                        class="form-control @error('slug') is-invalid @enderror"
                                        id="slug"
                                        name="slug"
                                        value="{{ old('slug') }}"
                                        placeholder="VD: do-uong">
                                    @error('slug')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">Để trống để tự động tạo từ tên danh mục</small>
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
                                placeholder="Mô tả về danh mục...">{{ old('description') }}</textarea>
                            @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            {{-- Danh mục cha --}}
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="parent_id" class="form-label fw-bold">Danh mục cha</label>
                                    <select class="form-select @error('parent_id') is-invalid @enderror"
                                        id="parent_id"
                                        name="parent_id">
                                        <option value="">Không có (Danh mục gốc)</option>
                                        @foreach($parentCategories as $parent)
                                        <option value="{{ $parent->id }}" {{ old('parent_id') == $parent->id ? 'selected' : '' }}>
                                            {{ $parent->name }}
                                        </option>
                                        @endforeach
                                    </select>
                                    @error('parent_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">Chọn danh mục cha nếu đây là danh mục con</small>
                                </div>
                            </div>

                            {{-- Thứ tự sắp xếp --}}
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="sort_order" class="form-label fw-bold">Thứ tự sắp xếp</label>
                                    <input type="number"
                                        class="form-control @error('sort_order') is-invalid @enderror"
                                        id="sort_order"
                                        name="sort_order"
                                        value="{{ old('sort_order', 0) }}"
                                        placeholder="VD: 1"
                                        min="0">
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
                                    <input type="url"
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
                                    <small class="form-text text-muted">Danh mục sẽ hiển thị khi được bật</small>
                                </div>
                            </div>
                        </div>

                        <div class="form-group mt-4 d-flex justify-content-end gap-2">
                            <a href="{{ route('admin.product_categories.index') }}" class="btn btn-secondary px-4">
                                <i class="fas fa-times me-1"></i> Hủy bỏ
                            </a>
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="fas fa-save me-1"></i> Tạo danh mục
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

