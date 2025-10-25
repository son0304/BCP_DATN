@extends('app')
@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="card-title">Quản lý Người dùng</h3>
                        <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Thêm người dùng
                        </a>
                    </div>
                </div>
                
                <!-- Search and Filter Form -->
                <div class="card-body">
                    <div class="search-filter-container">
                        <form method="GET" action="{{ route('admin.users.index') }}" class="search-form">
                            <div class="row align-items-end">
                                <div class="col-md-4">
                                    <div class="form-group mb-0">
                                        <label for="search">Tìm kiếm</label>
                                        <input type="text" class="form-control" id="search" name="search" 
                                               value="{{ request('search') }}" placeholder="Tên, email, số điện thoại...">
                                    </div>
                                </div>
                                
                                <div class="col-md-3">
                                    <div class="form-group mb-0">
                                        <label for="role_id">Vai trò</label>
                                        <select class="form-control" id="role_id" name="role_id">
                                            <option value="">Tất cả vai trò</option>
                                            @foreach($roles as $role)
                                                <option value="{{ $role->id }}" {{ request('role_id') == $role->id ? 'selected' : '' }}>
                                                    @if($role->name === 'Admin')
                                                        👑 Admin
                                                    @elseif($role->name === 'Manager')
                                                        👔 Manager
                                                    @elseif($role->name === 'Owner')
                                                        🔑 Owner
                                                    @else
                                                        👤 Customer
                                                    @endif
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="col-md-3">
                                    <div class="form-group mb-0">
                                        <label for="is_active">Trạng thái</label>
                                        <select class="form-control" id="is_active" name="is_active">
                                            <option value="">Tất cả trạng thái</option>
                                            <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>Hoạt động</option>
                                            <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>Không hoạt động</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="col-md-2">
                                    <div class="form-group mb-0">
                                        <label>&nbsp;</label>
                                        <div class="d-flex flex-column">
                                            <button type="submit" class="btn btn-primary mb-1">
                                                <i class="fas fa-search"></i> Tìm kiếm
                                            </button>
                                            @if(request()->hasAny(['search', 'role_id', 'is_active']))
                                                <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary btn-sm">
                                                    <i class="fas fa-times"></i> Xóa bộ lọc
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Search Results Info -->
                    @if(request()->hasAny(['search', 'role_id', 'is_active']))
                        <div class="alert alert-info">
                            <strong>Kết quả tìm kiếm:</strong>
                            @if(request('search'))
                                Từ khóa: "<strong>{{ request('search') }}</strong>"
                            @endif
                            @if(request('role_id'))
                                @php $selectedRole = $roles->firstWhere('id', request('role_id')); @endphp
                                @if($selectedRole)
                                    | Vai trò: <strong>
                                        @if($selectedRole->name === 'Admin')
                                            👑 Admin
                                        @elseif($selectedRole->name === 'Manager')
                                            👔 Manager
                                        @elseif($selectedRole->name === 'Owner')
                                            🔑 Owner
                                        @else
                                            👤 Customer
                                        @endif
                                    </strong>
                                @endif
                            @endif
                            @if(request('is_active') !== null)
                                | Trạng thái: <strong>{{ request('is_active') == '1' ? 'Hoạt động' : 'Không hoạt động' }}</strong>
                            @endif
                            | Tìm thấy <strong>{{ $users->total() }}</strong> người dùng
                        </div>
                    @endif

                    <!-- Users Table -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Tên</th>
                                    <th>Email</th>
                                    <th>Số điện thoại</th>
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
                                            @if($user->role->name === 'Admin')
                                                <span class="badge badge-danger">
                                                    <i class="fas fa-crown"></i> {{ $user->role->name }}
                                                </span>
                                            @elseif($user->role->name === 'Manager')
                                                <span class="badge badge-warning">
                                                    <i class="fas fa-user-tie"></i> {{ $user->role->name }}
                                                </span>
                                            @elseif($user->role->name === 'Owner')
                                                <span class="badge badge-success">
                                                    <i class="fas fa-key"></i> {{ $user->role->name }}
                                                </span>
                                            @else
                                                <span class="badge badge-info">
                                                    <i class="fas fa-user"></i> {{ $user->role->name }}
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($user->district && $user->province)
                                                {{ $user->district->name }}, {{ $user->province->name }}
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                        <td>
                                            <form method="POST" action="{{ route('admin.users.toggle-status', $user) }}" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm {{ $user->is_active ? 'btn-success' : 'btn-danger' }}">
                                                    {{ $user->is_active ? 'Hoạt động' : 'Không hoạt động' }}
                                                </button>
                                            </form>
                                        </td>
                                        <td>{{ $user->created_at->format('d/m/Y H:i') }}</td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('admin.users.show', $user) }}" class="btn btn-info btn-sm">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-warning btn-sm">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="d-inline"
                                                      onsubmit="return confirm('Bạn có chắc chắn muốn xóa người dùng này?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-sm">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center">Không có người dùng nào</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center">
                        {{ $users->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection