{{--  @extends('app')

@section('content')
    <style>
        :root {
            --bs-primary: #348738;
            --bs-primary-rgb: 52, 135, 56;
        }

        .btn-primary {
            --bs-btn-hover-bg: #2d6a2d;
            --bs-btn-hover-border-color: #2d6a2d;
        }

        .table-primary-green {
            background-color: var(--bs-primary);
            color: #fff;
        }
    </style>

    <div class="container-fluid mt-4">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white border-0 py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h1 class="h3 mb-0 fw-bold">Danh sách Sân</h1>
                    <a href="{{ route('courts.create', ['venue_id' => $venue->id]) }}" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i> Thêm sân mới
                    </a>

                </div>
            </div>

            <div class="card-body">
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle">
                        <thead class="table-primary-green">
                            <tr>
                                <th>ID</th>
                                <th>Tên sân</th>
                                <th>Thương hiệu</th>
                                <th>Loại hình</th>
                                <th class="text-center">Loại sân</th>
                                <th class="text-center">Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($courts as $court)
                                <tr>
                                    <td><strong>{{ $court->id }}</strong></td>
                                    <td>{{ $court->name }}</td>
                                    <td>{{ $court->venue->name ?? 'N/A' }}</td>
                                    <td>{{ $court->venueType->name ?? 'N/A' }}</td>
                                    <td class="text-center">
                                        @if ($court->is_indoor)
                                            <span class="badge bg-info text-dark">Trong nhà</span>
                                        @else
                                            <span class="badge bg-warning text-dark">Ngoài trời</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('courts.show', $court) }}"
                                                class="btn btn-sm btn-outline-primary" title="Xem Lịch">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('courts.edit', $court) }}"
                                                class="btn btn-sm btn-outline-warning" title="Chỉnh sửa">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-outline-danger"
                                                onclick="confirmDelete('{{ $court->id }}')" title="Xóa">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center p-4">
                                        <div class="text-muted">
                                            <i class="fas fa-futbol fa-3x mb-3"></i>
                                            <p>Chưa có sân nào được tạo.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($courts->hasPages())
                    <div class="d-flex justify-content-center mt-3">
                        {{ $courts->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Modal Xác nhận Xóa -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Xác nhận xóa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Bạn có chắc chắn muốn xóa sân này không? Tất cả lịch hoạt động liên quan cũng sẽ bị xóa.
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
        function confirmDelete(courtId) {
            // Cập nhật action của form trong modal
            const form = document.getElementById('deleteForm');
            form.action = `/admin/courts/${courtId}`; // Cập nhật URL động

            // Hiển thị modal
            const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
            modal.show();
        }
    </script>
@endpush  --}}
