@extends('app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Quản lý vé</h1>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-primary" onclick="location.reload()">
                <i class="fas fa-sync-alt me-1"></i> Làm mới
            </button>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Khách hàng</th>
                            <th>Trạng thái</th>
                            <th>Thanh toán</th>
                            <th>Tổng tiền</th>
                            <th>Số sân</th>
                            <th>Ngày tạo</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($tickets as $ticket)
                        <tr>
                            <td>
                                <strong>#{{ $ticket->id }}</strong>
                            </td>
                            <td>
                                <div>
                                    <strong>{{ $ticket->user->name ?? 'N/A' }}</strong>
                                    <br>
                                    <small class="text-muted">{{ $ticket->user->email ?? 'N/A' }}</small>
                                </div>
                            </td>
                            <td>
                                <span class="badge 
                                    @if($ticket->status == 'pending') bg-warning
                                    @elseif($ticket->status == 'confirmed') bg-success
                                    @elseif($ticket->status == 'cancelled') bg-danger
                                    @elseif($ticket->status == 'completed') bg-info
                                    @endif">
                                    @switch($ticket->status)
                                        @case('pending') Chờ xử lý @break
                                        @case('confirmed') Đã xác nhận @break
                                        @case('cancelled') Đã hủy @break
                                        @case('completed') Hoàn thành @break
                                        @default {{ $ticket->status }}
                                    @endswitch
                                </span>
                            </td>
                            <td>
                                <span class="badge 
                                    @if($ticket->payment_status == 'pending') bg-warning
                                    @elseif($ticket->payment_status == 'paid') bg-success
                                    @elseif($ticket->payment_status == 'refunded') bg-danger
                                    @endif">
                                    @switch($ticket->payment_status)
                                        @case('pending') Chờ thanh toán @break
                                        @case('paid') Đã thanh toán @break
                                        @case('refunded') Đã hoàn tiền @break
                                        @default {{ $ticket->payment_status }}
                                    @endswitch
                                </span>
                            </td>
                            <td>
                                <strong class="text-success">
                                    {{ number_format($ticket->total_amount, 0, ',', '.') }} VNĐ
                                </strong>
                                @if($ticket->discount_amount > 0)
                                    <br>
                                    <small class="text-muted">
                                        Giảm: {{ number_format($ticket->discount_amount, 0, ',', '.') }} VNĐ
                                    </small>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-primary">
                                    {{ $ticket->items->count() }} sân
                                </span>
                            </td>
                            <td>
                                {{ $ticket->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('admin.tickets.show', $ticket) }}" 
                                       class="btn btn-sm btn-outline-primary" 
                                       title="Xem chi tiết">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.tickets.edit', $ticket) }}" 
                                       class="btn btn-sm btn-outline-warning" 
                                       title="Chỉnh sửa">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" 
                                            class="btn btn-sm btn-outline-danger" 
                                            onclick="confirmDelete({{ $ticket->id }})"
                                            title="Xóa">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                <div class="text-muted">
                                    <i class="fas fa-ticket-alt fa-3x mb-3"></i>
                                    <p>Chưa có vé nào</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($tickets->hasPages())
                <div class="d-flex justify-content-center mt-4">
                    {{ $tickets->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Xác nhận xóa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                Bạn có chắc chắn muốn xóa vé này không? Hành động này không thể hoàn tác.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Xóa</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function confirmDelete(ticketId) {
    const form = document.getElementById('deleteForm');
    form.action = `/admin/tickets/${ticketId}`;
    
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
}
</script>
@endpush
