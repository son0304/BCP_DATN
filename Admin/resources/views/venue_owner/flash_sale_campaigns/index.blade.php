@extends('app')

@section('content')
<style>
    .campaign-card {
        transition: all 0.3s ease;
        border-radius: 12px;
    }

    .campaign-card:hover {
        transform: translateY(-5px);
    }

    .status-badge {
        font-weight: 600;
        font-size: 0.75rem;
        padding: 5px 12px;
    }

    .filter-label {
        font-size: 0.75rem;
        text-transform: uppercase;
        font-weight: 700;
        color: #6c757d;
        margin-bottom: 5px;
        display: block;
    }

    .table-nowrap td {
        white-space: nowrap;
    }
</style>

<div class="container-fluid py-4">
    {{-- Header & Nút Tạo mới --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-0 text-dark">Chiến Dịch Flash Sale</h4>
            <p class="text-muted small mb-0">Quản lý các chương trình giảm giá giờ vàng của bạn</p>
        </div>
        <button type="button" class="btn btn-primary px-4 rounded-pill fw-bold shadow-sm" data-bs-toggle="modal"
            data-bs-target="#createCampaignModal">
            <i class="fas fa-plus me-2"></i>Tạo chiến dịch mới
        </button>
    </div>

    {{-- Thông báo --}}
    @if (session('success'))
    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
        <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif
    @if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
        <div class="fw-bold"><i class="fas fa-exclamation-triangle me-2"></i> Có lỗi xảy ra:</div>
        <ul class="mb-0 mt-2">
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    {{-- BỘ LỌC & TÌM KIẾM --}}
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <form action="{{ route('owner.flash_sale_campaigns.index') }}" method="GET"
                class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label class="filter-label">Tìm kiếm</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-0"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control border-0 bg-light"
                            placeholder="Tên chiến dịch..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="filter-label">Trạng thái</label>
                    <select name="status" class="form-select border-0 bg-light">
                        <option value="">Tất cả trạng thái</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Chờ kích hoạt
                        </option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Đang diễn ra
                        </option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Tạm dừng
                        </option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Đã kết thúc
                        </option>
                    </select>
                </div>
                <div class="col-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-dark px-4 fw-bold flex-grow-1">Lọc dữ liệu</button>
                    @if (request()->anyFilled(['search', 'status']))
                    <a href="{{ route('owner.flash_sale_campaigns.index') }}" class="btn btn-light border px-3">
                        <i class="fas fa-undo"></i>
                    </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- DANH SÁCH CHIẾN DỊCH --}}
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 table-nowrap">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4 py-3 text-muted small fw-bold">TÊN CHIẾN DỊCH</th>
                            <th class="text-muted small fw-bold">THỜI GIAN DIỄN RA</th>
                            <th class="text-muted small fw-bold">SỐ LƯỢNG SLOT</th>
                            <th class="text-muted small fw-bold">TRẠNG THÁI</th>
                            <th class="text-center pe-4 text-muted small fw-bold">THAO TÁC</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($flashSaleCampaigns as $campaign)
                        <tr>
                            <td class="ps-4">
                                <h6 class="mb-1 fw-bold text-dark">{{ $campaign->name }}</h6>
                                <small class="text-muted">{{ Str::limit($campaign->description, 40) }}</small>
                            </td>
                            <td>
                                <div class="small fw-bold">
                                    {{ \Carbon\Carbon::parse($campaign->start_datetime)->format('H:i d/m/Y') }}
                                </div>
                                <div class="text-muted small">đến
                                    {{ \Carbon\Carbon::parse($campaign->end_datetime)->format('H:i d/m/Y') }}
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">
                                    <i class="fas fa-clock me-1 text-primary"></i> {{ $campaign->items->count() }}
                                    khung giờ
                                </span>
                            </td>
                            <td>
                                @switch($campaign->status)
                                @case('pending')
                                <span
                                    class="badge bg-info bg-opacity-10 text-white border border-info rounded-pill status-badge">Chờ
                                    giờ</span>
                                @break

                                @case('active')
                                <span
                                    class="badge bg-success bg-opacity-10 text-white border border-success rounded-pill status-badge">Đang
                                    chạy</span>
                                @break

                                @case('inactive')
                                <span
                                    class="badge bg-secondary bg-opacity-10 text-white border border-secondary rounded-pill status-badge">Tạm
                                    dừng</span>
                                @break

                                @case('completed')
                                <span
                                    class="badge bg-dark bg-opacity-10 text-white border border-dark rounded-pill status-badge">Đã
                                    kết thúc</span>
                                @break
                                @endswitch
                            </td>
                            <td class="text-center pe-4">
                                <div class="d-inline-flex gap-2">
                                    {{-- Thiết lập khung giờ --}}
                                    <a href="{{ route('owner.flash_sale_campaigns.show', $campaign->id) }}"
                                        class="btn btn-sm btn-light border"
                                        title="Thiết lập khung giờ">
                                        <i class="fas fa-cog text-secondary"></i>
                                    </a>

                                    {{-- Sửa thông tin --}}
                                    <button type="button"
                                        class="btn btn-sm btn-light border"
                                        data-bs-toggle="modal"
                                        data-bs-target="#editCampaign{{ $campaign->id }}"
                                        title="Sửa thông tin">
                                        <i class="fas fa-edit text-primary"></i>
                                    </button>

                                    {{-- Xóa --}}
                                    <form action="{{ route('owner.flash_sale_campaigns.destroy', $campaign->id) }}"
                                        method="POST"
                                        onsubmit="return confirm('Bạn có chắc chắn muốn xóa chiến dịch này?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="btn btn-sm btn-light border"
                                            title="Xóa chiến dịch">
                                            <i class="fas fa-trash-alt text-danger"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>

                        {{-- MODAL CẬP NHẬT CHIẾN DỊCH (UPDATE) --}}
                        <div class="modal fade" id="editCampaign{{ $campaign->id }}" tabindex="-1"
                            aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content border-0 shadow">
                                    <div class="modal-header bg-dark text-white">
                                        <h5 class="modal-title fw-bold">Sửa Chiến Dịch</h5>
                                        <button type="button" class="btn-close btn-close-white"
                                            data-bs-dismiss="modal"></button>
                                    </div>
                                    <form action="{{ route('owner.flash_sale_campaigns.update', $campaign->id) }}"
                                        method="POST">
                                        @csrf
                                        @method('PUT')
                                        <div class="modal-body p-4">
                                            <div class="mb-3">
                                                <label class="filter-label">Tên chiến dịch <span
                                                        class="text-danger">*</span></label>
                                                <input type="text" name="name" class="form-control"
                                                    value="{{ $campaign->name }}" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="filter-label">Mô tả</label>
                                                <textarea name="description" class="form-control" rows="2">{{ $campaign->description }}</textarea>
                                            </div>
                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <label class="filter-label">Bắt đầu <span
                                                            class="text-danger">*</span></label>
                                                    <input type="datetime-local" name="start_datetime"
                                                        class="form-control"
                                                        value="{{ \Carbon\Carbon::parse($campaign->start_datetime)->format('Y-m-d\TH:i') }}"
                                                        min="{{ now()->format('Y-m-d\TH:i') }}" required>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="filter-label">Kết thúc <span
                                                            class="text-danger">*</span></label>
                                                    <input type="datetime-local" name="end_datetime"
                                                        class="form-control"
                                                        value="{{ \Carbon\Carbon::parse($campaign->end_datetime)->format('Y-m-d\TH:i') }}"
                                                        min="{{ now()->format('Y-m-d\TH:i') }}" required>
                                                </div>
                                            </div>
                                            <div class="alert alert-info mt-3 small border-0">
                                                <i class="fas fa-info-circle me-1"></i> Lưu ý: Khi đổi thời gian,
                                                các slot đã chọn không nằm trong khoảng thời gian mới sẽ bị tự động
                                                gỡ bỏ.
                                            </div>
                                        </div>
                                        <div class="modal-footer bg-light border-0">
                                            <button type="button" class="btn btn-white"
                                                data-bs-dismiss="modal">Hủy</button>
                                            <button type="submit"
                                                class="btn btn-dark px-4 fw-bold text-white">Lưu thay đổi</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">Không tìm thấy chiến dịch Flash
                                Sale nào.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-4 py-3 border-top">
                {{ $flashSaleCampaigns->links() }}
            </div>
        </div>
    </div>

    {{-- MODAL TẠO CHIẾN DỊCH (BƯỚC 1) --}}
    <div class="modal fade" id="createCampaignModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered border-0 shadow">
            <div class="modal-content border-0">
                <div class="modal-header bg-primary text-white border-0">
                    <h5 class="modal-title fw-bold">Tạo Chiến Dịch Flash Sale</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('owner.flash_sale_campaigns.store_campaign') }}" method="POST">
                    @csrf
                    <div class="modal-body p-4">
                        {{-- Tên chiến dịch --}}
                        <div class="mb-3">
                            <label class="filter-label">Tên chiến dịch <span class="text-danger">*</span></label>
                            <input type="text" name="name"
                                class="form-control @error('name') is-invalid @enderror"
                                placeholder="VD: Khuyến mãi mừng hè..." value="{{ old('name') }}">
                            @error('name')
                            <div class="invalid-feedback fw-bold">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Mô tả --}}
                        <div class="mb-3">
                            <label class="filter-label">Mô tả (Không bắt buộc)</label>
                            <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="2"
                                placeholder="VD: Áp dụng cho toàn bộ các sân cầu lông...">{{ old('description') }}</textarea>
                            @error('description')
                            <div class="invalid-feedback fw-bold">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row g-3">
                            {{-- Thời gian bắt đầu --}}
                            <div class="col-md-6">
                                <label class="filter-label">Thời gian bắt đầu <span
                                        class="text-danger">*</span></label>
                                <input type="datetime-local" name="start_datetime"
                                    class="form-control @error('start_datetime') is-invalid @enderror"
                                    value="{{ old('start_datetime') }}"
                                    min="{{ now()->format('Y-m-d\TH:i') }}">

                                @error('start_datetime')
                                <div class="invalid-feedback fw-bold">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Thời gian kết thúc --}}
                            <div class="col-md-6">
                                <label class="filter-label">Thời gian kết thúc <span
                                        class="text-danger">*</span></label>
                                <input type="datetime-local" name="end_datetime"
                                    class="form-control @error('end_datetime') is-invalid @enderror"
                                    value="{{ old('end_datetime') }}"
                                    min="{{ now()->format('Y-m-d\TH:i') }}">
                                @error('end_datetime')
                                <div class="invalid-feedback fw-bold">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light border-0">
                        <button type="button" class="btn btn-white" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-primary px-4 fw-bold shadow-sm">
                            Tiếp theo <i class="fas fa-arrow-right ms-1"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('input[type="datetime-local"]').forEach(input => {
            const now = new Date();
            now.setSeconds(0);
            now.setMilliseconds(0);
            input.min = now.toISOString().slice(0, 16);
        });

        document.querySelectorAll('input[name="start_datetime"]').forEach(startInput => {
            startInput.addEventListener('change', function() {
                const endInput = this.closest('.row')?.querySelector('input[name="end_datetime"]');
                if (endInput) {
                    endInput.min = this.value;
                }
            });
        });
    });
</script>
@endsection