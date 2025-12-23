@extends('app')
@section('content')

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">

            <div class="card shadow-sm">
                <!-- Header -->
                <div class="card-header bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0 text-primary fw-bold">
                            Cập nhật Tag
                        </h4>
                        <a href="{{ route('admin.tags.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Quay lại
                        </a>
                    </div>
                </div>

                <!-- Body -->
                <div class="card-body">

                    <form method="POST" action="{{ route('admin.tags.update', $tag) }}">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            {{-- Tên tag --}}
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="name" class="form-label fw-bold">
                                        Tên tag <span class="text-danger">*</span>
                                    </label>
                                    <input type="text"
                                           id="name"
                                           name="name"
                                           value="{{ old('name', $tag->name) }}"
                                           class="form-control @error('name') is-invalid @enderror"
                                           placeholder="VD: Khuyến mãi, Sự kiện">

                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror

                                    <small class="form-text text-muted">
                                        Tên tag phải là duy nhất, không trùng với tag khác
                                    </small>
                                </div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="form-group mt-4 d-flex justify-content-end gap-2">
                            <a href="{{ route('admin.tags.index') }}" class="btn btn-secondary px-4">
                                <i class="fas fa-times me-1"></i> Hủy bỏ
                            </a>
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="fas fa-save me-1"></i> Cập nhật
                            </button>
                        </div>

                    </form>

                </div>
            </div>

        </div>
    </div>
</div>

@endsection
