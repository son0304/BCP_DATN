@extends('app')

@section('title', 'Tạo Chiến Dịch Flash Sale Mới')

@section('content')
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-lg border-0 rounded-3">
                    {{-- Header màu xanh đậm chuyên nghiệp --}}
                    <div class="card-header bg-primary text-white p-4">
                        <h4 class="mb-0 fw-bold">
                            <i class="bi bi-lightning-fill me-2"></i>Tạo Chiến Dịch Flash Sale
                        </h4>
                        <p class="mb-0 small text-white-50">Thiết lập khung giờ vàng và thông tin chiến dịch</p>
                    </div>

                    <div class="card-body p-4">
                        {{-- Form bắt đầu --}}
                        {{-- Thay 'flash-campaigns.store' bằng route thực tế của bạn --}}
                        <form action="{{ route('admin.flash_sale_campaigns.store') }}" method="POST">
                            @csrf

                            {{-- 1. Tên chiến dịch --}}
                            <div class="mb-4">
                                <label for="name" class="form-label fw-bold text-secondary">Tên chiến dịch <span
                                        class="text-danger">*</span></label>
                                <input type="text"
                                    class="form-control form-control-lg @error('name') is-invalid @enderror" id="name"
                                    name="name" value="{{ old('name') }}" placeholder="Ví dụ: Xả lỗ giờ trưa 20/10">
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- 2. Thời gian bắt đầu & Kết thúc (Chia 2 cột) --}}
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <label for="start_datetime" class="form-label fw-bold text-secondary">Bắt đầu <span
                                            class="text-danger">*</span></label>
                                    <input type="datetime-local"
                                        class="form-control @error('start_datetime') is-invalid @enderror"
                                        id="start_datetime" name="start_datetime" value="{{ old('start_datetime') }}">
                                    @error('start_datetime')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="end_datetime" class="form-label fw-bold text-secondary">Kết thúc <span
                                            class="text-danger">*</span></label>
                                    <input type="datetime-local"
                                        class="form-control @error('end_datetime') is-invalid @enderror" id="end_datetime"
                                        name="end_datetime" value="{{ old('end_datetime') }}">
                                    @error('end_datetime')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            {{-- 3. Trạng thái --}}
                            <div class="mb-4">
                                <label for="status" class="form-label fw-bold text-secondary">Trạng thái thiết lập</label>
                                <select class="form-select @error('status') is-invalid @enderror" name="status"
                                    id="status">
                                    <option value="pending" {{ old('status') == 'pending' ? 'selected' : '' }}>⏳ Sắp diễn ra
                                        (Pending)</option>
                                    <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>🔥 Đang chạy
                                        (Active)</option>
                                    <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>🔒 Tạm khóa
                                        (Inactive)</option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Chọn "Sắp diễn ra" để chủ sân có thời gian đăng ký trước.</div>
                            </div>

                            {{-- 4. Mô tả --}}
                            <div class="mb-4">
                                <label for="description" class="form-label fw-bold text-secondary">Mô tả chi tiết</label>
                                <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description"
                                    rows="4" placeholder="Mô tả về chương trình khuyến mãi này...">{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Action Buttons --}}
                            <div class="d-flex justify-content-end gap-2 mt-5">
                                <a href="{{ url()->previous() }}" class="btn btn-light border px-4">Hủy bỏ</a>
                                <button type="submit" class="btn btn-primary px-5 fw-bold">
                                    <i class="bi bi-save me-1"></i> Lưu Chiến Dịch
                                </button>
                            </div>

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
