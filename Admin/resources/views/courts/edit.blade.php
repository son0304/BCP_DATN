@extends('app')

@section('content')
    <div class="container-fluid">
        <h1>Chỉnh sửa sân: {{ $court->name }}</h1>

        <div class="card">
            <div class="card-body">
                <form action="{{ route('courts.update', $court) }}" method="POST">
                    @csrf
                    @method('PUT')
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <strong>Có lỗi xảy ra!</strong>
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="mb-3">
                        <label for="name" class="form-label">Tên sân</label>
                        <input type="text" class="form-control" id="name" name="name"
                            value="{{ old('name', $court->name) }}" required>
                    </div>

                    <div class="mb-3">
                        <label for="venue_id" class="form-label">Địa điểm (Venue)</label>
                        <select class="form-select" id="venue_id" name="venue_id" required>
                            <option value="">-- Chọn địa điểm --</option>
                            @foreach ($venues as $venue)
                                <option value="{{ $venue->id }}"
                                    {{ old('venue_id', $court->venue_id) == $venue->id ? 'selected' : '' }}>
                                    {{ $venue->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="venue_type_id" class="form-label">Loại hình (Cầu lông, Pickleball...)</label>
                        <select class="form-select" id="venue_type_id" name="venue_type_id" required>
                            <option value="">-- Chọn loại hình --</option>
                            @foreach ($venueTypes as $venueType)
                                <option value="{{ $venueType->id }}"
                                    {{ old('venue_type_id', $court->venue_type_id) == $venueType->id ? 'selected' : '' }}>
                                    {{ $venueType->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="price_per_hour" class="form-label">Giá mỗi giờ (VNĐ)</label>
                        <input type="number" class="form-control" id="price_per_hour" name="price_per_hour"
                            value="{{ old('price_per_hour', $court->price_per_hour) }}" required min="0"
                            step="1000">
                    </div>

                    <div class="mb-3">
                        <label for="surface" class="form-label">Bề mặt sân (Ví dụ: Thảm, xi măng...)</label>
                        <input type="text" class="form-control" id="surface" name="surface"
                            value="{{ old('surface', $court->surface) }}">
                    </div>

                    <div class="mb-3">
                        <label for="is_indoor" class="form-label">Loại sân</label>
                        <select class="form-select" id="is_indoor" name="is_indoor" required>
                            <option value="1" {{ old('is_indoor', $court->is_indoor) == 1 ? 'selected' : '' }}>Trong
                                nhà</option>
                            <option value="0" {{ old('is_indoor', $court->is_indoor) == 0 ? 'selected' : '' }}>Ngoài
                                trời</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-success">Cập nhật</button>
                    <a href="{{ route('courts.index') }}" class="btn btn-secondary">Hủy bỏ</a>
                </form>
            </div>
        </div>
    </div>
@endsection
