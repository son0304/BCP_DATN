@extends('app')

@section('content')
{{-- Thêm chút CSS nội bộ để custom nhanh giao diện --}}
<style>
    /* Font số tiền đẹp hơn */
    .font-monospace { font-family: 'Consolas', 'Monaco', monospace; letter-spacing: -0.5px; }

    /* Soft Badges - Badge màu nhạt nhìn sang hơn */
    .badge-soft-success { background-color: #d1e7dd; color: #0f5132; }
    .badge-soft-warning { background-color: #fff3cd; color: #664d03; }
    .badge-soft-danger { background-color: #f8d7da; color: #842029; }
    .badge-soft-primary { background-color: #cfe2ff; color: #084298; }
    .badge-soft-momo { background-color: #fce4ec; color: #ad1457; } /* Màu riêng cho MoMo */

    /* Avatar User */
    .avatar-circle {
        width: 40px; height: 40px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-weight: bold; font-size: 14px;
        box-shadow: 0 3px 6px rgba(0,0,0,0.1);
    }

    /* Table Hover Effect */
    .table-hover tbody tr:hover {
        background-color: #f8f9fa;
        transform: translateY(-1px);
        transition: all 0.2s;
        box-shadow: 0 2px 5px rgba(0,0,0,0.05);
    }

    /* Card Design */
    .card-modern {
        border: none;
        border-radius: 12px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.05);
    }

    /* Custom Input Group */
    .input-group-text { background: #fff; border-right: none; color: #6c757d; }
    .form-control, .form-select { border-left: none; box-shadow: none !important; border-color: #ced4da; }
    .form-control:focus, .form-select:focus { border-color: #86b7fe; }
    .input-group:focus-within .input-group-text { border-color: #86b7fe; color: #0d6efd; }
    .input-group:focus-within .form-control { border-left: 1px solid #86b7fe !important; margin-left: -1px; }

</style>

<div class="container-fluid py-4 bg-light">

    {{-- Header Page --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-1">Quản lý Giao dịch</h4>
            <p class="text-muted small mb-0">Theo dõi dòng tiền và lịch sử thanh toán</p>
        </div>
        <div>
            <button class="btn btn-success text-white shadow-sm btn-sm px-3 rounded-pill">
                <i class="fas fa-file-excel me-1"></i> Xuất Excel
            </button>
        </div>
    </div>

    {{-- CARD 1: BỘ LỌC (FILTER) --}}
    <div class="card card-modern mb-4">
        <div class="card-body p-4">
            <form action="{{ route('admin.transactions.index') }}" method="GET">
                <div class="row g-3 align-items-end">
                    {{-- Tìm kiếm --}}
                    <div class="col-md-4 col-12">
                        <label class="form-label fw-bold text-uppercase text-secondary small" style="font-size: 0.75rem;">Từ khóa tìm kiếm</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                            <input type="text" name="keyword" class="form-control"
                                   placeholder="Nhập mã GD, SĐT, Tên..."
                                   value="{{ request('keyword') }}">
                        </div>
                    </div>

                    {{-- Trạng thái & Nguồn (Gộp chung 1 dòng trên mobile, chia đôi trên PC) --}}
                    <div class="col-md-2 col-6">
                        <label class="form-label fw-bold text-uppercase text-secondary small" style="font-size: 0.75rem;">Trạng thái</label>
                        <select name="status" class="form-select">
                            <option value="">Tất cả</option>
                            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Thành công</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Đang chờ</option>
                            <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Thất bại</option>
                        </select>
                    </div>

                    <div class="col-md-2 col-6">
                        <label class="form-label fw-bold text-uppercase text-secondary small" style="font-size: 0.75rem;">Nguồn tiền</label>
                        <select name="source" class="form-select">
                            <option value="">Tất cả</option>
                            <option value="momo" {{ request('source') == 'momo' ? 'selected' : '' }}>MoMo</option>
                            <option value="wallet" {{ request('source') == 'wallet' ? 'selected' : '' }}>Ví nội bộ</option>
                        </select>
                    </div>

                    {{-- Ngày tháng --}}
                    <div class="col-md-3 col-12">
                         <label class="form-label fw-bold text-uppercase text-secondary small" style="font-size: 0.75rem;">Thời gian</label>
                         <div class="input-group">
                             <input type="date" name="date_from" class="form-control small-date" value="{{ request('date_from') }}">
                             <span class="input-group-text border-start-0 border-end-0 bg-white text-muted">-</span>
                             <input type="date" name="date_to" class="form-control small-date" value="{{ request('date_to') }}">
                         </div>
                    </div>

                    {{-- Nút bấm --}}
                    <div class="col-md-1 col-12 d-flex">
                        <button type="submit" class="btn btn-primary w-100 me-2 rounded-3 shadow-sm" title="Tìm kiếm">
                            <i class="fas fa-filter"></i>
                        </button>
                        <a href="{{ route('admin.transactions.index') }}" class="btn btn-light border w-100 rounded-3" title="Reset">
                            <i class="fas fa-sync-alt text-secondary"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- CARD 2: BẢNG DỮ LIỆU --}}
    <div class="card card-modern">
        <div class="card-body p-0"> {{-- p-0 để bảng tràn viền --}}
            <div class="table-responsive rounded-top-3">
                <table class="table table-hover align-middle mb-0" style="border-collapse: separate; border-spacing: 0;">
                    <thead class="bg-light text-secondary">
                        <tr>
                            <th class="py-3 ps-4 text-uppercase small fw-bold" width="5%">ID</th>
                            <th class="py-3 text-uppercase small fw-bold">Khách hàng</th>
                            <th class="py-3 text-uppercase small fw-bold">Mã Booking</th>
                            <th class="py-3 text-end text-uppercase small fw-bold">Số tiền</th>
                            <th class="py-3 text-center text-uppercase small fw-bold">Nguồn</th>
                            <th class="py-3 text-center text-uppercase small fw-bold">Trạng thái</th>
                            <th class="py-3 text-uppercase small fw-bold">Ghi chú</th>
                            <th class="py-3 text-uppercase small fw-bold">Ngày tạo</th>
                            <th class="py-3 text-center text-uppercase small fw-bold" width="5%"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transactions as $item)
                        <tr style="border-bottom: 1px solid #f0f0f0;">
                            <td class="ps-4 fw-bold text-secondary">#{{ $item->id }}</td>

                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-circle me-3">
                                        {{ substr($item->user->name ?? 'U', 0, 1) }}
                                    </div>
                                    <div class="d-flex flex-column">
                                        <span class="fw-bold text-dark">{{ $item->user->name ?? 'User Unknown' }}</span>
                                        <span class="text-muted small" style="font-size: 0.8rem;">
                                            <i class="fas fa-phone-alt fa-xs me-1"></i>{{ $item->user->phone ?? '---' }}
                                        </span>
                                    </div>
                                </div>
                            </td>

                            <td>
                                <span class="badge bg-light text-dark border rounded-pill px-3">
                                    #{{ $item->booking_id }}
                                </span>
                            </td>

                            <td class="text-end">
                                <span class="fw-bold text-dark font-monospace fs-6">
                                    {{ number_format($item->amount, 0, ',', '.') }}
                                </span>
                                <span class="text-muted small align-top">đ</span>
                            </td>

                            <td class="text-center">
                                @if(strtolower($item->payment_source) == 'momo')
                                    <span class="badge badge-soft-momo border border-pink-100 px-2 py-1 rounded-2">
                                        <img src="https://upload.wikimedia.org/wikipedia/vi/f/fe/MoMo_Logo.png" width="14" class="me-1"> MoMo
                                    </span>
                                @elseif(strtolower($item->payment_source) == 'wallet')
                                    <span class="badge badge-soft-success px-2 py-1 rounded-2">
                                        <i class="fas fa-wallet me-1"></i> Ví
                                    </span>
                                @else
                                    <span class="badge bg-light text-secondary border">{{ $item->payment_source }}</span>
                                @endif
                            </td>

                            <td class="text-center">
                                @if($item->status == 'success' || $item->status == 'completed')
                                    <span class="badge badge-soft-success rounded-pill px-3 py-2">
                                        <i class="fas fa-check-circle me-1"></i>Thành công
                                    </span>
                                @elseif($item->status == 'pending')
                                    <span class="badge badge-soft-warning rounded-pill px-3 py-2">
                                        <i class="fas fa-clock me-1"></i>Đang chờ
                                    </span>
                                @elseif($item->status == 'failed')
                                    <span class="badge badge-soft-danger rounded-pill px-3 py-2">
                                        <i class="fas fa-times-circle me-1"></i>Thất bại
                                    </span>
                                @endif
                            </td>

                            <td>
                                <span class="d-inline-block text-truncate text-muted" style="max-width: 180px;" data-bs-toggle="tooltip" title="{{ $item->note }}">
                                    {{ $item->note }}
                                </span>
                            </td>

                            <td>
                                <div class="d-flex flex-column">
                                    <span class="fw-bold text-dark small">{{ $item->created_at->format('H:i') }}</span>
                                    <span class="text-muted small" style="font-size: 0.75rem;">{{ $item->created_at->format('d/m/Y') }}</span>
                                </div>
                            </td>

                            <td class="text-center pe-3">
                                <button class="btn btn-sm btn-light text-primary border-0 rounded-circle shadow-sm" style="width: 32px; height: 32px;" title="Xem chi tiết">
                                    <i class="fas fa-chevron-right"></i>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-5">
                                <div class="opacity-50">
                                    <i class="fas fa-receipt fa-4x mb-3 text-secondary"></i>
                                    <p class="text-muted">Không tìm thấy giao dịch nào.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Footer Pagination --}}
            <div class="px-4 py-3 border-top d-flex justify-content-between align-items-center bg-white rounded-bottom-3">
                <div class="text-muted small">
                    Hiển thị <b>{{ $transactions->firstItem() }}</b> - <b>{{ $transactions->lastItem() }}</b> trong tổng số <b>{{ $transactions->total() }}</b> giao dịch
                </div>
                <div>
                    {{ $transactions->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Kích hoạt tooltip
    document.addEventListener("DOMContentLoaded", function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });
    });
</script>
@endsection
