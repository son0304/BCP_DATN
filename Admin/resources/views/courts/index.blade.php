@extends('app')
@section('content')
<h1>Danh sách sân</h1>
<a href="{{ route('admin.courts.create') }}" class="btn btn-primary mb-3">Thêm sân mới</a>

@if (session('success'))
<div class="alert alert-success">
    {{ session('success') }}
</div>
@endif
@if (session('error'))
<div class="alert alert-danger">
    {{ session('error') }}
</div>
@endif

<table class="table table-bordered">
    <thead>
        <tr>
            <th>ID</th>
            <th>Tên sân</th>
            <th>Thương hiệu</th>
            <th>Loại hình</th>
            {{-- ĐÃ XÓA CỘT GIÁ/GIỜ --}}
            <th>Loại sân</th>
            <th>Hành động</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($courts as $court)
        <tr>
            <td>{{ $court->id }}</td>
            <td>{{ $court->name }}</td>
            <td>{{ $court->venue->name ?? 'N/A' }}</td>
            <td>{{ $court->venue_type->name ?? 'N/A' }}</td>
            {{-- ĐÃ XÓA CỘT GIÁ/GIỜ --}}
            <td>{{ $court->is_indoor ? 'Trong nhà' : 'Ngoài trời' }}</td>
            <td>
                <a href="{{ route('admin.courts.show', $court) }}" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-eye me-1"></i> Chi tiết
                </a>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

{{ $courts->links() }}
@endsection