@extends('app')
@section('content')

    <style>
        :root {
            --primary-color: #348738;
            --accent-color: #f97316;
            --card-radius: 12px;
            --card-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f9fa;
        }

        .card {
            border-radius: var(--card-radius);
            box-shadow: var(--card-shadow);
            border: none;
        }

        .card-header {
            background-color: #fff;
            border-bottom: 1px solid #e5e5e5;
            font-weight: 600;
        }

        .table th,
        .table td {
            vertical-align: middle !important;
        }

        .badge-role {
            font-size: 0.85rem;
            padding: 0.35em 0.65em;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .badge-role i {
            font-size: 0.9rem;
        }

        .btn-sm i {
            pointer-events: none;
        }

        .search-filter-container {
            margin-bottom: 20px;
            background-color: #f1f6f1;
            padding: 15px 20px;
            border-radius: 10px;
        }

        .alert-info {
            background-color: #e9f7ef;
            border-color: #c6e0c3;
            color: #2d6a4f;
        }

        .table-hover tbody tr:hover {
            background-color: #f1f6f1;
        }

        .btn-action-group .btn {
            margin-right: 5px;
        }

        @media (max-width: 768px) {
            .search-filter-container .row>[class*="col-"] {
                margin-bottom: 10px;
            }
        }
    </style>

    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">

                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3 class="card-title mb-0">Quản lý Người dùng</h3>
                        <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i> Thêm người dùng
                        </a>
                    </div>

                    <!-- Search & Filter -->
                    <div class="card-body">
                        <!-- Filter container -->
                        <div class="p-3 mb-4 rounded-3" style="background-color: #f8f9fa;">
                            <form method="GET" action="{{ route('admin.users.index') }}">
                                <div class="row g-3">

                                    <!-- Row 1: Search -->
                                    <div class="col-md-3">
                                        <label for="search" class="form-label fw-semibold">Tìm kiếm</label>
                                        <input type="text" id="search" name="search" value="{{ request('search') }}"
                                            class="form-control" placeholder="Tên, email, số điện thoại...">
                                    </div>

                                    <!-- Row 2: Role -->
                                    <div class="col-md-3">
                                        <label for="role_id" class="form-label fw-semibold">Vai trò</label>
                                        <select id="role_id" name="role_id" class="form-control">
                                            <option value="">Tất cả vai trò</option>
                                            @foreach ($roles as $role)
                                                <option value="{{ $role->id }}"
                                                    {{ request('role_id') == $role->id ? 'selected' : '' }}>
                                                    @if ($role->name === 'admin')
                                                        👑 Admin
                                                    @elseif ($role->name === 'user')
                                                        👔 Manager
                                                    @elseif ($role->name === 'venue_owner')
                                                        🔑 Owner
                                                  
                                                    @endif
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <!-- Row 3: Status -->
                                    <div class="col-md-3">
                                        <label for="is_active" class="form-label fw-semibold">Trạng thái</label>
                                        <select id="is_active" name="is_active" class="form-control">
                                            <option value="">Tất cả trạng thái</option>
                                            <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>Hoạt
                                                động</option>
                                            <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>
                                                Không hoạt động</option>
                                        </select>
                                    </div>

                                    <!-- Row 4: Buttons -->
                                    <div class="col-md-3 d-flex flex-column justify-content-end">
                                        <button type="submit" class="btn btn-primary mb-1">
                                            <i class="fas fa-search me-1"></i> Tìm kiếm
                                        </button>
                                        @if (request()->hasAny(['search', 'role_id', 'is_active']))
                                            <a href="{{ route('admin.users.index') }}"
                                                class="btn btn-outline-secondary btn-sm">
                                                <i class="fas fa-times me-1"></i> Xóa bộ lọc
                                            </a>
                                        @endif
                                    </div>

                                </div>
                            </form>
                        </div>

                        <!-- Search results info -->
                        @if (request()->hasAny(['search', 'role_id', 'is_active']))
                            <div class="alert alert-info rounded-3">
                                <strong>Kết quả tìm kiếm:</strong>
                                @if (request('search'))
                                    Từ khóa: "<strong>{{ request('search') }}</strong>"
                                @endif
                                @if (request('role_id'))
                                    @php $selectedRole = $roles->firstWhere('id', request('role_id')); @endphp
                                    @if ($selectedRole)
                                        | Vai trò: <strong>
                                            @if ($selectedRole->name === 'admin')
                                                Admin
                                            @elseif ($selectedRole->name === 'user')
                                                User
                                            @elseif ($selectedRole->name === 'venue_owner')
                                                Owner
                                            @endif
                                        </strong>
                                    @endif
                                @endif
                                @if (request('is_active') !== null)
                                    | Trạng thái:
                                    <strong>{{ request('is_active') == '1' ? 'Hoạt động' : 'Không hoạt động' }}</strong>
                                @endif
                                | Tìm thấy <strong>{{ $users->total() }}</strong> người dùng
                            </div>
                        @endif

                        <!-- Users Table -->
                        <div class="table-responsive">
                            <table class="table table-hover table-bordered align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Tên</th>
                                        <th>Email</th>
                                        <th>SĐT</th>
                                        <th>Vai trò</th>
                                        <th>Địa chỉ</th>
                                        <th>Trạng thái</th>
                                        <th>Ngày tạo</th>
                                        <th>Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($users as $user)
                                        <tr>
                                            <td>{{ $user->id }}</td>
                                            <td>{{ $user->name }}</td>
                                            <td>{{ $user->email }}</td>
                                            <td>{{ $user->phone ?? 'N/A' }}</td>
                                            <td>
                                                @php
                                                    $roleClass = match ($user->role->name) {
                                                        'Admin' => 'danger',
                                                        'Manager' => 'warning',
                                                        'Owner' => 'success',
                                                        default => 'info',
                                                    };
                                                    $roleIcon = match ($user->role->name) {
                                                        'Admin' => 'fa-crown',
                                                        'Manager' => 'fa-user-tie',
                                                        'Owner' => 'fa-key',
                                                        default => 'fa-user',
                                                    };
                                                @endphp
                                                <span
                                                    class="badge bg-{{ $roleClass }} text-white py-1 px-2 rounded-pill">
                                                    <i class="fas {{ $roleIcon }}"></i> {{ $user->role->name }}
                                                </span>
                                            </td>
                                            <td>{{ $user->district && $user->province ? $user->district->name . ', ' . $user->province->name : 'N/A' }}
                                            </td>
                                            <td>
                                                <form method="POST"
                                                    action="{{ route('admin.users.toggle-status', $user) }}">
                                                    @csrf
                                                    <button type="submit"
                                                        class="btn btn-sm {{ $user->is_active ? 'btn-success' : 'btn-danger' }}">
                                                        {{ $user->is_active ? 'Hoạt động' : 'Không hoạt động' }}
                                                    </button>
                                                </form>
                                            </td>
                                            <td>{{ $user->created_at->format('d/m/Y H:i') }}</td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('admin.users.show', $user) }}"
                                                        class="btn btn-info btn-sm"><i class="fas fa-eye"></i></a>
                                                    <a href="{{ route('admin.users.edit', $user) }}"
                                                        class="btn btn-warning btn-sm"><i class="fas fa-edit"></i></a>
                                                    <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                                                        class="d-inline"
                                                        onsubmit="return confirm('Bạn có chắc chắn muốn xóa người dùng này?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger btn-sm"><i
                                                                class="fas fa-trash"></i></button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="text-center py-3">Không có người dùng nào</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-center mt-3">
                            {{ $users->appends(request()->query())->links() }}
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </div>

@endsection
