<!-- MODAL THÊM BANNER MỚI -->
<div class="modal fade" id="addBannerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('admin.settings.banners.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i>Thêm Banner Mới</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Hình ảnh Banner</label>
                        <input type="file" name="image" class="form-control" required>
                        <div class="form-text">Định dạng: JPG, PNG. Dung lượng < 2MB. Tỷ lệ khuyên dùng 1920x600.</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Tiêu đề Banner</label>
                            <input type="text" name="title" class="form-control"
                                placeholder="Ví dụ: Siêu khuyến mãi mùa hè">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Link điều hướng (Target URL)</label>
                            <input type="text" name="target_url" class="form-control"
                                placeholder="Ví dụ: /san-bong-a hoặc https://...">
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Vị trí hiển thị</label>
                                <select name="position" class="form-select">
                                    <option value="home_hero">Banner Chính (Trang chủ)</option>
                                    <option value="list_sidebar">Cột bên (Trang danh sách)</option>
                                    <option value="popup">Popup quảng cáo</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Thứ tự ưu tiên</label>
                                <input type="number" name="priority" class="form-control" value="0">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Ngày bắt đầu</label>
                                <input type="date" name="start_date" class="form-control" value="{{ date('Y-m-d') }}"
                                    required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Ngày kết thúc</label>
                                <input type="date" name="end_date" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy bỏ</button>
                        <button type="submit" class="btn btn-primary">Lưu nội dung</button>
                    </div>
                </div>
        </form>
    </div>
</div>

<!-- MODAL THÊM SÂN TÀI TRỢ -->
<div class="modal fade" id="addSponsoredModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('admin.settings.sponsored.store') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title"><i class="fas fa-ad me-2"></i>Thiết lập Sân Tài Trợ</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Chọn sân bóng</label>
                        <select name="venue_id" class="form-select" required>
                            <option value="">-- Tìm sân cần quảng cáo --</option>
                            @foreach ($venues as $v)
                                <option value="{{ $v->id }}">{{ $v->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Gói tài trợ (Vị trí hiển thị)</label>
                        <select name="tier" class="form-select">
                            <option value="diamond">Diamond (Gói Kim cương - Hiện trên cùng)</option>
                            <option value="gold">Gold (Gói Vàng - Hiện giữa)</option>
                            <option value="silver">Silver (Gói Bạc - Ngẫu nhiên)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nhãn hiển thị (Badge)</label>
                        <input type="text" name="badge_text" class="form-control"
                            placeholder="Ví dụ: HOT, GIẢM 20%, SÂN MỚI">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Ngày bắt đầu</label>
                            <input type="date" name="start_date" class="form-control" value="{{ date('Y-m-d') }}"
                                required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Ngày hết hạn</label>
                            <input type="date" name="end_date" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Link đích (Target URL)</label>
                        <input type="text" name="target_url" class="form-control"
                            placeholder="Link dẫn tới trang đặt sân">
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy bỏ</button>
                    <button type="submit" class="btn btn-warning">Kích hoạt ngay</button>
                </div>
            </div>
        </form>
    </div>
</div>
