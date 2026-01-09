@extends('app') {{-- Hoặc layout tương ứng của chủ sân --}}
@section('content')
    <style>
        .bg-soft-success {
            background-color: #d1fae5;
            color: #065f46;
        }

        .bg-soft-danger {
            background-color: #fee2e2;
            color: #991b1b;
        }

        .bg-soft-warning {
            background-color: #fef3c7;
            color: #92400e;
        }

        .voucher-code {
            font-family: 'Monaco', monospace;
            background: #f1f5f9;
            padding: 5px 10px;
            border-radius: 6px;
            font-weight: 700;
            border: 1px dashed #3b82f6;
            color: #1d4ed8;
        }
    </style>

    <div class="container-fluid py-4">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="fw-bold mb-0 text-dark"><i class="fas fa-tags me-2 text-primary"></i>Mã Giảm Giá Của Tôi</h5>
                    <p class="text-muted small mb-0">Quản lý các chương trình ưu đãi cho các sân bạn đang sở hữu</p>
                </div>
                <a href="{{ route('owner.promotions.create') }}" class="btn btn-primary px-4 rounded-pill shadow-sm">
                    <i class="fas fa-plus me-2"></i>Tạo mã mới
                </a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light text-muted small font-weight-bold">
                            <tr>
                                <th class="ps-4">Mã Voucher</th>
                                <th>Mức Ưu Đãi</th>
                                <th>Lượt Sử Dụng</th>
                                <th>Hiệu Lực</th>
                                <th>Phạm Vi Áp Dụng</th>
                                <th>Trạng Thái</th>
                                <th class="text-end pe-4">Thao Tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($promotions as $p)
                                <tr>
                                    <td class="ps-4">
                                        <div class="voucher-code mb-1">{{ $p->code }}</div>
                                        <div class="small text-muted">{{ Str::limit($p->description, 30) }}</div>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark">
                                            {{ $p->type == 'percentage' ? $p->value . '%' : number_format($p->value) . '₫' }}
                                        </div>
                                        <small class="text-muted">Đơn từ {{ number_format($p->min_order_value) }}₫</small>
                                    </td>
                                    <td>
                                        <div class="small mb-1 font-weight-bold">{{ $p->used_count }} /
                                            {{ $p->usage_limit ?: '∞' }}</div>
                                        <div class="progress" style="height: 5px; width: 100px;">
                                            @php $percent = $p->usage_limit > 0 ? ($p->used_count / $p->usage_limit) * 100 : 0; @endphp
                                            <div class="progress-bar bg-primary" style="width: {{ min($percent, 100) }}%">
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="small fw-bold">{{ $p->start_at->format('d/m/Y') }}</div>
                                        <div class="small text-muted">{{ $p->end_at->format('d/m/Y') }}</div>
                                    </td>
                                    <td>
                                        @if ($p->venue)
                                            <span class="badge bg-light text-dark border"><i
                                                    class="fas fa-map-marker-alt"></i> {{ $p->venue->name }}</span>
                                        @else
                                            <span class="badge bg-soft-primary"><i class="fas fa-home"></i> Tất cả sân của
                                                tôi</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($p->process_status == 'disabled')
                                            <span class="badge bg-soft-danger rounded-pill px-3">Đã tắt</span>
                                        @elseif($p->isExpired())
                                            <span class="badge bg-soft-warning rounded-pill px-3">Hết hạn</span>
                                        @else
                                            <span class="badge bg-soft-success rounded-pill px-3">Đang chạy</span>
                                        @endif
                                    </td>
                                    <td class="text-end pe-4">
                                        <div class="btn-group">
                                            <a href="{{ route('owner.promotions.edit', $p) }}"
                                                class="btn btn-sm btn-outline-warning border-0"><i
                                                    class="fas fa-edit"></i></a>
                                            <form action="{{ route('owner.promotions.destroy', $p) }}" method="POST"
                                                class="d-inline">
                                                @csrf @method('DELETE')
                                                <button class="btn btn-sm btn-outline-danger border-0"
                                                    onclick="return confirm('Xóa mã này?')"><i
                                                        class="fas fa-trash"></i></button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted">Bạn chưa tạo mã giảm giá nào.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
