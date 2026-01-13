<div class="modal fade" id="addBannerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('admin.settings.banners.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="modal-content shadow-lg border-0">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold">Thêm Banner Mới</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3 text-center">
                        <label class="form-label fw-bold small d-block text-start">Xem trước hình ảnh</label>
                        <img id="preview-add" src="https://placehold.co/600x300?text=Preview+Image" class="rounded border shadow-sm mb-3" style="max-width: 100%; height: 150px; object-fit: cover;">
                        <input type="file" name="image" class="form-control @error('image') is-invalid @enderror" onchange="previewImage(this, 'preview-add')">
                        @error('image') <div class="invalid-feedback text-start">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small">Tiêu đề Banner <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title') }}" placeholder="Nhập tiêu đề quảng cáo...">
                        @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small">Link đích (URL)</label>
                        <input type="text" name="target_url" class="form-control" value="{{ old('target_url') }}" placeholder="Ví dụ: /san-bong-a">
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold small">Vị trí</label>
                            <select name="position" class="form-select">
                                <option value="home_hero">Trang chủ (Chính)</option>
                                <option value="popup">Popup quảng cáo</option>
                                <option value="list_sidebar">Cột bên (Sidebar)</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold small">Trạng thái</label>
                            <select name="is_active" class="form-select">
                                <option value="1">Hiển thị ngay</option>
                                <option value="0">Tạm ẩn</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold small">Thứ tự ưu tiên <span class="text-danger">*</span></label>
                            <input type="number" name="priority" class="form-control @error('priority') is-invalid @enderror" value="{{ old('priority', 0) }}">
                            @error('priority') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold small">Ngày kết thúc <span class="text-danger">*</span></label>
                            <input type="date" name="end_date" class="form-control @error('end_date') is-invalid @enderror" value="{{ old('end_date') }}">
                            @error('end_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <input type="hidden" name="start_date" value="{{ date('Y-m-d') }}">
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-primary px-4">Phát hành ngay</button>
                </div>
            </div>
        </form>
    </div>
</div>
