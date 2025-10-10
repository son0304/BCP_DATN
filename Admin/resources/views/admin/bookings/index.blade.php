@extends('layouts.admin')

@section('content')
<h2>Danh sách Booking</h2>

<table class="table">
  <thead>
    <tr>
      <th>ID</th>
      <th>Khách hàng</th>
      <th>Địa điểm</th>
      <th>Tổng tiền</th>
      <th>Trạng thái</th>
      <th>Thanh toán</th>
      <th>Ngày đặt</th>
      <th></th>
    </tr>
  </thead>
  <tbody>
    @foreach($bookings as $b)
    <tr>
      <td>{{ $b->id }}</td>
      <td>{{ $b->user->name }}</td>
      <td>{{ $b->venue->name }}</td>
      <td>{{ number_format($b->total_amount) }}₫</td>
      <td>{{ ucfirst($b->status) }}</td>
      <td>{{ ucfirst($b->payment_status) }}</td>
      <td>{{ $b->created_at->format('d/m/Y H:i') }}</td>
      <td>
        <a href="{{ route('admin.bookings.show', $b->id) }}">Chi tiết</a>
      </td>
    </tr>
    @endforeach
  </tbody>
</table>

{{ $bookings->links() }}
@endsection
