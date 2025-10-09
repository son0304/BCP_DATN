@extends('app')
@section('content')
    <h1>Danh sách sân</h1>
    <a href="{{ route('courts.create') }}" class="btn btn-primary mb-3">Thêm sân mới</a>

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Tên sân</th>
                <th>Địa điểm</th>
                <th>Loại hình</th>
                <th>Giá/giờ</th>
                <th>Trong nhà</th>
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
                    <td>{{ number_format($court->price_per_hour) }} VNĐ</td>
                    <td>{{ $court->is_indoor ? 'Có' : 'Không' }}</td>
                    <td>
                        <a href="{{ route('courts.edit', $court) }}" class="btn btn-sm btn-warning">Sửa</a>
                        <form action="{{ route('courts.destroy', $court) }}" method="POST" style="display:inline-block;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger"
                                onclick="return confirm('Bạn có chắc muốn xóa không?')">Xóa</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{ $courts->links() }}
@endsection
