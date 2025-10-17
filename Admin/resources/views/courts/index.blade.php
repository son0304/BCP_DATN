@extends('app')
@section('content')
    <h1>Danh sách sân</h1>
    <a href="{{ route('courts.create') }}" class="btn btn-primary mb-3">Thêm sân mới</a>

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
                <th>Địa điểm</th>
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