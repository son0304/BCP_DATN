@extends('app')
@section('content')
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="fw-bold mb-0 text-primary"><i class="fas fa-plus-circle me-2"></i>Tạo Chương Trình Ưu Đãi
                            Mới</h5>
                    </div>
                    <div class="card-body p-4">
                        <form action="{{ route('owner.promotions.store') }}" method="POST">
                            @csrf
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Mã Voucher <span class="text-danger">*</span></label>
                                    <input type="text" name="code" class="form-control text-uppercase fw-bold"
                                        placeholder="VD: CHAOHE2026" required>
                                    <small class="text-muted">Mã khách hàng sẽ nhập khi đặt sân.</small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Trạng thái</label>
                                    <select name="process_status" class="form-select">
                                        <option value="active">Kích hoạt ngay</option>
                                        <option value="disabled">Tạm ẩn (Tắt)</option>
                                    </select>
                                </div>

                                <div class="col-12">
                                    <label class="form-label fw-bold">Mô tả hiển thị cho khách hàng</label>
                                    <textarea name="description" rows="2" class="form-control"
                                        placeholder="VD: Giảm ngay 20k cho đơn hàng từ 200k..."></textarea>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label fw-bold text-primary">Cách thức giảm</label>
                                    <select name="type" id="discountType" class="form-select border-primary fw-bold">
                                        <option value="percentage">Giảm theo phần trăm (%)</option>
                                        <option value="fixed">Giảm số tiền cố định (₫)</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Giá trị giảm <span
                                            class="text-danger">*</span></label>
                                    <input type="number" name="value" class="form-control fw-bold"
                                        placeholder="VD: 10 hoặc 20000" required>
                                </div>
                                <div id="maxDiscountCol" class="col-md-4">
                                    <label class="form-label fw-bold">Giảm tối đa (₫)</label>
                                    <input type="number" name="max_discount_amount" class="form-control"
                                        placeholder="Để trống nếu không giới hạn">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Phạm vi áp dụng</label>
                                    <select name="venue_id" class="form-select border-primary">
                                        <option value="">🏘️ TẤT CẢ SÂN CỦA TÔI</option>
                                        @foreach ($venues as $v)
                                            <option value="{{ $v->id }}">📍 Chỉ riêng sân: {{ $v->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted">Chọn "Tất cả sân" nếu bạn muốn mã có hiệu lực trên toàn bộ cơ
                                        sở bạn quản lý.</small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Đơn tối thiểu (₫)</label>
                                    <input type="number" name="min_order_value" class="form-control" value="0">
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Tổng lượt dùng tối đa</label>
                                    <input type="number" name="usage_limit" class="form-control" value="0">
                                    <small class="text-muted">0 = Không giới hạn.</small>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Bắt đầu</label>
                                    <input type="datetime-local" name="start_at" class="form-control"
                                        value="{{ now()->format('Y-m-d\TH:i') }}" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold text-danger">Kết thúc</label>
                                    <input type="datetime-local" name="end_at" class="form-control" required>
                                </div>

                                <input type="hidden" name="target_user_type" value="all">
                            </div>

                            <div class="mt-4 pt-3 border-top text-end">
                                <a href="{{ route('owner.promotions.index') }}"
                                    class="btn btn-light px-4 rounded-pill">Quay lại</a>
                                <button type="submit" class="btn btn-primary px-5 rounded-pill shadow fw-bold">Tạo
                                    Voucher</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const discountType = document.getElementById('discountType');
            const maxDiscountCol = document.getElementById('maxDiscountCol');

            function toggleMax() {
                maxDiscountCol.style.display = (discountType.value === 'percentage') ? 'block' : 'none';
            }
            discountType.addEventListener('change', toggleMax);
            toggleMax();
        });
    </script>
@endsection
