<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\Venue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class BookingController extends Controller
{
    public function booking_admin(Request $request)
    {
        $search  = $request->input('search');
        $status  = $request->input('status');
        $venueId = $request->input('venue');

        // Khởi tạo query với các quan hệ cần thiết
        $query = Ticket::with([
            'user',
            'items.booking.court.venue',
            'items.booking.timeSlot',
        ]);

        // Filter: Lọc theo sân (nếu chọn)
        if ($venueId) {
            $query->whereHas('items.booking.court.venue', function ($q) use ($venueId) {
                $q->where('id', $venueId);
            });
        }

        // Filter: Trạng thái
        if ($status) {
            $query->where('status', $status);
        }

        // Filter: Tìm kiếm tên khách hàng
        if ($search) {
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        // Lấy dữ liệu và phân trang
        $tickets = $query->orderBy('created_at', 'desc')->paginate(10);

        // Admin được lấy toàn bộ danh sách sân để lọc
        $venues = Venue::all();

        return view('admin.bookings.index', compact('tickets', 'search', 'status', 'venues', 'venueId'));
    }
    public function booking_venue(Request $request)
    {
        $user = Auth::user(); // Lấy người dùng hiện tại

        // 1. Lấy tham số từ URL
        $search  = $request->input('search');
        $status  = $request->input('status');
        $venueId = $request->input('venue');

        // 2. Khởi tạo Query Booking
        $query = Ticket::with([
            'user',
            'items.booking.court.venue',
            'items.booking.timeSlot',
        ]);

        // 3. Lọc Booking: Chỉ lấy vé có chứa sân của owner_id này
        $query->whereHas('items.booking.court.venue', function ($q) use ($user, $venueId) {
            $q->where('owner_id', $user->id); // <--- QUAN TRỌNG: Chỉ lấy sân của tôi

            if ($venueId) {
                $q->where('id', $venueId); // Lọc thêm theo sân cụ thể nếu chọn
            }
        });

        // 4. Các bộ lọc khác (Trạng thái, Tìm kiếm)
        if ($status) {
            $query->where('status', $status);
        }
        if ($search) {
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        // 5. Lấy dữ liệu Booking
        $tickets = $query->orderBy('created_at', 'desc')->paginate(10);

        // 6. Lọc Dropdown Venue: Chỉ lấy danh sách sân của owner này
        $venues = Venue::where('owner_id', $user->id)->get();
        // dd($tickets);// <--- PHẦN BẠN YÊU CẦU

        return view('venue_owner.bookings.index', compact('tickets', 'search', 'status', 'venues', 'venueId'));
    }



    public function update(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|string',
            'payment_status' => 'required|string',
        ]);

        $ticket = Ticket::findOrFail($id);

        $ticket->update([
            'status' => $request->status,
            'payment_status' => $request->payment_status,
        ]);
        if ($request->status == 3 && $oldStatus != 3) {
        
        // 1. Lấy khung giờ kết thúc muộn nhất của đơn này
        // (Sắp xếp theo ngày và giờ kết thúc của slot cuối cùng)
        $lastItem = $ticket->items->map(function($item) {
            return [
                'full_end_time' => Carbon::parse($item->booking->date . ' ' . $item->booking->timeSlot->end_time)
            ];
        })->sortByDesc('full_end_time')->first();

        $finalEndTime = $lastItem['full_end_time'];

        // 2. Thiết lập thời gian thực thi
        $now = now();
        
        // Hẹn giờ thông báo: End Time - 10 phút
        $notifyAt = $finalEndTime->copy()->subMinutes(10);
        
        if ($notifyAt->gt($now)) {
            // Gửi ticket vào hàng đợi, delay đến lúc cần thông báo
            NotifyOwnerJob::dispatch($ticket)->delay($notifyAt);
        }

        // Hẹn giờ tự động hoàn thành: Đúng lúc End Time
        if ($finalEndTime->gt($now)) {
            AutoCompleteTicketJob::dispatch($ticket)->delay($finalEndTime);
        }
    }


        return redirect()->back()->with('success', 'Cập nhật đơn hàng thành công!');
    }
}
