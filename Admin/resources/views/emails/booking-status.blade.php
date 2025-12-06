<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        .email-container {
            max-width: 600px;
            margin: 20px auto;
            background: #fff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .header {
            background-color: #10B981;
            color: white;
            padding: 20px;
            text-align: center;
        }

        .header h1 {
            margin: 0;
            font-size: 24px;
        }

        .content {
            padding: 20px;
        }

        .info-section {
            margin-bottom: 20px;
            border-bottom: 1px solid #eee;
            padding-bottom: 15px;
        }

        .info-title {
            font-weight: bold;
            color: #10B981;
            text-transform: uppercase;
            font-size: 14px;
            margin-bottom: 10px;
            display: block;
        }

        .table-custom {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 14px;
        }

        .table-custom th {
            background-color: #f8f9fa;
            text-align: left;
            padding: 8px;
            border: 1px solid #ddd;
        }

        .table-custom td {
            padding: 8px;
            border: 1px solid #ddd;
            vertical-align: top;
        }

        .total-row td {
            font-weight: bold;
            background-color: #f0fdf4;
            color: #059669;
        }

        .footer {
            background-color: #f9fafb;
            padding: 15px;
            text-align: center;
            font-size: 12px;
            color: #6b7280;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            background: #d1fae5;
            color: #065f46;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <div class="email-container">
        <!-- HEADER -->
        <div class="header">
            <h1>THANH TOÁN THÀNH CÔNG</h1>
            <p>Cảm ơn bạn đã sử dụng dịch vụ của chúng tôi!</p>
        </div>

        <div class="content">
            <!-- LỜI CHÀO -->
            <p>Xin chào <strong>{{ $ticket->user->name }}</strong>,</p>
            <p>Đơn đặt sân (Vé #{{ $ticket->id }}) của bạn đã được thanh toán thành công.)</p>

            <!-- THÔNG TIN KHÁCH HÀNG -->
            <div class="info-section">
                <span class="info-title">Thông tin người đặt</span>
                <p style="margin: 5px 0;"><strong>Họ tên:</strong> {{ $ticket->user->name }}</p>
                <p style="margin: 5px 0;"><strong>Email:</strong> {{ $ticket->user->email }}</p>
                <p style="margin: 5px 0;"><strong>Số điện thoại:</strong> {{ $ticket->user->phone ?? '---' }}</p>
            </div>

            <!-- CHI TIẾT VÉ -->
            <div class="info-section">
                <span class="info-title">Chi tiết lịch đặt sân</span>
                <table class="table-custom">
                    <thead>
                        <tr>
                            <th>Địa điểm / Sân</th>
                            <th>Thời gian</th>
                            <th>Giá tiền</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($ticket->items as $item)
                            <tr>
                                <td>
                                    <!-- Lấy tên Venue -->
                                    <div style="font-weight:bold; color:#333;">
                                        {{ $item->booking->court->venue->name ?? 'Sân cầu lông' }}
                                    </div>
                                    <!-- Lấy tên Sân -->
                                    <div style="font-size:12px; color:#666;">
                                        {{ $item->booking->court->name ?? 'Sân ?' }}
                                    </div>
                                </td>
                                <td>
                                    <!-- Ngày tháng -->
                                    <div>📅 {{ \Carbon\Carbon::parse($item->booking->date)->format('d/m/Y') }}</div>
                                    <!-- Giờ -->
                                    <div style="color: #0284c7; font-weight:500;">
                                        ⏰
                                        {{ \Carbon\Carbon::parse($item->booking->timeSlot->start_time)->format('H:i') }}
                                        -
                                        {{ \Carbon\Carbon::parse($item->booking->timeSlot->end_time)->format('H:i') }}
                                    </div>
                                </td>
                                <td style="text-align: right;">
                                    {{ number_format($item->unit_price, 0, ',', '.') }} đ
                                </td>
                            </tr>
                        @endforeach

                        <!-- TỔNG KẾT TIỀN -->
                        <tr>
                            <td colspan="2" style="text-align: right; color: #666;">Tạm tính:</td>
                            <td style="text-align: right;">{{ number_format($ticket->subtotal, 0, ',', '.') }} đ</td>
                        </tr>
                        @if ($ticket->discount_amount > 0)
                            <tr>
                                <td colspan="2" style="text-align: right; color: #666;">Giảm giá:</td>
                                <td style="text-align: right; color: #dc2626;">
                                    -{{ number_format($ticket->discount_amount, 0, ',', '.') }} đ</td>
                            </tr>
                        @endif
                        <tr class="total-row">
                            <td colspan="2" style="text-align: right;">TỔNG THANH TOÁN:</td>
                            <td style="text-align: right;">{{ number_format($ticket->total_amount, 0, ',', '.') }} đ
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- TRẠNG THÁI -->
            <div style="text-align: center; margin-top: 20px;">
                <p>Trạng thái thanh toán: <span class="status-badge">Đã thanh toán</span></p>
                <p style="font-size: 13px; color: #666;">Vui lòng đến sân sớm 10 phút để chuẩn bị. Mã vé này có thể dùng
                    để check-in tại sân.</p>
            </div>
        </div>

        <!-- FOOTER -->
        <div class="footer">
            <p>Mọi thắc mắc xin liên hệ hotline: 1900 xxxx</p>
            <p>&copy; {{ date('Y') }} BCP Sports. All rights reserved.</p>
        </div>
    </div>
</body>

</html>
